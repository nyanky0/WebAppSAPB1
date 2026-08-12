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
            $table->string('sync_status')->default('Draft'); // Draft, Synced, Failed
            $table->string('sap_status')->nullable();
            $table->date('document_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('required_date')->nullable();
            $table->string('requester')->nullable();
            $table->string('vendor')->nullable();
            $table->string('tax_code')->nullable();
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
