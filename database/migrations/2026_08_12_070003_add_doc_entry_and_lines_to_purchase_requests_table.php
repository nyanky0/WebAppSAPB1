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
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'doc_entry')) {
                $table->integer('doc_entry')->nullable()->index();
            }
            if (!Schema::hasColumn('purchase_requests', 'doc_num')) {
                $table->integer('doc_num')->nullable()->index();
            }
        });

        Schema::table('purchase_request_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_request_lines', 'line_num')) {
                $table->integer('line_num')->default(0);
            }
            if (!Schema::hasColumn('purchase_request_lines', 'target_type')) {
                $table->integer('target_type')->nullable();
            }
            if (!Schema::hasColumn('purchase_request_lines', 'target_entry')) {
                $table->integer('target_entry')->nullable();
            }
            if (!Schema::hasColumn('purchase_request_lines', 'target_line')) {
                $table->integer('target_line')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['doc_entry', 'doc_num']);
        });

        Schema::table('purchase_request_lines', function (Blueprint $table) {
            $table->dropColumn(['line_num', 'target_type', 'target_entry', 'target_line']);
        });
    }
};
