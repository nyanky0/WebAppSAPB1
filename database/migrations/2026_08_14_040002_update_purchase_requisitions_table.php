<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'urgency_level')) {
                $table->string('urgency_level')->default('normal'); // 'low', 'normal', 'high'
            }
            if (!Schema::hasColumn('purchase_requests', 'approval_status')) {
                $table->string('approval_status')->default('none'); // 'none', 'pending', 'approved', 'rejected'
            }
        });

        Schema::table('purchase_request_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_request_lines', 'whs_code')) {
                $table->string('whs_code')->nullable();
            }
            if (!Schema::hasColumn('purchase_request_lines', 'on_hand_qty')) {
                $table->decimal('on_hand_qty', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('purchase_request_lines', 'required_date')) {
                $table->date('required_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['urgency_level', 'approval_status']);
        });
        Schema::table('purchase_request_lines', function (Blueprint $table) {
            $table->dropColumn(['whs_code', 'on_hand_qty', 'required_date']);
        });
    }
};
