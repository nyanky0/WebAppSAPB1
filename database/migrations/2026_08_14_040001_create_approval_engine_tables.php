<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Approval Stages
        Schema::create('approval_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('min_approvals')->default(1);
            $table->integer('min_rejections')->default(1);
            $table->json('approver_user_ids')->nullable(); // Array of user IDs
            $table->timestamps();
        });

        // Approval Templates
        Schema::create('approval_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('target_document'); // e.g. PurchaseRequisition, PurchaseQuotation, PurchaseOrder
            $table->json('originator_user_ids')->nullable(); // Array of originator user IDs
            $table->string('terms_type')->default('always'); // 'always' or 'conditional'
            $table->timestamps();
        });

        // Template Stages (ordered stages)
        Schema::create('approval_template_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_template_id')->constrained('approval_templates')->onDelete('cascade');
            $table->foreignId('approval_stage_id')->constrained('approval_stages')->onDelete('cascade');
            $table->integer('stage_order')->default(1);
            $table->timestamps();
        });

        // Template Terms (conditional terms)
        Schema::create('approval_template_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_template_id')->constrained('approval_templates')->onDelete('cascade');
            $table->string('target_level')->default('header'); // 'header' or 'detail'
            $table->string('field_name');
            $table->string('operator'); // '=', '>', '>=', '<', '<=', '!='
            $table->string('value');
            $table->timestamps();
        });

        // Approval Requests
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // e.g. PurchaseRequisition, PurchaseQuotation
            $table->unsignedBigInteger('document_id');
            $table->foreignId('approval_template_id')->constrained('approval_templates')->onDelete('cascade');
            $table->foreignId('current_stage_id')->nullable()->constrained('approval_stages')->onDelete('set null');
            $table->integer('current_stage_order')->default(1);
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->string('originator_id');
            $table->foreign('originator_id')->references('uid7')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Approval Request Decisions (individual votes)
        Schema::create('approval_request_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->onDelete('cascade');
            $table->foreignId('approval_stage_id')->constrained('approval_stages')->onDelete('cascade');
            $table->string('user_id');
            $table->foreign('user_id')->references('uid7')->on('users')->onDelete('cascade');
            $table->string('decision'); // 'approved', 'rejected'
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_decisions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_template_terms');
        Schema::dropIfExists('approval_template_stages');
        Schema::dropIfExists('approval_templates');
        Schema::dropIfExists('approval_stages');
    }
};
