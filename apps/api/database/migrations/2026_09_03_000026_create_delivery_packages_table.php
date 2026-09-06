<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_packages', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title')->default('Final Deliverables Package');
            $table->string('status')->default('PREPARING');
            $table->string('storage_key')->nullable();
            $table->string('disk')->default('media');
            $table->unsignedBigInteger('total_size_bytes')->default(0);
            $table->unsignedInteger('file_count')->default(0);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('released_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
        });

        Schema::create('delivery_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_package_id')->constrained('delivery_packages')->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['delivery_package_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_package_items');
        Schema::dropIfExists('delivery_packages');
    }
};
