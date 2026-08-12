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
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('center_code')->unique();
            $table->string('center_name')->nullable();
            $table->integer('dimension_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('sync_status')->default('Synced');
            $table->string('sap_status')->default('Created');
            $table->text('sync_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
