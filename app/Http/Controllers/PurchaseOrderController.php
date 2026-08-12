<?php

namespace App\Http/Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseRequest;
use App\Models\BusinessPartner;
use App\Models\Tax;
use App\Models\Warehouse;
use App\Models\Uom;
use App\Models\ChartOfAccount;
use App\Models\Dimension;
use App\Models\CostCenter;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['lines', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('doc_num', 'cast_as_string', "%{$search}%")
                  ->orWhere('doc_entry', 'cast_as_string', "%{$search}%")
                  ->orWhere('card_code', 'ilike', "%{$search}%")
                  ->orWhere('card_name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('sync_status', $request->status);
        }

        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $purchaseOrders = $query->paginate($perPage)->withQueryString();

        return view('purchase-order.index', compact('purchaseOrders', 'sortField', 'sortDirection', 'perPage'));
    }

    public function create(Request $request)
    {
        $vendors = BusinessPartner::where('card_type', 'cSupplier')->where('valid', true)->orderBy('card_code')->get();
        $taxes = Tax::where('locked', false)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $uoms = Uom::all();
        $chartOfAccounts = ChartOfAccount::where('is_active', true)->where('account_type', 'Postable')->get();
        $dimensions = Dimension::where('is_active', true)->orderBy('dimension_code')->get();
        $costCenters = CostCenter::where('is_active', true)->where('center_code', 'NOT ILIKE', 'Centr_z%')->orderBy('center_code')->get();

        $basePr = null;
        $prefilledData = null;

        if ($request->filled('from_pr')) {
            $basePr = PurchaseRequest::with('lines')->find($request->from_pr);
            if ($basePr) {
                // Ensure PR is synced to SAP
                if ($basePr->sync_status !== 'Synced' || !$basePr->doc_entry) {
                    return redirect()->route('purchase-request.index')->with('error', 'Cannot copy to Purchase Order because the Purchase Request is not yet synced to SAP.');
                }

                $prefilledData = [
                    'doc_type' => $basePr->doc_type,
                    'card_code' => $basePr->vendor,
                    'posting_date' => $basePr->posting_date ? $basePr->posting_date : date('Y-m-d'),
                    'delivery_date' => $basePr->required_date ? $basePr->required_date : date('Y-m-d', strtotime('+3 days')),
                    'document_date' => date('Y-m-d'),
                    'whs_code' => $basePr->whs_code,
                    'tax_code' => $basePr->tax_code,
                    'lines' => $basePr->lines->map(function($line, $idx) use ($basePr) {
                        return [
                            'item_code' => $line->item_code,
                            'item_description' => $line->item_description,
                            'account_code' => $line->account_code,
                            'account_name' => $line->account_name,
                            'quantity' => (float) $line->quantity,
                            'price' => (float) $line->price,
                            'uom_code' => $line->uom_code,
                            'costing_code' => $line->costing_code,
                            'costing_code2' => $line->costing_code2,
                            'costing_code3' => $line->costing_code3,
                            'costing_code4' => $line->costing_code4,
                            'costing_code5' => $line->costing_code5,
                            'base_type' => 1470000113, // SAP B1 Purchase Request Object Type
                            'base_entry' => $basePr->doc_entry ?? $basePr->id,
                            'base_line' => $line->line_num ?? $idx,
                        ];
                    })
                ];
            }
        }

        return view('purchase-order.create', compact('vendors', 'taxes', 'warehouses', 'uoms', 'chartOfAccounts', 'dimensions', 'costCenters', 'basePr', 'prefilledData'));
    }

    public function store(Request $request, SapService $sap)
    {
        $validated = $request->validate([
            'doc_type' => 'required|string|in:dssItem,dssService',
            'card_code' => 'required|string',
            'posting_date' => 'required|date',
            'delivery_date' => 'required|date',
            'document_date' => 'required|date',
            'whs_code' => 'nullable|string',
            'tax_code' => 'required|string',
            'comments' => 'nullable|string',
            'instant_sync' => 'nullable|boolean',
            'lines' => 'required|array|min:1',
            'lines.*.item_code' => 'nullable|string',
            'lines.*.item_description' => 'nullable|string',
            'lines.*.account_code' => 'nullable|string',
            'lines.*.account_name' => 'nullable|string',
            'lines.*.quantity' => 'nullable|numeric|min:0',
            'lines.*.price' => 'nullable|numeric|min:0',
            'lines.*.uom_code' => 'nullable|string',
            'lines.*.costing_code' => 'nullable|string',
            'lines.*.costing_code2' => 'nullable|string',
            'lines.*.costing_code3' => 'nullable|string',
            'lines.*.costing_code4' => 'nullable|string',
            'lines.*.costing_code5' => 'nullable|string',
            'lines.*.base_type' => 'nullable|integer',
            'lines.*.base_entry' => 'nullable|integer',
            'lines.*.base_line' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $vendor = BusinessPartner::where('card_code', $validated['card_code'])->first();
            $vendorName = $vendor ? $vendor->card_name : $validated['card_code'];

            $po = PurchaseOrder::create([
                'sync_status' => 'Draft',
                'sap_status' => 'Open',
                'doc_type' => $validated['doc_type'],
                'card_code' => $validated['card_code'],
                'card_name' => $vendorName,
                'posting_date' => $validated['posting_date'],
                'delivery_date' => $validated['delivery_date'],
                'document_date' => $validated['document_date'],
                'whs_code' => $validated['whs_code'] ?? null,
                'tax_code' => $validated['tax_code'],
                'comments' => $validated['comments'] ?? null,
                'created_by' => auth()->user()->uid7,
            ]);

            foreach ($validated['lines'] as $idx => $line) {
                $po->lines()->create([
                    'line_num' => $idx,
                    'item_code' => $line['item_code'] ?? null,
                    'item_description' => $line['item_description'] ?? null,
                    'account_code' => $line['account_code'] ?? null,
                    'account_name' => $line['account_name'] ?? null,
                    'quantity' => $line['quantity'] ?? 1,
                    'price' => $line['price'] ?? 0,
                    'uom_code' => $line['uom_code'] ?? null,
                    'tax_code' => $validated['tax_code'],
                    'costing_code' => $line['costing_code'] ?? null,
                    'costing_code2' => $line['costing_code2'] ?? null,
                    'costing_code3' => $line['costing_code3'] ?? null,
                    'costing_code4' => $line['costing_code4'] ?? null,
                    'costing_code5' => $line['costing_code5'] ?? null,
                    'base_type' => !empty($line['base_type']) ? (int) $line['base_type'] : null,
                    'base_entry' => !empty($line['base_entry']) ? (int) $line['base_entry'] : null,
                    'base_line' => isset($line['base_line']) && $line['base_line'] !== '' ? (int) $line['base_line'] : null,
                ]);
            }

            DB::commit();

            if (!empty($validated['instant_sync'])) {
                $syncResult = $this->pushToSap($po, $sap);
                if ($syncResult['success']) {
                    return redirect()->route('purchase-order.index')->with('success', 'Purchase Order created and synced to SAP successfully!');
                } else {
                    return redirect()->route('purchase-order.index')->with('error', 'Purchase Order created but failed to sync to SAP: ' . $syncResult['message']);
                }
            }

            SystemLog::logAction('sap', 'Created Purchase Order', "Saved PO #{$po->id} as Draft in Web App.");

            return redirect()->route('purchase-order.index')->with('success', 'Purchase Order saved successfully as Draft.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create Purchase Order: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['lines', 'creator'])->findOrFail($id);
        $basePr = $purchaseOrder->basePurchaseRequest();

        return view('purchase-order.show', compact('purchaseOrder', 'basePr'));
    }

    public function sync(PurchaseOrder $purchaseOrder, SapService $sap)
    {
        $config = Config::first();
        if (!$config || !$config->base_url || !$config->database) {
            return back()->with('error', 'Configuration is missing.');
        }

        $syncResult = $this->pushToSap($purchaseOrder, $sap);
        if ($syncResult['success']) {
            return back()->with('success', 'Purchase Order synced to SAP successfully!');
        } else {
            return back()->with('error', 'Failed to sync Purchase Order: ' . $syncResult['message']);
        }
    }

    public function pushToSap(PurchaseOrder $po, SapService $sap)
    {
        try {
            $user = $po->created_by ? \App\Models\User::find($po->created_by) : auth()->user();
            if (!$user) {
                throw new \Exception("User not found for PO.");
            }

            $isService = ($po->doc_type === 'dssService');
            $lines = [];

            foreach ($po->lines as $line) {
                $linePayload = [
                    'ItemDescription' => $line->item_description,
                    'VatGroup' => $line->tax_code ?? $po->tax_code,
                ];

                if ($isService) {
                    $linePayload['AccountCode'] = $line->account_code;
                    $linePayload['LineTotal'] = (float) $line->price;
                } else {
                    $linePayload['ItemCode'] = $line->item_code;
                    $linePayload['Quantity'] = (float) $line->quantity;
                    $linePayload['UnitPrice'] = (float) $line->price;

                    if (!empty($po->whs_code)) {
                        $linePayload['WarehouseCode'] = $po->whs_code;
                    }
                    if (!empty($line->uom_code)) {
                        $linePayload['UoMCode'] = $line->uom_code;
                    }
                }

                if (!empty($line->costing_code)) $linePayload['CostingCode'] = $line->costing_code;
                if (!empty($line->costing_code2)) $linePayload['CostingCode2'] = $line->costing_code2;
                if (!empty($line->costing_code3)) $linePayload['CostingCode3'] = $line->costing_code3;
                if (!empty($line->costing_code4)) $linePayload['CostingCode4'] = $line->costing_code4;
                if (!empty($line->costing_code5)) $linePayload['CostingCode5'] = $line->costing_code5;

                // Base Document Linkage (PR -> PO)
                if (!empty($line->base_type) && !empty($line->base_entry)) {
                    $linePayload['BaseType'] = (int) $line->base_type; // 1470000113 for PR
                    $linePayload['BaseEntry'] = (int) $line->base_entry;
                    if (isset($line->base_line)) {
                        $linePayload['BaseLine'] = (int) $line->base_line;
                    }
                }

                $lines[] = $linePayload;
            }

            $payload = [
                'CardCode' => $po->card_code,
                'DocType' => $po->doc_type ?? 'dssItem',
                'DocDate' => $po->posting_date,
                'DocDueDate' => $po->delivery_date,
                'TaxDate' => $po->document_date,
                'Comments' => $po->comments,
                'DocumentLines' => $lines,
            ];

            $response = $sap->post($user, 'PurchaseOrders', $payload);

            if (isset($response['error'])) {
                throw new \Exception($response['error']['message']['value'] ?? 'Unknown SAP Error');
            }

            $po->update([
                'doc_entry' => $response['DocEntry'] ?? null,
                'doc_num' => $response['DocNum'] ?? null,
                'sync_status' => 'Synced',
                'sap_status' => $response['DocumentStatus'] ?? 'Open',
                'sync_error' => null
            ]);

            SystemLog::logAction('sap', 'Synced Purchase Order', "PO #{$po->id} successfully pushed to SAP. DocEntry: " . ($response['DocEntry'] ?? '-'), true);

            return ['success' => true];

        } catch (\Exception $e) {
            $po->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            
            SystemLog::logAction('sap', 'Sync PO Failed', "PO #{$po->id} failed: " . $e->getMessage(), true);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
