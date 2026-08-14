<?php

namespace App\Services;

use App\Models\ApprovalTemplate;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDecision;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class ApprovalEngineService
{
    /**
     * Evaluate a document to check if an Approval Request needs to be created.
     * Returns true if document went into approval pending status, false if auto-approved / no template match.
     */
    public static function processDocumentApproval($documentType, $document, $user)
    {
        // Find active templates matching document_type and originator
        $templates = ApprovalTemplate::where('is_active', true)
            ->where('target_document', $documentType)
            ->get();

        $matchingTemplate = null;

        foreach ($templates as $tmpl) {
            $originators = $tmpl->originator_user_ids ?? [];
            if (!empty($originators) && !in_array($user->id, $originators)) {
                continue; // Not originator
            }

            if ($tmpl->terms_type === 'always') {
                $matchingTemplate = $tmpl;
                break;
            } elseif ($tmpl->terms_type === 'conditional') {
                if (self::evaluateTerms($tmpl, $document)) {
                    $matchingTemplate = $tmpl;
                    break;
                }
            }
        }

        if ($matchingTemplate && $matchingTemplate->stages()->count() > 0) {
            $firstStageRel = $matchingTemplate->stages()->first();

            // Create Approval Request
            $approvalReq = ApprovalRequest::create([
                'document_type' => $documentType,
                'document_id' => $document->id,
                'approval_template_id' => $matchingTemplate->id,
                'current_stage_id' => $firstStageRel->approval_stage_id,
                'current_stage_order' => 1,
                'status' => 'pending',
                'originator_id' => $user->id,
            ]);

            // Update document approval status
            $document->update([
                'approval_status' => 'pending',
                'status' => 'draft',
            ]);

            SystemLog::logAction('approval', 'Submitted for Approval', "Document {$documentType} #{$document->id} submitted for approval under template '{$matchingTemplate->name}'.");

            return true;
        }

        // No template match: Auto approve
        $document->update([
            'approval_status' => 'approved',
            'status' => 'open',
        ]);

        return false;
    }

    /**
     * Evaluate conditional terms for a template against a document.
     */
    private static function evaluateTerms($template, $document)
    {
        $terms = $template->terms;
        if ($terms->isEmpty()) return true;

        foreach ($terms as $term) {
            $val = null;
            if ($term->target_level === 'header') {
                $val = $document->{$term->field_name} ?? null;
            } elseif ($term->target_level === 'detail') {
                // If line level, aggregate sum or check any line matching
                $lines = $document->lines ?? collect();
                $val = $lines->sum($term->field_name);
            }

            if (!self::compareValues($val, $term->operator, $term->value)) {
                return false;
            }
        }

        return true;
    }

    private static function compareValues($actual, $operator, $expected)
    {
        if (is_numeric($actual) && is_numeric($expected)) {
            $actual = (float) $actual;
            $expected = (float) $expected;
        }

        switch ($operator) {
            case '=': return $actual == $expected;
            case '>': return $actual > $expected;
            case '>=': return $actual >= $expected;
            case '<': return $actual < $expected;
            case '<=': return $actual <= $expected;
            case '!=': return $actual != $expected;
            case 'contains': return str_contains(strtolower((string)$actual), strtolower((string)$expected));
            default: return false;
        }
    }

    /**
     * Record a vote (approve or reject) by an approver user.
     */
    public static function recordDecision($approvalRequest, $user, $decision, $comments = null)
    {
        $currentStage = $approvalRequest->currentStage;
        if (!$currentStage) return;

        // Check if user is an approver for this stage
        $approvers = $currentStage->approver_user_ids ?? [];
        if (!in_array($user->id, $approvers)) {
            throw new \Exception("User is not an authorized approver for this stage.");
        }

        // Save or update user vote for this request & stage
        ApprovalRequestDecision::updateOrCreate(
            [
                'approval_request_id' => $approvalRequest->id,
                'approval_stage_id' => $currentStage->id,
                'user_id' => $user->id,
            ],
            [
                'decision' => $decision,
                'comments' => $comments,
            ]
        );

        // Count votes for current stage
        $decisions = ApprovalRequestDecision::where('approval_request_id', $approvalRequest->id)
            ->where('approval_stage_id', $currentStage->id)
            ->get();

        $approvalsCount = $decisions->where('decision', 'approved')->count();
        $rejectionsCount = $decisions->where('decision', 'rejected')->count();

        // Check rejection threshold
        if ($rejectionsCount >= $currentStage->min_rejections) {
            $approvalRequest->update(['status' => 'rejected']);
            self::updateDocumentApprovalStatus($approvalRequest, 'rejected');
            SystemLog::logAction('approval', 'Rejected', "Approval Request #{$approvalRequest->id} was rejected at stage {$currentStage->name}.");
            return;
        }

        // Check approval threshold
        if ($approvalsCount >= $currentStage->min_approvals) {
            // Move to next stage or approve completely
            $template = $approvalRequest->template;
            $nextStageRel = $template->stages()
                ->where('stage_order', '>', $approvalRequest->current_stage_order)
                ->orderBy('stage_order', 'asc')
                ->first();

            if ($nextStageRel) {
                $approvalRequest->update([
                    'current_stage_id' => $nextStageRel->approval_stage_id,
                    'current_stage_order' => $nextStageRel->stage_order,
                ]);
                SystemLog::logAction('approval', 'Advanced Stage', "Approval Request #{$approvalRequest->id} advanced to stage order {$nextStageRel->stage_order}.");
            } else {
                // Fully approved!
                $approvalRequest->update(['status' => 'approved']);
                self::updateDocumentApprovalStatus($approvalRequest, 'approved');
                SystemLog::logAction('approval', 'Fully Approved', "Approval Request #{$approvalRequest->id} was fully approved.");
            }
        }
    }

    private static function updateDocumentApprovalStatus($approvalRequest, $status)
    {
        $docType = $approvalRequest->document_type;
        $docId = $approvalRequest->document_id;

        $modelClass = null;
        if ($docType === 'PurchaseRequisition') {
            $modelClass = \App\Models\PurchaseRequest::class;
        } elseif ($docType === 'PurchaseQuotation') {
            $modelClass = \App\Models\PurchaseQuotation::class;
        } elseif ($docType === 'PurchaseOrder') {
            $modelClass = \App\Models\PurchaseOrder::class;
        }

        if ($modelClass) {
            $doc = $modelClass::find($docId);
            if ($doc) {
                $doc->update([
                    'approval_status' => $status,
                    'status' => ($status === 'approved') ? 'open' : 'draft',
                ]);
            }
        }
    }
}
