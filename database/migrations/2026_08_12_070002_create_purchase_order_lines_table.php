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
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->integer('line_num')->default(0);
            $table->string('item_code')->nullable();
            $table->string('item_description')->nullable();
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();
            $table->decimal('quantity', 19, 6)->default(1);
            $table->decimal('price', 19, 6)->default(0);
            $table->string('uom_code')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('costing_code')->nullable();
            $table->string('costing_code2')->nullable();
            $table->string('costing_code3')->nullable();
            $table->string('costing_code4')->nullable();
            $table->string('costing_code5')->nullable();
            $table->integer('base_type')->nullable(); // 1470000113 for Purchase Request
            $table->integer('base_entry')->nullable(); // PR DocEntry
            $table->integer('base_line')->nullable(); // PR LineNum
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
    }
};
