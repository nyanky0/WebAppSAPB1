<?php

namespace App\Http\Controllers;

use App\Models\ApprovalTemplate;
use App\Models\ApprovalStage;
use App\Models\ApprovalTemplateStage;
use App\Models\ApprovalTemplateTerm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalTemplateController extends Controller
{
    public function index()
    {
        $templates = ApprovalTemplate::with(['stages.stage', 'terms'])->latest()->paginate(20);
        return view('approvals.templates.index', compact('templates'));
    }

    public function create()
    {
        $stages = ApprovalStage::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('approvals.templates.form', compact('stages', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_document' => 'required|string',
            'stages' => 'required|array|min:1',
            'terms_type' => 'required|in:always,conditional',
        ]);

        DB::transaction(function() use ($request) {
            $template = ApprovalTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'target_document' => $request->target_document,
                'originator_user_ids' => $request->filled('originator_user_ids') ? array_map('intval', $request->originator_user_ids) : [],
                'terms_type' => $request->terms_type,
            ]);

            // Save ordered stages
            foreach ($request->stages as $order => $stageId) {
                ApprovalTemplateStage::create([
                    'approval_template_id' => $template->id,
                    'approval_stage_id' => $stageId,
                    'stage_order' => $order + 1,
                ]);
            }

            // Save terms if conditional
            if ($request->terms_type === 'conditional' && is_array($request->terms)) {
                foreach ($request->terms as $t) {
                    if (!empty($t['field_name'])) {
                        ApprovalTemplateTerm::create([
                            'approval_template_id' => $template->id,
                            'target_level' => $t['target_level'] ?? 'header',
                            'field_name' => $t['field_name'],
                            'operator' => $t['operator'] ?? '=',
                            'value' => $t['value'] ?? '',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('approvals.templates.index')->with('success', 'Approval Template created successfully.');
    }

    public function edit($id)
    {
        $template = ApprovalTemplate::with(['stages', 'terms'])->findOrFail($id);
        $stages = ApprovalStage::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('approvals.templates.form', compact('template', 'stages', 'users'));
    }

    public function update(Request $request, $id)
    {
        $template = ApprovalTemplate::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'target_document' => 'required|string',
            'stages' => 'required|array|min:1',
            'terms_type' => 'required|in:always,conditional',
        ]);

        DB::transaction(function() use ($request, $template) {
            $template->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'target_document' => $request->target_document,
                'originator_user_ids' => $request->filled('originator_user_ids') ? array_map('intval', $request->originator_user_ids) : [],
                'terms_type' => $request->terms_type,
            ]);

            // Re-create stages
            $template->stages()->delete();
            foreach ($request->stages as $order => $stageId) {
                ApprovalTemplateStage::create([
                    'approval_template_id' => $template->id,
                    'approval_stage_id' => $stageId,
                    'stage_order' => $order + 1,
                ]);
            }

            // Re-create terms
            $template->terms()->delete();
            if ($request->terms_type === 'conditional' && is_array($request->terms)) {
                foreach ($request->terms as $t) {
                    if (!empty($t['field_name'])) {
                        ApprovalTemplateTerm::create([
                            'approval_template_id' => $template->id,
                            'target_level' => $t['target_level'] ?? 'header',
                            'field_name' => $t['field_name'],
                            'operator' => $t['operator'] ?? '=',
                            'value' => $t['value'] ?? '',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('approvals.templates.index')->with('success', 'Approval Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = ApprovalTemplate::findOrFail($id);
        $template->delete();
        return redirect()->route('approvals.templates.index')->with('success', 'Approval Template deleted successfully.');
    }
}
