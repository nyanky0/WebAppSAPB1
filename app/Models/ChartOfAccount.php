<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'external_code',
        'currency',
        'levels',
        'account_type',
        'is_control_account',
        'is_cash_account',
        'is_active',
        'category',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    protected $casts = [
        'is_control_account' => 'boolean',
        'is_cash_account' => 'boolean',
        'is_active' => 'boolean',
        'levels' => 'integer',
    ];
}
