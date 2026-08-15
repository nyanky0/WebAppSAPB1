<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithholdingTax extends Model
{
    protected $fillable = [
        'code',
        'wt_code',
        'name',
        'wt_name',
        'rate',
        'category',
        'gl_account',
        'inactive',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    protected $casts = [
        'rate' => 'float',
        'inactive' => 'boolean',
    ];
}
