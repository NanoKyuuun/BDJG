<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('provider')->default('DUITKU');
            $table->string('merchant_order_id')->unique();
            $table->string('provider_reference')->nullable()->index();
            $table->unsignedBigInteger('amount');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('PENDING');
            $table->text('payment_url')->nullable();
            $table->string('va_number')->nullable();
            $table->text('qr_string')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
