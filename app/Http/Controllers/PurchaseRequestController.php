<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SapService;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\SystemLog;
use App\Models\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tax;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with('lines')->latest();
        
        $filters = $request->input('sync_statuses', ['Draft', 'Synced', 'Failed']);
        
        if (!empty($filters)) {
            $query->whereIn('sync_status', $filters);
        }

        $requests = $query->paginate(20)->withQueryString();
        
        return view('purchase-request.index', compact('requests', 'filters'));
    }

    public function create()
    {
        $taxes = Tax::where('locked', false)->get();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        $uoms = \App\Models\Uom::all();
        $chartOfAccounts = \App\Models\ChartOfAccount::where('is_active', true)->where('account_type', 'Postable')->get();
        $dimensions = \App\Models\Dimension::where('is_active', true)->orderBy('dimension_code')->get();
        $costCenters = \App\Models\CostCenter::where('is_active', true)->where('center_code', 'NOT ILIKE', 'Centr_z%')->orderBy('center_code')->get();

        return view('purchase-request.create', compact('taxes', 'warehouses', 'uoms', 'chartOfAccounts', 'dimensions', 'costCenters'));
    }

    public function getVendors()
    {
        try {
            $vendors = \App\Models\BusinessPartner::where(function($q) {
                    $q->where('type', 'Vendor')
                      ->orWhere('type', 'cSupplier')
                      ->orWhere('type', 'vSupplier');
                })
                ->get()
                ->map(function($bp) {
                    return [
                        'CardCode' => $bp->bp_code,
                        'CardName' => $bp->name,
                        'ContactEmployees' => collect($bp->contact_persons ?? [])->map(function($cp) {
                            return ['Name' => $cp];
                        })->toArray()
                    ];
                });
            
            return response()->json(['success' => true, 'data' => $vendors]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAccounts()
    {
        try {
            $accounts = \App\Models\ChartOfAccount::where('is_active', true)
                ->where('account_type', 'Postable')
                ->get()
                ->map(function($acc) {
                    return [
                        'Code' => $acc->code,
                        'Name' => $acc->name,
                        'FormatCode' => $acc->external_code ?? $acc->code,
                    ];
                });
            
            return response()->json(['success' => true, 'data' => $accounts]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getItems()
    {
        try {
            $itemGroupsMap = \App\Models\ItemGroup::pluck('default_uom', 'group_name')->toArray();

            $items = \App\Models\Item::where('is_active', true)
                ->get()
                ->map(function($item) use ($itemGroupsMap) {
                    $groupDefaultUom = $itemGroupsMap[$item->item_group] ?? null;
                    
                    // SAP Fallback order: Purchasing UoM -> Inventory UoM -> Item Group Default UoM
                    $resolvedUom = $item->purchasing_uom ?: ($item->inventory_uom ?: ($item->uom ?: $groupDefaultUom));

                    return [
                        'ItemCode' => $item->item_code,
                        'ItemName' => $item->item_name,
                        'PurchasingUom' => $item->purchasing_uom,
                        'InventoryUom' => $item->inventory_uom ?: $item->uom,
                        'SalesUom' => $item->sales_uom,
                        'GroupDefaultUom' => $groupDefaultUom,
                        'ResolvedUom' => $resolvedUom,
                        'UomGroupType' => $item->uom_group_type ?? 'Manual',
                        'UomGroup' => $item->uom_group,
                    ];
                });
            
            return response()->json(['success' => true, 'data' => $items]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request, SapService $sap)
    {
        $validated = $request->validate([
            'doc_type' => 'required|string|in:dssItem,dssService',
            'document_date' => 'required|date',
            'valid_until' => 'required|date',
            'posting_date' => 'required|date',
            'required_date' => 'required|date',
            'vendor' => 'required|string',
            'whs_code' => 'nullable|string',
            'tax_code' => 'required|string',
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
        ]);

        try {
            DB::beginTransaction();

            $pr = PurchaseRequest::create([
                'sync_status' => 'Draft',
                'doc_type' => $validated['doc_type'],
                'document_date' => $validated['document_date'],
                'valid_until' => $validated['valid_until'],
                'posting_date' => $validated['posting_date'],
                'required_date' => $validated['required_date'],
                'requester' => auth()->user()->sap_user,
                'vendor' => $validated['vendor'],
                'whs_code' => $validated['whs_code'] ?? null,
                'tax_code' => $validated['tax_code'],
                'created_by' => auth()->user()->uid7,
            ]);

            foreach ($validated['lines'] as $line) {
                $pr->lines()->create([
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
                ]);
            }

            DB::commit();

            if (!empty($validated['instant_sync'])) {
                $syncResult = $this->pushToSap($pr, $sap);
                if ($syncResult['success']) {
                    return redirect()->route('purchase-request.index')->with('success', 'Purchase Request created and synced to SAP successfully!');
                } else {
                    return redirect()->route('purchase-request.index')->with('error', 'Purchase Request created but failed to sync to SAP: ' . $syncResult['message']);
                }
            }

            SystemLog::logAction('sap', 'Created Purchase Request', "Saved PR #{$pr->id} as Draft in Web App.");

            return redirect()->route('purchase-request.index')->with('success', 'Purchase Request saved successfully as Draft.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create Purchase Request: ' . $e->getMessage())->withInput();
        }
    }

    public function pushToSap(PurchaseRequest $pr, SapService $sap)
    {
        try {
            $user = $pr->created_by ? \App\Models\User::find($pr->created_by) : auth()->user();
            if (!$user) {
                throw new \Exception("User not found for PR.");
            }

            $isService = ($pr->doc_type === 'dssService');
            $lines = [];

            foreach ($pr->lines as $line) {
                $linePayload = [
                    'ItemDescription' => $line->item_description,
                    'VatGroup' => $line->tax_code ?? $pr->tax_code,
                    'LineVendor' => $pr->vendor
                ];

                if ($isService) {
                    $linePayload['AccountCode'] = $line->account_code;
                    $linePayload['LineTotal'] = (float) $line->price;
                } else {
                    $linePayload['ItemCode'] = $line->item_code;
                    $linePayload['Quantity'] = (float) $line->quantity;
                    $linePayload['UnitPrice'] = (float) $line->price;

                    if (!empty($pr->whs_code)) {
                        $linePayload['WarehouseCode'] = $pr->whs_code;
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

                $lines[] = $linePayload;
            }

            $payload = [
                'DocType' => $pr->doc_type ?? 'dssItem',
                'DocDate' => $pr->posting_date,
                'DocDueDate' => $pr->valid_until,
                'RequriedDate' => $pr->required_date,
                'Requester' => $pr->requester,
                'DocumentLines' => $lines,
            ];

            $response = $sap->post($user, 'PurchaseRequests', $payload);

            if (isset($response['error'])) {
                throw new \Exception($response['error']['message']['value'] ?? 'Unknown SAP Error');
            }

            $pr->update([
                'sync_status' => 'Synced',
                'sap_status' => $response['DocumentStatus'] ?? 'Open',
                'sync_error' => null
            ]);

            SystemLog::logAction('sap', 'Synced Purchase Request', "PR #{$pr->id} successfully pushed to SAP.", true);
            SystemLog::logAction('scheduler', 'Processed PR Sync', "PR #{$pr->id} successfully pushed to SAP via Instant Sync.", true);

            return ['success' => true];

        } catch (\Exception $e) {
            $pr->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            
            SystemLog::logAction('sap', 'Sync PR Failed', "PR #{$pr->id} failed: " . $e->getMessage(), true);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
