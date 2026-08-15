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
        Schema::create('withholding_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('wt_code')->nullable();
            $table->string('name')->nullable();
            $table->string('wt_name')->nullable();
            $table->decimal('rate', 10, 4)->default(0);
            $table->string('category')->nullable();
            $table->string('gl_account')->nullable();
            $table->boolean('inactive')->default(false);
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
        Schema::dropIfExists('withholding_taxes');
    }
};
