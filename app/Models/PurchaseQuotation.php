<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseQuotation extends Model
{
    protected $fillable = [
        'doc_num',
        'card_code',
        'card_name',
        'document_date',
        'due_date',
        'urgency_level',
        'status',
        'approval_status',
        'base_requisition_id',
        'comments',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'uid7');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseQuotationLine::class);
    }

    public function baseRequisition()
    {
        return $this->belongsTo(PurchaseRequest::class, 'base_requisition_id');
    }
}
