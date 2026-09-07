<?php

namespace Database\Seeders;

use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Enums\DpType;
use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. Happiness Package (Foto + Video)
        // ==========================================
        $happiness = Service::updateOrCreate(
            ['slug' => 'happiness-package'],
            [
                'name' => 'Happiness Package',
                'description_internal' => 'Paket gabungan dokumentasi fotografi dan videografi wedding & couple session.',
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $happiness->id, 'name' => 'Couple Session'],
            [
                'description_internal' => 'Coverage 3 jam, 1 Photographer, 1 Videographer, 1 Assistant, 2 konsep, 1 cetak 50x60 cm + frame, 3 cetak 20x25 cm, Cinema Teaser ~1.30 menit, 40 foto edited + file original, free flashdisk.',
                'base_price' => 3500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $happiness->id, 'name' => 'Half Day Wedding'],
            [
                'description_internal' => 'Coverage 3–5 jam, 1 Photographer, 1 Videographer, 1 cetak 50x60 cm + frame, 3 cetak 20x25 cm, Cinema Teaser ~1.30 menit, seluruh file edited + original, free flashdisk.',
                'base_price' => 4000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $happiness->id, 'name' => 'Full Day Wedding'],
            [
                'description_internal' => 'Coverage 8–10 jam, 1 Photographer, 1 Videographer, 1 Assistant, 1 cetak 50x60 cm + frame, 1 Exclusive Album, 120 foto cetak 10.2x15.2 cm, Cinema Full ~4 menit, Cinema Highlight ~45 detik, seluruh file edited + original, free flashdisk.',
                'base_price' => 5500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        // ==========================================
        // 2. Photography Package (Foto Saja)
        // ==========================================
        $photo = Service::updateOrCreate(
            ['slug' => 'photography-package'],
            [
                'name' => 'Photography Package',
                'description_internal' => 'Paket khusus layanan fotografi wedding, couple session, dan dokumentasi momen spesial.',
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $photo->id, 'name' => 'Couple Session Photography'],
            [
                'description_internal' => 'Coverage 3 jam, 1 Photographer, 1 Assistant, 2 konsep, 2 cetak 50x60 cm + frame, 3 cetak 20x25 cm, 20 foto edited + original, free flashdisk.',
                'base_price' => 2000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $photo->id, 'name' => 'Romance'],
            [
                'description_internal' => 'Coverage 8–10 jam, 1 Photographer, 1 Assistant, 1 cetak 50x60 cm + frame, 1 Exclusive Album, 100 foto cetak 10.2x15.2 cm, seluruh file edited + original, free flashdisk.',
                'base_price' => 3000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $photo->id, 'name' => 'Soul'],
            [
                'description_internal' => 'Coverage 8–10 jam, 2 Photographer, 2 cetak 50x60 cm + frame, 3 cetak 20x25 cm, 1 Exclusive Album, 120 foto cetak 10.2x15.2 cm, seluruh file edited + original, free flashdisk.',
                'base_price' => 5500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $photo->id, 'name' => 'Loyalty'],
            [
                'description_internal' => 'Coverage 8–10 jam, 2 Photographer, 1 Candid Photographer, 2 cetak 50x60 cm + frame, 3 cetak 20x25 cm + frame, 1 Exclusive Album, 120 foto cetak 10.2x15.2 cm, 1 Album Magazine / Collage, seluruh file edited + original, free flashdisk.',
                'base_price' => 5000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 4,
            ]
        );

        // ==========================================
        // 3. Videography Package (Video Saja)
        // ==========================================
        $video = Service::updateOrCreate(
            ['slug' => 'videography-package'],
            [
                'name' => 'Videography Package',
                'description_internal' => 'Paket khusus layanan videografi cinematic dan dokumentasi wedding.',
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $video->id, 'name' => 'Couple Session Videography'],
            [
                'description_internal' => 'Coverage 3 jam, 1 Videographer, 1 Assistant, 2 konsep, Cinema Full ~4 menit, Cinema Highlight ~45 detik, free flashdisk.',
                'base_price' => 2000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $video->id, 'name' => 'Perfect'],
            [
                'description_internal' => 'Coverage 8–10 jam, 2 Videographer, 1 Assistant, Cinema Full ~4 menit, Cinema Teaser ~1.30 menit, Cinema Highlight ~45 detik, free flashdisk.',
                'base_price' => 4000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $video->id, 'name' => 'Lover'],
            [
                'description_internal' => 'Coverage 8–10 jam, 2 Videographer, Cinema Full ~4 menit, free flashdisk.',
                'base_price' => 2500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $video->id, 'name' => 'Cute'],
            [
                'description_internal' => 'Coverage 8–10 jam, 1 Videographer, Cinema Teaser ~1.30 menit, free flashdisk.',
                'base_price' => 1500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 4,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $video->id, 'name' => 'Sweet'],
            [
                'description_internal' => 'Coverage 8–10 jam, 1 Videographer, Cinema Highlight ~45 detik.',
                'base_price' => 1000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 5,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $video->id, 'name' => 'Mamoar'],
            [
                'description_internal' => 'Coverage 8–10 jam, 1 Videographer, Video Dokumentasi ~15 menit, free flashdisk.',
                'base_price' => 2000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 6,
            ]
        );

        // ==========================================
        // 4. Custom Your Story (Custom Package)
        // ==========================================
        $custom = Service::updateOrCreate(
            ['slug' => 'custom-your-story'],
            [
                'name' => 'Custom Your Story',
                'description_internal' => 'Paket fleksibel yang disesuaikan dengan kebutuhan, durasi, kru, dan anggaran khusus customer.',
                'status' => CatalogStatus::Active,
                'sort_order' => 4,
            ]
        );

        Package::updateOrCreate(
            ['service_id' => $custom->id, 'name' => 'Custom Wedding Story'],
            [
                'description_internal' => 'Paket custom dengan durasi, jumlah fotografer/videografer, album, dan output yang disesuaikan kebutuhan spesifik customer.',
                'base_price' => 3000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        // ==========================================
        // Add-Ons
        // ==========================================
        AddOn::updateOrCreate(
            ['name' => 'Extra Coverage (per Jam)'],
            [
                'service_id' => null,
                'description_internal' => 'Tambahan durasi liputan sebesar Rp500.000 per 1 jam kerja.',
                'price' => 500000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Additional Photographer'],
            [
                'service_id' => $photo->id,
                'description_internal' => 'Tambahan 1 orang fotografer profesional untuk liputan acara.',
                'price' => 1000000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Additional Videographer'],
            [
                'service_id' => $video->id,
                'description_internal' => 'Tambahan 1 orang videografer profesional untuk liputan acara.',
                'price' => 1000000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Drone 4K Aerial Coverage'],
            [
                'service_id' => $video->id,
                'description_internal' => 'Dokumentasi udara/aerial drone 4K dengan pilot bersertifikat.',
                'price' => 1200000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 4,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Same Day Edit (SDE) Video'],
            [
                'service_id' => $video->id,
                'description_internal' => 'Video highlight kilat yang diedit pada hari yang sama untuk ditayangkan saat resepsi.',
                'price' => 1500000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 5,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Additional Exclusive Album'],
            [
                'service_id' => $photo->id,
                'description_internal' => 'Tambahan 1 buah album eksklusif premium hardcover.',
                'price' => 850000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 6,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Cetak Foto 50 × 60 cm + Frame'],
            [
                'service_id' => $photo->id,
                'description_internal' => 'Cetak foto pembesaran ukuran 50x60 cm lengkap beserta frame minimalis.',
                'price' => 450000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 7,
            ]
        );

        AddOn::updateOrCreate(
            ['name' => 'Express 24-Hour Delivery'],
            [
                'service_id' => null,
                'description_internal' => 'Prioritas pengerjaan kilat dan penyerahan seluruh file hasil editing dalam waktu 24 jam.',
                'price' => 1500000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 8,
            ]
        );
    }
}
