<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_comments', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('revision_id')->constrained('revisions')->cascadeOnDelete();
            $table->foreignId('media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('timecode_seconds', 8, 3)->nullable(); // e.g. 14.520s
            $table->unsignedInteger('frame_number')->nullable();
            $table->json('coordinates')->nullable(); // e.g. {"x": 0.45, "y": 0.62}
            $table->text('comment');
            $table->string('status')->default('OPEN');
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['revision_id', 'status']);
            $table->index(['media_asset_id', 'timecode_seconds']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_comments');
    }
};
