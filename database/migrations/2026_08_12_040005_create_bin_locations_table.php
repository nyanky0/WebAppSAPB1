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
        Schema::create('bin_locations', function (Blueprint $table) {
            $table->id();
            $table->integer('abs_entry')->nullable();
            $table->string('bin_code')->nullable();
            $table->string('whs_code')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('bin_locations');
    }
};
