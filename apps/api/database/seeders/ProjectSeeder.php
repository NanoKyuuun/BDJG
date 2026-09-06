<?php

namespace Database\Seeders;

use App\Domains\Billing\Models\Invoice;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Schedules\Enums\ScheduleType;
use App\Domains\Schedules\Models\Schedule;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Enums\TaskStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\ProjectAssignment;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@bdjg.studio')->first();

        $workerUser = User::firstOrCreate(
            ['email' => 'worker@bdjg.studio'],
            [
                'name' => 'BDJG Worker',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
        $workerUser->syncRoles('WORKER');

        // 1. Seed Worker Profile
        $workerProfile = WorkerProfile::firstOrCreate(
            ['user_id' => $workerUser->id],
            [
                'profession' => WorkerProfession::Videographer,
                'skills' => ['Sony FX3', 'Ronin RS3', 'Color Grading', 'Drone Piloting'],
                'phone' => '+6281234567890',
                'status' => WorkerStatus::Active,
                'notes_internal' => 'Senior Lead Cinematographer & Drone Operator.',
            ]
        );

        // 2. Seed Project from Accepted & Paid Quotation QT-2026-002
        $q2 = Quotation::where('quotation_number', 'QT-2026-002')->first();
        $inv2 = Invoice::where('invoice_number', 'INV-2026-002')->first();

        if ($q2) {
            $q2->loadMissing(['acceptedVersion', 'currentVersion']);
            $version = $q2->acceptedVersion ?? $q2->currentVersion;

            $project = Project::firstOrCreate(
                ['project_number' => 'PRJ-2026-001'],
                [
                    'client_id' => $q2->client_id,
                    'quotation_id' => $q2->id,
                    'accepted_quotation_version_id' => $version?->id,
                    'name' => $version?->project_name ?? 'Dhea & Arya Cinematic Wedding Film',
                    'service_name_snapshot' => $version?->service_name_snapshot ?? 'Wedding Video',
                    'package_name_snapshot' => $version?->package_name_snapshot ?? 'Cinematic 4K Wedding Film',
                    'contract_value' => $version?->grand_total ?? 18000000,
                    'status' => ProjectStatus::Production,
                    'shoot_date' => now()->addDays(5)->toDateString(),
                    'deadline' => now()->addDays(20)->toDateString(),
                    'location' => 'Plataran Dharmawangsa, Jakarta Selatan',
                    'brief' => 'Capture cinematic highlights, holy matrimony ceremony, and evening reception in warm aesthetic tones.',
                    'assigned_admin_id' => $admin?->id,
                    'activated_at' => now()->subDays(2),
                    'created_by' => $admin?->id,
                    'notes_internal' => 'DP paid via Duitku. Full crew gear allocated.',
                ]
            );

            // Link Invoice to Project
            if ($inv2) {
                $inv2->update(['project_id' => $project->id]);
            }

            // 3. Assign Worker to Project
            ProjectAssignment::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'worker_id' => $workerProfile->id,
                    'assignment_role' => 'Lead Cinematographer',
                ],
                [
                    'fee_amount' => 3500000,
                    'is_active' => true,
                    'assigned_at' => now()->subDays(2),
                    'assigned_by' => $admin?->id,
                ]
            );

            // 4. Seed Tasks
            Task::firstOrCreate(
                ['project_id' => $project->id, 'title' => 'Moodboard & Visual Shotlist Creation'],
                [
                    'description' => 'Draft camera movements, lens choices, and couple portrait angles.',
                    'status' => TaskStatus::Done,
                    'priority' => TaskPriority::High,
                    'assigned_worker_id' => $workerProfile->id,
                    'due_at' => now()->subDay(),
                    'completed_at' => now()->subDay(),
                    'created_by' => $admin?->id,
                ]
            );

            Task::firstOrCreate(
                ['project_id' => $project->id, 'title' => 'Wedding Day Production Shooting'],
                [
                    'description' => 'Execute multi-cam 4K 10-bit recording on location.',
                    'status' => TaskStatus::InProgress,
                    'priority' => TaskPriority::Urgent,
                    'assigned_worker_id' => $workerProfile->id,
                    'due_at' => now()->addDays(5),
                    'created_by' => $admin?->id,
                ]
            );

            Task::firstOrCreate(
                ['project_id' => $project->id, 'title' => 'Teaser & Highlights Editing'],
                [
                    'description' => 'Color grading in DaVinci Resolve and sound design mixing.',
                    'status' => TaskStatus::Todo,
                    'priority' => TaskPriority::Medium,
                    'assigned_worker_id' => $workerProfile->id,
                    'due_at' => now()->addDays(15),
                    'created_by' => $admin?->id,
                ]
            );

            // 5. Seed Schedules
            Schedule::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'title' => 'Wedding Day Call Sheet & Shooting',
                ],
                [
                    'schedule_type' => ScheduleType::Shooting,
                    'start_time' => now()->addDays(5)->setHour(6)->setMinute(0),
                    'end_time' => now()->addDays(5)->setHour(22)->setMinute(0),
                    'location' => 'Plataran Dharmawangsa, Jl. Dharmawangsa Raya No.6, Jakarta',
                    'description' => 'Multi-camera team call time 06:00 AM. Drone flight permitted 08:00–10:00 AM.',
                    'created_by' => $admin?->id,
                ]
            );
        }
    }
}
