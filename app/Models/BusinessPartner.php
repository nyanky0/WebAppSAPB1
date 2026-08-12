<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPartner extends Model
{
    protected $fillable = [
        'bp_code',
        'name',
        'type',
        'contact_persons',
        'sync_status',
        'sap_status',
        'sync_error'
    ];

    protected $casts = [
        'contact_persons' => 'array'
    ];
}
