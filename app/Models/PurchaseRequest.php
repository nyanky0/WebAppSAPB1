<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'sap_number', 'doc_entry', 'doc_num', 'sync_status', 'sap_status', 'doc_type', 'document_date', 'valid_until', 'posting_date', 'required_date', 
        'requester', 'vendor', 'whs_code', 'tax_code', 'sync_error', 'created_by'
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseRequestLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'uid7');
    }

    // Connected Target Purchase Orders (copied to PO)
    public function targetPurchaseOrders()
    {
        if ($this->doc_entry) {
            $poIds = PurchaseOrderLine::where('base_entry', $this->doc_entry)->pluck('purchase_order_id')->unique();
            return PurchaseOrder::whereIn('id', $poIds)->get();
        }
        return collect();
    }
}
