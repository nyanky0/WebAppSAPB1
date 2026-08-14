<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('doc_num')->nullable();
            $table->string('card_code');
            $table->string('card_name')->nullable();
            $table->date('document_date');
            $table->date('due_date');
            $table->string('urgency_level')->default('normal'); // 'low', 'normal', 'high'
            $table->string('status')->default('draft'); // 'draft', 'open', 'close', 'cancel'
            $table->string('approval_status')->default('none'); // 'none', 'pending', 'approved', 'rejected'
            $table->unsignedBigInteger('base_requisition_id')->nullable();
            $table->text('comments')->nullable();
            $table->string('created_by')->nullable();
            $table->foreign('created_by')->references('uid7')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('purchase_quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_quotation_id')->constrained('purchase_quotations')->onDelete('cascade');
            $table->integer('line_num')->default(0);
            $table->string('item_code');
            $table->string('item_description')->nullable();
            $table->date('required_date')->nullable();
            $table->decimal('required_qty', 15, 2)->default(0);
            $table->date('quoted_date')->nullable();
            $table->decimal('quoted_qty', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->string('uom_code')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('whs_code')->nullable();
            $table->decimal('on_hand_qty', 15, 2)->default(0);
            $table->string('costing_code')->nullable();
            $table->unsignedBigInteger('base_requisition_line_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_quotation_lines');
        Schema::dropIfExists('purchase_quotations');
    }
};
