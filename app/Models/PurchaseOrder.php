<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'doc_entry',
        'doc_num',
        'sync_status',
        'sap_status',
        'doc_type',
        'card_code',
        'card_name',
        'posting_date',
        'delivery_date',
        'document_date',
        'whs_code',
        'tax_code',
        'comments',
        'sync_error',
        'created_by',
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'uid7');
    }

    // Connected Base Purchase Request (if copied from PR)
    public function basePurchaseRequest()
    {
        $baseEntry = $this->lines()->whereNotNull('base_entry')->value('base_entry');
        if ($baseEntry) {
            return PurchaseRequest::where('doc_entry', $baseEntry)->orWhere('id', $baseEntry)->first();
        }
        return null;
    }
}
