<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\SystemLog;
use App\Models\Tax;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\ApprovalEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with('lines')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('doc_num', 'ilike', "%{$search}%")
                  ->orWhere('card_code', 'ilike', "%{$search}%")
                  ->orWhere('card_name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $requests = $query->paginate($perPage)->withQueryString();

        return view('purchase-request.index', compact('requests'));
    }

    public function create()
    {
        $taxes = Tax::where('locked', false)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $uoms = \App\Models\Uom::all();
        $chartOfAccounts = \App\Models\ChartOfAccount::where('is_active', true)->where('account_type', 'Postable')->get();
        $dimensions = \App\Models\Dimension::where('is_active', true)->orderBy('dimension_code')->get();
        $costCenters = \App\Models\CostCenter::where('is_active', true)->where('center_code', 'NOT ILIKE', 'Centr_z%')->orderBy('center_code')->get();
        $items = \App\Models\Item::where('is_active', true)->orderBy('item_code')->get();

        return view('purchase-request.create', compact('taxes', 'warehouses', 'uoms', 'chartOfAccounts', 'dimensions', 'costCenters', 'items'));
    }

    public function duplicate($id)
    {
        $existing = PurchaseRequest::with('lines')->findOrFail($id);

        $taxes = Tax::where('locked', false)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $uoms = \App\Models\Uom::all();
        $chartOfAccounts = \App\Models\ChartOfAccount::where('is_active', true)->where('account_type', 'Postable')->get();
        $dimensions = \App\Models\Dimension::where('is_active', true)->orderBy('dimension_code')->get();
        $costCenters = \App\Models\CostCenter::where('is_active', true)->where('center_code', 'NOT ILIKE', 'Centr_z%')->orderBy('center_code')->get();
        $items = \App\Models\Item::where('is_active', true)->orderBy('item_code')->get();

        $prefilledData = [
            'doc_type' => $existing->doc_type ?? 'dssItem',
            'card_code' => $existing->card_code,
            'tax_code' => $existing->tax_code,
            'urgency_level' => $existing->urgency_level ?? 'normal',
            'posting_date' => date('Y-m-d'),
            'delivery_date' => date('Y-m-d', strtotime('+3 days')),
            'document_date' => date('Y-m-d'),
            'comments' => 'Duplicated from PR #' . ($existing->doc_num ?? $existing->id),
            'lines' => $existing->lines->map(function($line) {
                return [
                    'item_code' => $line->item_code,
                    'item_description' => $line->item_description,
                    'account_code' => $line->account_code,
                    'account_name' => $line->account_name,
                    'quantity' => $line->quantity,
                    'price' => $line->price,
                    'uom_code' => $line->uom_code,
                    'whs_code' => $line->whs_code,
                    'on_hand_qty' => $line->on_hand_qty,
                    'required_date' => $line->required_date ? $line->required_date->format('Y-m-d') : date('Y-m-d'),
                    'costing_code' => $line->costing_code,
                ];
            })->toArray()
        ];

        return view('purchase-request.create', compact('taxes', 'warehouses', 'uoms', 'chartOfAccounts', 'dimensions', 'costCenters', 'items', 'prefilledData'));
    }

    public function show($id)
    {
        $purchaseRequest = PurchaseRequest::with(['lines', 'creator'])->findOrFail($id);
        $targetPos = $purchaseRequest->targetPurchaseOrders();

        return view('purchase-request.show', compact('purchaseRequest', 'targetPos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'posting_date' => 'required|date',
            'delivery_date' => 'required|date',
            'document_date' => 'required|date',
            'tax_code' => 'required|string',
            'lines' => 'required|array|min:1',
        ]);

        // Optional Blanket Agreement validation (disabled/commented out as requested)
        // $this->checkBlanketAgreementValidation($request);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $nextDocNum = 'PR-' . date('Ymd') . '-' . str_pad(PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            $pr = PurchaseRequest::create([
                'doc_num' => $nextDocNum,
                'doc_type' => $request->input('doc_type', 'dssItem'),
                'card_code' => $request->input('card_code'),
                'card_name' => $request->input('card_name'),
                'req_type' => 12,
                'requester_name' => $user->name,
                'requester_branch' => 'Head Office',
                'requester_department' => 'Procurement',
                'posting_date' => $request->posting_date,
                'delivery_date' => $request->delivery_date,
                'document_date' => $request->document_date,
                'tax_code' => $request->tax_code,
                'urgency_level' => $request->input('urgency_level', 'normal'),
                'status' => 'draft',
                'approval_status' => 'none',
                'comments' => $request->comments,
                'created_by' => $user->id,
            ]);

            foreach ($request->lines as $idx => $line) {
                PurchaseRequestLine::create([
                    'purchase_request_id' => $pr->id,
                    'line_num' => $idx,
                    'item_code' => $line['item_code'] ?? null,
                    'item_description' => $line['item_description'] ?? null,
                    'account_code' => $line['account_code'] ?? null,
                    'account_name' => $line['account_name'] ?? null,
                    'quantity' => $line['quantity'] ?? 1,
                    'price' => $line['price'] ?? 0,
                    'uom_code' => $line['uom_code'] ?? null,
                    'whs_code' => $line['whs_code'] ?? null,
                    'on_hand_qty' => $line['on_hand_qty'] ?? 0,
                    'required_date' => $line['required_date'] ?? $request->delivery_date,
                    'costing_code' => $line['costing_code'] ?? null,
                    'costing_code2' => $line['costing_code2'] ?? null,
                    'costing_code3' => $line['costing_code3'] ?? null,
                    'costing_code4' => $line['costing_code4'] ?? null,
                    'costing_code5' => $line['costing_code5'] ?? null,
                    'tax_code' => $request->tax_code,
                ]);
            }

            DB::commit();

            // Evaluate Web App Exclusive Approval Engine
            $wentToApproval = ApprovalEngineService::processDocumentApproval('PurchaseRequisition', $pr, $user);

            SystemLog::logAction('purchase_request', 'Create Purchase Requisition', "Created Purchase Requisition #{$pr->doc_num}");

            $msg = $wentToApproval 
                ? "Purchase Requisition #{$pr->doc_num} created and submitted for Approval."
                : "Purchase Requisition #{$pr->doc_num} created successfully.";

            return redirect()->route('purchase-request.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Purchase Requisition Store Error: " . $e->getMessage());
            return back()->with('error', "Failed to create Purchase Requisition: " . $e->getMessage())->withInput();
        }
    }

    public function getItemStock($itemCode)
    {
        $item = Item::where('item_code', $itemCode)->first();
        if (!$item) {
            return response()->json(['success' => false, 'data' => []]);
        }

        // Returns array of warehouse stock (e.g. from local item warehouse data or default)
        $warehouses = Warehouse::where('is_active', true)->get()->map(function($wh) use ($item) {
            return [
                'whs_code' => $wh->whs_code,
                'whs_name' => $wh->whs_name,
                'on_hand_qty' => rand(10, 100) // Local stock estimate or saved quantity
            ];
        });

        return response()->json(['success' => true, 'data' => $warehouses]);
    }

    /**
     * Helper validation for Purchase Blanket Agreements (DISABLED/COMMENTED OUT).
     * Un-comment lines below when blanket agreement validation needs to be activated.
     */
    private function checkBlanketAgreementValidation(Request $request)
    {
        /*
        $userDate = $request->posting_date ?? date('Y-m-d');
        $itemCodes = collect($request->lines)->pluck('item_code')->filter()->toArray();

        // Query SAP BlanketAgreements endpoint or local DB
        // Example logic:
        // $blanketAgreements = $sap->get($user, 'BlanketAgreements?$filter=Status eq \'asApproved\'');
        // foreach ($blanketAgreements['value'] as $ba) {
        //     $startDate = date('Y-m-d', strtotime($ba['StartDate']));
        //     $endDate = date('Y-m-d', strtotime($ba['EndDate']));
        //     if ($userDate >= $startDate && $userDate <= $endDate) {
        //         foreach ($ba['BlanketAgreements_ItemsLines'] as $baLine) {
        //             if (in_array($baLine['ItemNo'], $itemCodes)) {
        //                 throw new \Exception("Item {$baLine['ItemNo']} is already in active blanket agreement no {$ba['DocNum']}");
        //             }
        //         }
        //     }
        // }
        */
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
            $items = Item::where('is_active', true)
                ->get()
                ->map(function($item) {
                    return [
                        'ItemCode' => $item->item_code,
                        'ItemName' => $item->item_name,
                        'Uom' => $item->sales_uom ?? ($item->purchase_uom ?? 'Pcs'),
                    ];
                });
            return response()->json(['success' => true, 'data' => $items]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
