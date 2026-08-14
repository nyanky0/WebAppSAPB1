<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'target_document',
        'originator_user_ids',
        'terms_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'originator_user_ids' => 'array',
    ];

    public function stages()
    {
        return $this->hasMany(ApprovalTemplateStage::class)->orderBy('stage_order', 'asc');
    }

    public function terms()
    {
        return $this->hasMany(ApprovalTemplateTerm::class);
    }
}
