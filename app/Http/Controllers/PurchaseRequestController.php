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
        return view('purchase-request.create', compact('taxes'));
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

    public function getItems()
    {
        try {
            $items = \App\Models\Item::where('is_active', true)
                ->get()
                ->map(function($item) {
                    return [
                        'ItemCode' => $item->item_code,
                        'ItemName' => $item->item_name
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
            'document_date' => 'required|date',
            'valid_until' => 'required|date',
            'posting_date' => 'required|date',
            'required_date' => 'required|date',
            'vendor' => 'required|string',
            'tax_code' => 'required|string',
            'instant_sync' => 'nullable|boolean',
            'lines' => 'required|array|min:1',
            'lines.*.item_code' => 'required|string',
            'lines.*.item_description' => 'nullable|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $pr = PurchaseRequest::create([
                'sync_status' => 'Draft',
                'document_date' => $validated['document_date'],
                'valid_until' => $validated['valid_until'],
                'posting_date' => $validated['posting_date'],
                'required_date' => $validated['required_date'],
                'requester' => auth()->user()->sap_user,
                'vendor' => $validated['vendor'],
                'tax_code' => $validated['tax_code'],
                'created_by' => auth()->user()->uid7,
            ]);

            foreach ($validated['lines'] as $line) {
                $pr->lines()->create([
                    'item_code' => $line['item_code'],
                    'item_description' => $line['item_description'] ?? null,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'] ?? 0,
                    'tax_code' => $validated['tax_code'],
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

            $lines = [];
            foreach ($pr->lines as $line) {
                $lines[] = [
                    'ItemCode' => $line->item_code,
                    'Quantity' => (float) $line->quantity,
                    'UnitPrice' => (float) $line->price,
                    'VatGroup' => $line->tax_code,
                    'LineVendor' => $pr->vendor
                ];
            }

            $payload = [
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
