<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $fillable = [
        'center_code',
        'center_name',
        'dimension_code',
        'is_active',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    public function dimension()
    {
        return $this->belongsTo(Dimension::class, 'dimension_code', 'dimension_code');
    }
}
