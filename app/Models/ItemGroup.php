<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    protected $fillable = [
        'sap_number',
        'group_name',
        'sync_status',
        'sap_status',
        'sync_error'
    ];
}
