<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalTemplateStage extends Model
{
    protected $fillable = [
        'approval_template_id',
        'approval_stage_id',
        'stage_order',
    ];

    public function template()
    {
        return $this->belongsTo(ApprovalTemplate::class, 'approval_template_id');
    }

    public function stage()
    {
        return $this->belongsTo(ApprovalStage::class, 'approval_stage_id');
    }
}
