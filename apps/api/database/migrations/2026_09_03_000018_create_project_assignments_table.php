<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('worker_profiles')->cascadeOnDelete();
            $table->string('assignment_role')->default('CREW');
            $table->unsignedBigInteger('fee_amount')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('removed_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'worker_id', 'assignment_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_assignments');
    }
};
