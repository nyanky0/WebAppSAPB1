<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'disabled',
        'sync_status',
        'sap_status',
        'sync_error',
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];
}
