<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinLocation extends Model
{
    use HasFactory;

    protected $table = 'bin_locations';

    protected $fillable = [
        'abs_entry',
        'bin_code',
        'whs_code',
        'is_active',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'whs_code', 'whs_code');
    }
}
