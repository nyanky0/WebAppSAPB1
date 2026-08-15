<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPartner extends Model
{
    protected $fillable = [
        'bp_code',
        'card_code',
        'name',
        'card_name',
        'bp_name',
        'card_type',
        'type',
        'group_code',
        'phone1',
        'email',
        'currency',
        'contact_persons',
        'sync_status',
        'sap_status',
        'sync_error'
    ];

    protected $casts = [
        'contact_persons' => 'array'
    ];
}
