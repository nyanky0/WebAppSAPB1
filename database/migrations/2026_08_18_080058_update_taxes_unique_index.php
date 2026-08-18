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
        Schema::table('taxes', function (Blueprint $table) {
            // Drop the old unique index on code only
            $table->dropUnique('taxes_code_unique');
            // Create new composite unique index on code and date_from
            $table->unique(['code', 'date_from'], 'taxes_code_date_from_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            // Drop the composite unique index
            $table->dropUnique('taxes_code_date_from_unique');
            // Recreate the old unique index on code only
            $table->unique('code', 'taxes_code_unique');
        });
    }
};