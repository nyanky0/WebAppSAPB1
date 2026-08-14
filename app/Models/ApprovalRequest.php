<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'document_type',
        'document_id',
        'approval_template_id',
        'current_stage_id',
        'current_stage_order',
        'status',
        'originator_id',
    ];

    public function template()
    {
        return $this->belongsTo(ApprovalTemplate::class, 'approval_template_id');
    }

    public function currentStage()
    {
        return $this->belongsTo(ApprovalStage::class, 'current_stage_id');
    }

    public function originator()
    {
        return $this->belongsTo(User::class, 'originator_id', 'uid7');
    }

    public function decisions()
    {
        return $this->hasMany(ApprovalRequestDecision::class);
    }
}
