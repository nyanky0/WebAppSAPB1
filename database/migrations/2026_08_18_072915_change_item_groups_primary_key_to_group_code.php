<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the foreign key constraints that reference item_groups.id
        // We need to check if there are any foreign keys first
        
        // Drop the primary key and make group_code the primary key
        Schema::table('item_groups', function (Blueprint $table) {
            // Make group_code not nullable and unique first
            $table->string('group_code')->nullable(false)->change();
        });
        
        // Drop the existing primary key (PostgreSQL syntax)
        DB::statement('ALTER TABLE item_groups DROP CONSTRAINT item_groups_pkey');
        
        // Add primary key on group_code
        Schema::table('item_groups', function (Blueprint $table) {
            $table->primary('group_code');
            // Drop the id column
            $table->dropColumn('id');
            // Drop sap_number since group_code will hold the SAP number
            $table->dropColumn('sap_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_groups', function (Blueprint $table) {
            // Add back id column as primary key
            $table->id();
            // Drop primary key on group_code
            $table->dropPrimary('group_code');
            // Make group_code nullable again
            $table->string('group_code')->nullable()->change();
            // Add back sap_number
            $table->integer('sap_number')->nullable();
        });
    }
};