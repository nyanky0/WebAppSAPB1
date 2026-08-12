<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'line_num',
        'item_code',
        'item_description',
        'account_code',
        'account_name',
        'quantity',
        'price',
        'uom_code',
        'tax_code',
        'costing_code',
        'costing_code2',
        'costing_code3',
        'costing_code4',
        'costing_code5',
        'base_type',
        'base_entry',
        'base_line',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
