<?php

namespace App\Http/Controllers;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStage;
use App\Models\PurchaseOrder;
use App\Models\PurchaseQuotation;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Default widgets configuration if not set
        $defaultWidgets = [
            'pending_approvals' => true,
            'high_urgency_pr' => true,
            'pr_summary' => true,
            'pq_summary' => true,
            'po_summary' => true,
        ];

        $userWidgets = $user->dashboard_widgets ?? $defaultWidgets;

        // 1. Pending Approvals for logged in user
        $stageIdsWhereUserApprover = ApprovalStage::all()->filter(function ($stage) use ($user) {
            return in_array($user->uid7, $stage->approver_user_ids ?? []);
        })->pluck('id')->toArray();

        $pendingApprovalsQuery = ApprovalRequest::with(['originator', 'currentStage'])
            ->where('status', 'pending')
            ->whereIn('current_stage_id', $stageIdsWhereUserApprover);

        $pendingApprovalsCount = (clone $pendingApprovalsQuery)->count();
        $pendingApprovalsList = $pendingApprovalsQuery->latest()->take(5)->get();

        // Attach document instance to each approval request
        foreach ($pendingApprovalsList as $req) {
            if ($req->document_type === 'PurchaseRequisition') {
                $req->doc_instance = PurchaseRequest::find($req->document_id);
            } elseif ($req->document_type === 'PurchaseQuotation') {
                $req->doc_instance = PurchaseQuotation::find($req->document_id);
            }
        }

        // 2. High Urgency Requisitions
        $highUrgencyPRsQuery = PurchaseRequest::where('urgency_level', 'high');
        $highUrgencyPRsCount = (clone $highUrgencyPRsQuery)->count();
        $highUrgencyPRsList = $highUrgencyPRsQuery->latest()->take(5)->get();

        // 3. Purchase Requisitions Summary
        $prTotal = PurchaseRequest::count();
        $prOpen = PurchaseRequest::where('status', 'open')->count();
        $prDraft = PurchaseRequest::where('status', 'draft')->count();
        $recentPRs = PurchaseRequest::latest()->take(5)->get();

        // 4. Purchase Quotations Summary
        $pqTotal = PurchaseQuotation::count();
        $pqOpen = PurchaseQuotation::where('status', 'open')->count();
        $pqDraft = PurchaseQuotation::where('status', 'draft')->count();
        $recentPQs = PurchaseQuotation::latest()->take(5)->get();

        // 5. Purchase Orders Summary
        $poTotal = PurchaseOrder::count();
        $poOpen = PurchaseOrder::where('status', 'open')->count();
        $poSynced = PurchaseOrder::where('sync_status', 'synced')->count();
        $recentPOs = PurchaseOrder::latest()->take(5)->get();

        return view('dashboard', compact(
            'userWidgets',
            'pendingApprovalsCount',
            'pendingApprovalsList',
            'highUrgencyPRsCount',
            'highUrgencyPRsList',
            'prTotal',
            'prOpen',
            'prDraft',
            'recentPRs',
            'pqTotal',
            'pqOpen',
            'pqDraft',
            'recentPQs',
            'poTotal',
            'poOpen',
            'poSynced',
            'recentPOs'
        ));
    }

    public function updateWidgets(Request $request)
    {
        $user = Auth::user();
        
        $widgets = [
            'pending_approvals' => $request->has('pending_approvals'),
            'high_urgency_pr' => $request->has('high_urgency_pr'),
            'pr_summary' => $request->has('pr_summary'),
            'pq_summary' => $request->has('pq_summary'),
            'po_summary' => $request->has('po_summary'),
        ];

        $user->update(['dashboard_widgets' => $widgets]);

        return redirect()->route('dashboard')->with('success', 'Dashboard layout updated successfully.');
    }
}
