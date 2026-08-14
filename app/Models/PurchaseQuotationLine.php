<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseQuotationLine extends Model
{
    protected $fillable = [
        'purchase_quotation_id',
        'line_num',
        'item_code',
        'item_description',
        'required_date',
        'required_qty',
        'quoted_date',
        'quoted_qty',
        'unit_price',
        'uom_code',
        'tax_code',
        'whs_code',
        'on_hand_qty',
        'costing_code',
        'base_requisition_line_id',
    ];

    public function purchaseQuotation()
    {
        return $this->belongsTo(PurchaseQuotation::class);
    }
}
