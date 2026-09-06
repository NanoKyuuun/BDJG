<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('name');
            $table->text('description_internal')->nullable();
            $table->unsignedBigInteger('base_price');
            $table->string('currency', 3)->default('IDR');
            $table->string('default_dp_type')->default('PERCENTAGE');
            $table->decimal('default_dp_value', 10, 2)->default(50.00);
            $table->string('status')->default('ACTIVE');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
