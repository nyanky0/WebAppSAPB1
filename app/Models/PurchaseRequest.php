<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'sap_number', 'sync_status', 'sap_status', 'doc_type', 'document_date', 'valid_until', 'posting_date', 'required_date', 
        'requester', 'vendor', 'whs_code', 'tax_code', 'sync_error', 'created_by'
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseRequestLine::class);
    }
}
