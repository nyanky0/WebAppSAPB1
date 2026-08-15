<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('sap_number')->nullable();
            $table->string('sync_status')->default('Draft'); // Draft, Synced, Failed
            $table->string('sap_status')->nullable();
            $table->string('doc_type')->default('dssItem'); // dssItem or dssService
            $table->date('document_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('required_date')->nullable();
            $table->string('requester')->nullable();
            $table->string('vendor')->nullable();
            $table->string('whs_code')->nullable();
            $table->integer('doc_entry')->nullable()->index();
            $table->integer('doc_num')->nullable()->index();
            $table->string('urgency_level')->default('normal'); // low, normal, high
            $table->string('approval_status')->default('none'); // none, pending, approved, rejected
            $table->string('status')->default('draft'); // draft, open, close, cancel
            $table->text('sync_error')->nullable();
            $table->string('created_by')->nullable();
            $table->foreign('created_by')->references('uid7')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
