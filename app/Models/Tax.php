<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'code',
        'name',
        'rate',
        'locked',
        'sync_status',
        'sap_status',
        'sync_error'
    ];
}
