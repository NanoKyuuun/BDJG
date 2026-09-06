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
        // 1. Photography Service
        $photo = Service::firstOrCreate(
            ['slug' => 'photography'],
            [
                'name' => 'Photography',
                'description_internal' => 'Commercial, event, and portrait photography services',
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::firstOrCreate(
            ['service_id' => $photo->id, 'name' => 'Wedding Classic Photo'],
            [
                'description_internal' => 'Full day wedding photography with 2 photographers and edited album',
                'base_price' => 8500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::firstOrCreate(
            ['service_id' => $photo->id, 'name' => 'Graduation Exclusive Photo'],
            [
                'description_internal' => 'Studio and outdoor graduation portrait session with retouching',
                'base_price' => 1800000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        Package::firstOrCreate(
            ['service_id' => $photo->id, 'name' => 'Commercial Product Photo'],
            [
                'description_internal' => 'High-end product photography for catalog and marketing campaigns',
                'base_price' => 4500000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        // 2. Videography Service
        $video = Service::firstOrCreate(
            ['slug' => 'videography-film'],
            [
                'name' => 'Videography & Film',
                'description_internal' => 'Cinematic video production, commercials, and music videos',
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        Package::firstOrCreate(
            ['service_id' => $video->id, 'name' => 'Wedding Cinematic Film 4K'],
            [
                'description_internal' => 'Cinematic wedding highlight film + full ceremony documentary in 4K',
                'base_price' => 15000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        Package::firstOrCreate(
            ['service_id' => $video->id, 'name' => 'Company Profile Video'],
            [
                'description_internal' => 'Corporate storytelling, interview capture, and b-roll production',
                'base_price' => 22000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        // 3. Post Production Service
        $post = Service::firstOrCreate(
            ['slug' => 'post-production'],
            [
                'name' => 'Post Production',
                'description_internal' => 'Color grading, offline/online video editing, and sound design',
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );

        Package::firstOrCreate(
            ['service_id' => $post->id, 'name' => 'Color Grading Suite'],
            [
                'description_internal' => 'DaVinci Resolve professional color grade for short film or commercial',
                'base_price' => 5000000,
                'currency' => 'IDR',
                'default_dp_type' => DpType::Percentage,
                'default_dp_value' => 50.00,
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        // Add-Ons
        AddOn::firstOrCreate(
            ['name' => 'Drone 4K Aerial Coverage'],
            [
                'service_id' => $video->id,
                'description_internal' => 'Certified drone pilot with 4K ProRes capture',
                'price' => 2500000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 1,
            ]
        );

        AddOn::firstOrCreate(
            ['name' => 'Additional Photographer'],
            [
                'service_id' => $photo->id,
                'description_internal' => 'Extra dedicated second shooter on location',
                'price' => 1500000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 2,
            ]
        );

        AddOn::firstOrCreate(
            ['name' => 'Express 24-Hour Delivery'],
            [
                'service_id' => null,
                'description_internal' => 'Priority post-production rush turn-around within 24 hours',
                'price' => 2000000,
                'currency' => 'IDR',
                'status' => CatalogStatus::Active,
                'sort_order' => 3,
            ]
        );
    }
}
