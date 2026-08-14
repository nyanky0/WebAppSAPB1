<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequestDecision extends Model
{
    protected $fillable = [
        'approval_request_id',
        'approval_stage_id',
        'user_id',
        'decision',
        'comments',
    ];

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function stage()
    {
        return $this->belongsTo(ApprovalStage::class, 'approval_stage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uid7');
    }
}
