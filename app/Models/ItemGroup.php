<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    protected $fillable = [
        'sap_number',
        'group_name',
        'default_uom_group',
        'default_uom',
        'sync_status',
        'sap_status',
        'sync_error'
    ];
}
