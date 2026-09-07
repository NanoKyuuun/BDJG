<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_attachments', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename');
            $table->string('original_name');
            $table->string('storage_key');
            $table->string('disk')->default('media');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('attachment_type')->default('MOODBOARD'); // MOODBOARD, RUNDOWN, VENUE_LAYOUT, CONTRACT_SAMPLE, OTHER
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_attachments');
    }
};
