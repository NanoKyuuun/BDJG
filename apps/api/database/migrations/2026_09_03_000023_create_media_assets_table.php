<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pending_upload_id')->nullable()->constrained('pending_uploads')->nullOnDelete();
            $table->string('filename');
            $table->string('original_name');
            $table->string('storage_key')->unique();
            $table->string('disk')->default('media');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('category')->default('INTERNAL_DRAFT'); // RAW_FOOTAGE, PHOTO_SELECTION, INTERNAL_DRAFT, CLIENT_PREVIEW, FINAL_MASTER, ATTACHMENT
            $table->string('visibility')->default('INTERNAL'); // INTERNAL, ASSIGNED_WORKERS, CLIENT_SHARED, CLIENT_PREVIEW, FINAL_RELEASED, PUBLIC
            $table->unsignedInteger('version_number')->default(1);
            $table->string('processing_status')->default('READY'); // PENDING, PROCESSING, READY, FAILED
            $table->json('metadata')->nullable(); // duration, dimensions, codecs, etc.
            $table->foreignId('released_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'visibility']);
            $table->index(['project_id', 'category']);
            $table->index(['project_id', 'processing_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
