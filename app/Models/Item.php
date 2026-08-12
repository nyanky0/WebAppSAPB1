<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $primaryKey = 'item_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'item_code',
        'item_name',
        'foreign_name',
        'uom',
        'inventory_uom',
        'purchasing_uom',
        'sales_uom',
        'uom_group_type',
        'uom_group',
        'item_group',
        'is_active',
        'sync_status',
        'sap_status',
        'sync_error'
    ];
}
