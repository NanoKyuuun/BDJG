<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('client_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company_or_institution')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->date('preferred_date')->nullable();
            $table->date('alternative_date')->nullable();
            $table->string('location')->nullable();
            $table->text('project_brief');
            $table->json('reference_links')->nullable();
            $table->unsignedBigInteger('estimated_budget')->nullable();
            $table->string('source')->default('WEBSITE');
            $table->string('status')->default('NEW');
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('lost_reason')->nullable();
            $table->text('notes_internal')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
