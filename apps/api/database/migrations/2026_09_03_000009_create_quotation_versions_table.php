<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('project_name');
            $table->string('service_name_snapshot')->nullable();
            $table->string('package_name_snapshot')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->string('dp_type')->default('PERCENTAGE');
            $table->decimal('dp_value', 8, 2)->default(50.00);
            $table->unsignedBigInteger('dp_amount')->default(0);
            $table->unsignedBigInteger('remaining_amount')->default(0);
            $table->text('terms')->nullable();
            $table->text('revision_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['quotation_id', 'version_number']);
        });

        // Add foreign key constraint back to quotations table for current_version_id and accepted_version_id
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('current_version_id')->references('id')->on('quotation_versions')->nullOnDelete();
            $table->foreign('accepted_version_id')->references('id')->on('quotation_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
            $table->dropForeign(['accepted_version_id']);
        });

        Schema::dropIfExists('quotation_versions');
    }
};
