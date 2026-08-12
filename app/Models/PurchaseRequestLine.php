<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestLine extends Model
{
    protected $fillable = [
        'purchase_request_id', 'line_num', 'item_code', 'item_description', 'account_code', 'account_name', 
        'quantity', 'price', 'uom_code', 'tax_code', 'costing_code', 'costing_code2', 'costing_code3', 
        'costing_code4', 'costing_code5', 'target_type', 'target_entry', 'target_line'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
