<?php

namespace Database\Seeders;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaAssetSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        $worker = User::where('email', 'worker@gmail.com')->first();
        $project = Project::first();

        if (! $project || ! $admin) {
            return;
        }

        MediaAsset::firstOrCreate(
            ['storage_key' => "projects/{$project->id}/draft/cinematic_cut_v1.mp4"],
            [
                'project_id' => $project->id,
                'uploaded_by_user_id' => $worker?->id ?? $admin->id,
                'filename' => 'cinematic_cut_v1.mp4',
                'original_name' => 'Cinematic Teaser 4K Draft v1.mp4',
                'disk' => 'media',
                'mime_type' => 'video/mp4',
                'size_bytes' => 145000000,
                'category' => MediaCategory::ClientPreview,
                'visibility' => FileVisibility::ClientPreview,
                'version_number' => 1,
                'processing_status' => ProcessingStatus::Ready,
                'metadata' => [
                    'duration_seconds' => 125,
                    'resolution' => '3840x2160',
                    'fps' => 24,
                    'codec' => 'h264',
                ],
                'released_by_user_id' => $admin->id,
                'released_at' => now(),
            ]
        );

        MediaAsset::firstOrCreate(
            ['storage_key' => "projects/{$project->id}/raw/ceremony_raw_cam_a.mov"],
            [
                'project_id' => $project->id,
                'uploaded_by_user_id' => $worker?->id ?? $admin->id,
                'filename' => 'ceremony_raw_cam_a.mov',
                'original_name' => 'Ceremony Main Angle FX6 ProRes.mov',
                'disk' => 'media',
                'mime_type' => 'video/quicktime',
                'size_bytes' => 1200000000,
                'category' => MediaCategory::RawFootage,
                'visibility' => FileVisibility::Internal,
                'version_number' => 1,
                'processing_status' => ProcessingStatus::Ready,
                'metadata' => [
                    'duration_seconds' => 900,
                    'resolution' => '3840x2160',
                    'fps' => 50,
                ],
            ]
        );
    }
}
