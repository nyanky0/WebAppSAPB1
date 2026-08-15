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
        Schema::create('business_partners', function (Blueprint $table) {
            $table->id();
            $table->string('bp_code')->nullable();
            $table->string('card_code')->nullable();
            $table->string('name')->nullable();
            $table->string('card_name')->nullable();
            $table->string('bp_name')->nullable();
            $table->string('card_type')->nullable();
            $table->string('type')->default('Vendor'); // Vendor or Customer
            $table->string('group_code')->nullable();
            $table->string('phone1')->nullable();
            $table->string('email')->nullable();
            $table->string('currency')->nullable();
            $table->json('contact_persons')->nullable();
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
        Schema::dropIfExists('business_partners');
    }
};
