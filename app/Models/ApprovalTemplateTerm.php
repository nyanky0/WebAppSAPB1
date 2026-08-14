<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalTemplateTerm extends Model
{
    protected $fillable = [
        'approval_template_id',
        'target_level',
        'field_name',
        'operator',
        'value',
    ];

    public function template()
    {
        return $this->belongsTo(ApprovalTemplate::class, 'approval_template_id');
    }
}
