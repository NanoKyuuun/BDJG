<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('project_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->unsignedBigInteger('accepted_quotation_version_id')->nullable();
            $table->string('name');
            $table->string('service_name_snapshot')->nullable();
            $table->string('package_name_snapshot')->nullable();
            $table->unsignedBigInteger('contract_value')->default(0);
            $table->string('status')->default('PRE_PRODUCTION');
            $table->date('start_date')->nullable();
            $table->date('shoot_date')->nullable();
            $table->date('deadline')->nullable();
            $table->string('location')->nullable();
            $table->text('brief')->nullable();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes_internal')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Link invoices table to projects
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::dropIfExists('projects');
    }
};
