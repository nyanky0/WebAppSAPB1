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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // admin, sap, login, scheduler
            $table->string('action');
            $table->text('details')->nullable();
            $table->string('user_id')->nullable();
            $table->foreign('user_id')->references('uid7')->on('users')->onDelete('set null');
            $table->string('ip_address')->nullable();
            $table->string('pc_name')->nullable();
            $table->boolean('instant_sync')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
