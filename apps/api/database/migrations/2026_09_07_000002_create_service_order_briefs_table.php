<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_briefs', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('service_order_id')->unique()->constrained('service_orders')->cascadeOnDelete();
            $table->string('event_name')->nullable();
            $table->string('event_category')->default('WEDDING'); // WEDDING, COUPLE_SESSION, COMMERCIAL, CUSTOM
            $table->json('couple_session_details')->nullable();
            $table->json('wedding_details')->nullable();
            $table->json('custom_requirements')->nullable();
            $table->json('selected_add_ons')->nullable();
            $table->string('onsite_pic_name')->nullable();
            $table->string('onsite_pic_phone')->nullable();
            $table->boolean('portfolio_consent')->default(true);
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_briefs');
    }
};
