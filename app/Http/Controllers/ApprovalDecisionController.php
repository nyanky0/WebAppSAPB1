<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalEngineService;
use Illuminate\Http\Request;

class ApprovalDecisionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get approval requests where current_stage has $user in approver_user_ids
        $requests = ApprovalRequest::with(['template', 'currentStage', 'originator', 'decisions.user'])
            ->where('status', 'pending')
            ->get()
            ->filter(function($req) use ($user) {
                $approvers = $req->currentStage->approver_user_ids ?? [];
                return in_array($user->id, $approvers);
            });

        return view('approvals.decisions.index', compact('requests'));
    }

    public function show($id)
    {
        $request = ApprovalRequest::with(['template', 'currentStage', 'originator', 'decisions.user'])->findOrFail($id);
        
        $document = null;
        if ($request->document_type === 'PurchaseRequisition') {
            $document = \App\Models\PurchaseRequest::with(['lines', 'creator'])->find($request->document_id);
        } elseif ($request->document_type === 'PurchaseQuotation') {
            $document = \App\Models\PurchaseQuotation::with(['lines', 'creator'])->find($request->document_id);
        } elseif ($request->document_type === 'PurchaseOrder') {
            $document = \App\Models\PurchaseOrder::with(['lines', 'creator'])->find($request->document_id);
        }

        return view('approvals.decisions.show', compact('request', 'document'));
    }

    public function vote(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comments' => 'nullable|string',
        ]);

        $appReq = ApprovalRequest::findOrFail($id);
        $user = auth()->user();

        try {
            ApprovalEngineService::recordDecision($appReq, $user, $request->decision, $request->comments);
            return redirect()->route('approvals.decisions.index')->with('success', "Decision recorded as " . ucfirst($request->decision));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
