<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestLine extends Model
{
    protected $fillable = [
        'purchase_request_id', 'item_code', 'item_description', 'quantity', 'price', 'uom_code', 'tax_code'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
