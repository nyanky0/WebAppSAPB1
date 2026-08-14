<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Http\Request;

class ApprovalStageController extends Controller
{
    public function index()
    {
        $stages = ApprovalStage::latest()->paginate(20);
        $users = User::orderBy('name')->get();
        return view('approvals.stages.index', compact('stages', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_approvals' => 'required|integer|min:1',
            'min_rejections' => 'required|integer|min:1',
            'approver_user_ids' => 'required|array|min:1',
        ]);

        ApprovalStage::create([
            'name' => $request->name,
            'description' => $request->description,
            'min_approvals' => $request->min_approvals,
            'min_rejections' => $request->min_rejections,
            'approver_user_ids' => array_map('strval', $request->approver_user_ids),
        ]);

        return redirect()->route('approvals.stages.index')->with('success', 'Approval Stage created successfully.');
    }

    public function update(Request $request, $id)
    {
        $stage = ApprovalStage::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'min_approvals' => 'required|integer|min:1',
            'min_rejections' => 'required|integer|min:1',
            'approver_user_ids' => 'required|array|min:1',
        ]);

        $stage->update([
            'name' => $request->name,
            'description' => $request->description,
            'min_approvals' => $request->min_approvals,
            'min_rejections' => $request->min_rejections,
            'approver_user_ids' => array_map('strval', $request->approver_user_ids),
        ]);

        return redirect()->route('approvals.stages.index')->with('success', 'Approval Stage updated successfully.');
    }

    public function destroy($id)
    {
        $stage = ApprovalStage::findOrFail($id);
        $stage->delete();
        return redirect()->route('approvals.stages.index')->with('success', 'Approval Stage deleted successfully.');
    }
}
