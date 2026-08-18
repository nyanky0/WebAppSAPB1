<?php

namespace App\Http\Controllers;

use App\Models\PurchaseQuotation;
use App\Models\PurchaseQuotationLine;
use App\Models\PurchaseRequest;
use App\Models\Tax;
use App\Models\Warehouse;
use App\Models\SystemLog;
use App\Services\ApprovalEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseQuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseQuotation::with('lines')->latest();

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

        $quotations = $query->paginate($perPage)->withQueryString();

        return view('purchase-quotation.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        $vendors = \App\Models\BusinessPartner::where(function($q) {
            $q->where('type', 'Vendor')
              ->orWhere('type', 'cSupplier')
              ->orWhere('type', 'vSupplier');
        })->orderBy('name')->get();

        $taxes = Tax::where('locked', false)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $uoms = \App\Models\Uom::all();
        $costCenters = \App\Models\CostCenter::where('is_active', true)->where('center_code', 'NOT ILIKE', 'Centr_z%')->orderBy('center_code')->get();
        $items = \App\Models\Item::where('is_active', true)->orderBy('item_code')->get();

        $prefilledData = null;
        if ($request->filled('from_pr')) {
            $pr = PurchaseRequest::with('lines')->find($request->from_pr);
            if ($pr) {
                $prefilledData = [
                    'base_requisition_id' => $pr->id,
                    'card_code' => $request->input('card_code', ''),
                    'tax_code' => $pr->tax_code,
                    'urgency_level' => $pr->urgency_level ?? 'normal',
                    'document_date' => date('Y-m-d'),
                    'due_date' => date('Y-m-d', strtotime('+7 days')),
                    'comments' => "Copied from Purchase Requisition #{$pr->doc_num}",
                    'lines' => $pr->lines->map(function($l) {
                        return [
                            'item_code' => $l->item_code,
                            'item_description' => $l->item_description,
                            'required_date' => $l->required_date ? $l->required_date->format('Y-m-d') : date('Y-m-d'),
                            'required_qty' => $l->quantity,
                            'quoted_date' => date('Y-m-d'),
                            'quoted_qty' => $l->quantity,
                            'unit_price' => $l->price,
                            'uom_code' => $l->uom_code,
                            'whs_code' => $l->whs_code,
                            'on_hand_qty' => $l->on_hand_qty,
                            'costing_code' => $l->costing_code,
                            'base_requisition_line_id' => $l->id,
                        ];
                    })->toArray()
                ];
            }
        }

        return view('purchase-quotation.create', compact('vendors', 'taxes', 'warehouses', 'uoms', 'costCenters', 'items', 'prefilledData'));
    }

    public function getRequisitionsByVendor(Request $request)
    {
        // Eligible requisitions are status = 'open' or approval_status = 'approved' / 'none'
        $requisitions = PurchaseRequest::with('lines')
            ->whereIn('status', ['open', 'draft'])
            ->whereIn('approval_status', ['approved', 'none'])
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $requisitions]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'card_code' => 'required|string',
            'document_date' => 'required|date',
            'due_date' => 'required|date',
            'lines' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $vendor = \App\Models\BusinessPartner::where('bp_code', $request->card_code)->first();
            $cardName = $vendor ? $vendor->name : $request->card_code;

            $nextDocNum = 'PQ-' . date('Ymd') . '-' . str_pad(PurchaseQuotation::count() + 1, 4, '0', STR_PAD_LEFT);

            $pq = PurchaseQuotation::create([
                'doc_num' => $nextDocNum,
                'card_code' => $request->card_code,
                'card_name' => $cardName,
                'document_date' => $request->document_date,
                'due_date' => $request->due_date,
                'urgency_level' => $request->input('urgency_level', 'normal'),
                'status' => 'draft',
                'approval_status' => 'none',
                'base_requisition_id' => $request->base_requisition_id ?? null,
                'comments' => $request->comments,
                'created_by' => $user->id,
            ]);

            foreach ($request->lines as $idx => $line) {
                PurchaseQuotationLine::create([
                    'purchase_quotation_id' => $pq->id,
                    'line_num' => $idx,
                    'item_code' => $line['item_code'],
                    'item_description' => $line['item_description'] ?? null,
                    'required_date' => $line['required_date'] ?? null,
                    'required_qty' => $line['required_qty'] ?? 0,
                    'quoted_date' => $line['quoted_date'] ?? $request->document_date,
                    'quoted_qty' => $line['quoted_qty'] ?? 0,
                    'unit_price' => $line['unit_price'] ?? 0,
                    'uom_code' => $line['uom_code'] ?? null,
                    'whs_code' => $line['whs_code'] ?? null,
                    'on_hand_qty' => $line['on_hand_qty'] ?? 0,
                    'costing_code' => $line['costing_code'] ?? null,
                    'base_requisition_line_id' => $line['base_requisition_line_id'] ?? null,
                ]);
            }

            DB::commit();

            // Evaluate Approval Engine
            $wentToApproval = ApprovalEngineService::processDocumentApproval('PurchaseQuotation', $pq, $user);

            SystemLog::logAction('purchase_quotation', 'Create Purchase Quotation', "Created Purchase Quotation #{$pq->doc_num}");

            $msg = $wentToApproval 
                ? "Purchase Quotation #{$pq->doc_num} created and submitted for Approval."
                : "Purchase Quotation #{$pq->doc_num} created successfully.";

            return redirect()->route('purchase-quotation.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Purchase Quotation Store Error: " . $e->getMessage());
            return back()->with('error', "Failed to create Purchase Quotation: " . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $purchaseQuotation = PurchaseQuotation::with(['lines', 'creator', 'baseRequisition'])->findOrFail($id);
        return view('purchase-quotation.show', compact('purchaseQuotation'));
    }
}
