<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpGroup extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'sync_status',
        'sap_status',
        'sync_error'
    ];
}
