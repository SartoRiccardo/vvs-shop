<?php

namespace Webkul\PosteShipping\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\PosteShipping\Models\PosteCountryZone;
use Webkul\PosteShipping\Models\PosteRate;
use Webkul\PosteShipping\Models\PosteService;
use Webkul\PosteShipping\Models\PosteZone;

class PosteItalianeSeeder extends Seeder
{
    public function run(): void
    {
        $service = PosteService::firstOrCreate(
            ['name' => 'PosteItaliane'],
            ['description' => 'Servizio nazionale delle poste 🔎 This shipping method has full tracking', 'active' => true]
        );

        $zone = PosteZone::firstOrCreate(
            ['service_id' => $service->id, 'name' => 'Italia'],
            ['description' => '']
        );

        if (! PosteCountryZone::where('zone_id', $zone->id)->where('country_code', 'IT')->exists()) {
            PosteCountryZone::create(['zone_id' => $zone->id, 'country_code' => 'IT']);
        }

        $rates = [
            '0.5000' => 6.00,
            '1.0000' => 8.00,
            '2.0000' => 12.00,
        ];

        foreach ($rates as $maxWeightKg => $costEur) {
            PosteRate::firstOrCreate(
                ['zone_id' => $zone->id, 'max_weight_kg' => $maxWeightKg],
                ['cost_eur' => $costEur]
            );
        }
    }
}
