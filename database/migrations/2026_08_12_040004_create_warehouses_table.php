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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->string('whs_code')->primary();
            $table->string('whs_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('location')->nullable();
            $table->boolean('bin_enabled')->default(false);
            $table->string('sync_status')->default('Draft');
            $table->string('sap_status')->nullable();
            $table->text('sync_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
