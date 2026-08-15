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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->string('code')->primary(); // AcctCode
            $table->string('acct_code')->nullable(); // Alternate AcctCode field
            $table->string('name')->nullable(); // AcctName
            $table->string('acct_name')->nullable(); // Alternate AcctName field
            $table->string('external_code')->nullable(); // FormatCode
            $table->string('currency')->nullable(); // AcctCurrency
            $table->integer('levels')->default(1);
            $table->string('account_type')->default('Postable'); // Postable or Title
            $table->boolean('is_control_account')->default(false);
            $table->boolean('is_cash_account')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('category')->default('Assets'); // Assets, Liabilities, Capital & Reserves, Turnover, Cost of Sales, Operating Costs, Non-Operating Income & Expenditure, Taxation & Extraordinary Items
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
        Schema::dropIfExists('chart_of_accounts');
    }
};
