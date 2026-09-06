<?php

namespace Database\Seeders;

use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\CRM\Enums\InquirySource;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@bdjg.studio')->first();
        $photoService = Service::where('slug', 'photography')->first();
        $videoService = Service::where('slug', 'videography-film')->first();
        $weddingPackage = Package::where('name', 'Wedding Cinematic Film 4K')->first();

        // 1. NEW Inquiry
        Inquiry::firstOrCreate(
            ['email' => 'marketing@telkomdigital.id'],
            [
                'client_name' => 'Budi Santoso',
                'company_or_institution' => 'PT Telkom Digital',
                'phone' => '+6281200001111',
                'service_id' => $videoService?->id,
                'project_brief' => 'Membutuhkan dokumentasi video dan live streaming untuk event product launching 3 hari.',
                'preferred_date' => now()->addWeeks(3)->toDateString(),
                'estimated_budget' => 35000000,
                'source' => InquirySource::Website,
                'status' => InquiryStatus::New,
                'notes_internal' => 'Inquiry masuk via website form.',
            ]
        );

        // 2. CONTACTED Inquiry
        Inquiry::firstOrCreate(
            ['email' => 'hendra.kusuma@gmail.com'],
            [
                'client_name' => 'Hendra Kusuma',
                'company_or_institution' => null,
                'phone' => '+6281200002222',
                'service_id' => $photoService?->id,
                'project_brief' => 'Foto wisuda keluarga besar 15 orang di studio dan outdoor.',
                'preferred_date' => now()->addMonth()->toDateString(),
                'estimated_budget' => 3000000,
                'source' => InquirySource::WhatsApp,
                'status' => InquiryStatus::Contacted,
                'assigned_admin_id' => $admin?->id,
                'notes_internal' => 'Sudah dikontak via WA, sedang menunggu konfirmasi tanggal pasti.',
            ]
        );

        // 3. QUALIFIED Inquiry
        Inquiry::firstOrCreate(
            ['email' => 'creative@fashionbrand.com'],
            [
                'client_name' => 'Clarissa Putri',
                'company_or_institution' => 'Brand Clarissa Apparel',
                'phone' => '+6281200003333',
                'service_id' => $photoService?->id,
                'project_brief' => 'Photoshoot katalog Summer Collection 2026, 40 looks dengan 2 model.',
                'preferred_date' => now()->addWeeks(2)->toDateString(),
                'estimated_budget' => 12000000,
                'source' => InquirySource::Instagram,
                'status' => InquiryStatus::Qualified,
                'assigned_admin_id' => $admin?->id,
                'notes_internal' => 'Brief jelas, budget sesuai, siap diterbitkan Quotation.',
            ]
        );

        // 4. QUOTATION Inquiry
        Inquiry::firstOrCreate(
            ['email' => 'dhea.arya@example.com'],
            [
                'client_name' => 'Dhea & Arya',
                'company_or_institution' => null,
                'phone' => '+6281987654321',
                'service_id' => $videoService?->id,
                'package_id' => $weddingPackage?->id,
                'project_brief' => 'Wedding cinematic 4K highlight & documentary di Bandung.',
                'preferred_date' => now()->addMonths(2)->toDateString(),
                'estimated_budget' => 18000000,
                'source' => InquirySource::Website,
                'status' => InquiryStatus::Quotation,
                'assigned_admin_id' => $admin?->id,
                'notes_internal' => 'Quotation QT-2026-001 sedang dalam review klien.',
            ]
        );

        // 5. LOST Inquiry
        Inquiry::firstOrCreate(
            ['email' => 'eo.malang@example.com'],
            [
                'client_name' => 'Rian EO Malang',
                'company_or_institution' => 'CV Kreatif Mandiri',
                'phone' => '+6281200005555',
                'service_id' => $videoService?->id,
                'project_brief' => 'Dokumentasi festival musik 1 hari.',
                'preferred_date' => now()->addWeeks(4)->toDateString(),
                'estimated_budget' => 4000000,
                'source' => InquirySource::Referral,
                'status' => InquiryStatus::Lost,
                'lost_reason' => 'Budget mismatch (budget klien 4jt, estimasi paket 15jt).',
                'assigned_admin_id' => $admin?->id,
            ]
        );
    }
}
