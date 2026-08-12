<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    use HasFactory;

    protected $table = 'uoms';

    protected $fillable = [
        'abs_entry',
        'code',
        'name',
        'sync_status',
        'sap_status',
        'sync_error',
    ];
}
