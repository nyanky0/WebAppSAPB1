<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dimension extends Model
{
    protected $fillable = [
        'dimension_code',
        'dimension_name',
        'is_active',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    public function costCenters()
    {
        return $this->hasMany(CostCenter::class, 'dimension_code', 'dimension_code');
    }
}
