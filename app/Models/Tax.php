<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'code',
        'name',
        'rate',
        'date_from',
        'date_to',
        'locked',
        'sync_status',
        'sap_status',
        'sync_error'
    ];

    protected $casts = [
        'rate' => 'float',
        'date_from' => 'date',
        'date_to' => 'date',
        'locked' => 'boolean',
    ];
}
