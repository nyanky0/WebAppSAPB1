<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'min_approvals',
        'min_rejections',
        'approver_user_ids',
    ];

    protected $casts = [
        'approver_user_ids' => 'array',
        'min_approvals' => 'integer',
        'min_rejections' => 'integer',
    ];

    public function approvers()
    {
        $ids = $this->approver_user_ids ?? [];
        return User::whereIn('uid7', $ids)->get();
    }
}
