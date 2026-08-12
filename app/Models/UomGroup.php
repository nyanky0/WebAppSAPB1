<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UomGroup extends Model
{
    use HasFactory;

    protected $table = 'uom_groups';

    protected $fillable = [
        'abs_entry',
        'group_code',
        'group_name',
        'base_uom',
        'conversions',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    protected $casts = [
        'conversions' => 'array',
    ];
}
