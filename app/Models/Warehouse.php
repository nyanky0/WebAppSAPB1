<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';
    protected $primaryKey = 'whs_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'whs_code',
        'whs_name',
        'is_active',
        'location',
        'bin_enabled',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bin_enabled' => 'boolean',
    ];

    public function bins()
    {
        return $this->hasMany(BinLocation::class, 'whs_code', 'whs_code');
    }
}
