<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
    protected $primaryKey = 'group_code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'group_code',
        'group_name',
        'default_uom_group',
        'default_uom',
        'sync_status',
        'sap_status',
        'sync_error'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'item_group', 'group_code');
    }
}