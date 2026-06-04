<?php

namespace Webkul\PosteShipping\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\PosteShipping\Models\PosteCountryZone;
use Webkul\PosteShipping\Models\PosteRate;
use Webkul\PosteShipping\Models\PosteService;
use Webkul\PosteShipping\Models\PosteZone;

class PosteMiniBoxEconomySeeder extends Seeder
{
    // Prices: raw * 1.22 + 1, rounded to 2 decimals.
    // Weight bands in kg: 0.500, 1.000, 2.000
    private function price(float $raw): float
    {
        return round($raw * 1.22 + 1, 2);
    }

    public function run(): void
    {
        $service = PosteService::firstOrCreate(
            ['name' => 'PostMiniBox Economy'],
            ['description' => 'Poste Italiane PostMiniBox Economy ⚠️ This shipping method is untracked', 'active' => true]
        );

        $this->seedZone($service, 'Europa', 'European countries', $this->europaCountries(), [
            '0.5000' => $this->price(4.50),
            '1.0000' => $this->price(7.30),
            '2.0000' => $this->price(12.05),
        ]);

        $this->seedZone($service, 'Resto del mondo', 'Rest of the world', $this->restoDelMondoCountries(), [
            '0.5000' => $this->price(7.85),
            '1.0000' => $this->price(12.15),
            '2.0000' => $this->price(20.70),
        ]);

        $this->seedZone($service, 'Oceania', 'Oceania countries', $this->oceaniaCountries(), [
            '0.5000' => $this->price(9.05),
            '1.0000' => $this->price(18.30),
            '2.0000' => $this->price(24.75),
        ]);
    }

    private function seedZone(PosteService $service, string $name, string $description, array $countryCodes, array $rates): void
    {
        $zone = PosteZone::firstOrCreate(
            ['service_id' => $service->id, 'name' => $name],
            ['description' => $description]
        );

        $existingCodes = $zone->countryZones()->pluck('country_code')->toArray();
        $validCodes = $this->filterExistingCountries($countryCodes);

        foreach (array_diff($validCodes, $existingCodes) as $code) {
            PosteCountryZone::create(['zone_id' => $zone->id, 'country_code' => $code]);
        }

        foreach ($rates as $maxWeightKg => $costEur) {
            PosteRate::firstOrCreate(
                ['zone_id' => $zone->id, 'max_weight_kg' => $maxWeightKg],
                ['cost_eur' => $costEur]
            );
        }
    }

    private function filterExistingCountries(array $codes): array
    {
        $existing = \DB::table('countries')->whereIn('code', $codes)->pluck('code')->toArray();
        $missing = array_diff($codes, $existing);

        if (! empty($missing)) {
            $this->command->warn('Skipped (not in DB): '.implode(', ', $missing));
        }

        return $existing;
    }

    private function europaCountries(): array
    {
        return [
            'AT', // Austria
            'BE', // Belgio
            'BG', // Bulgaria
            'CY', // Cipro
            'HR', // Croazia
            'DK', // Danimarca
            'EE', // Estonia
            'FI', // Finlandia
            'FR', // Francia
            'DE', // Germania
            'GB', // Gran Bretagna e Irlanda del Nord
            'GR', // Grecia
            'IE', // Irlanda
            'IS', // Islanda
            'LV', // Lettonia
            'LT', // Lituania
            'LU', // Lussemburgo
            'MT', // Malta
            'NL', // Paesi Bassi
            'PL', // Polonia
            'PT', // Portogallo
            'CZ', // Repubblica Ceca
            'RO', // Romania
            'SM', // San Marino
            'SK', // Slovacchia
            'SI', // Slovenia
            'ES', // Spagna
            'VA', // Stato del Vaticano
            'SE', // Svezia
            'CH', // Svizzera
            'HU', // Ungheria
        ];
    }

    private function restoDelMondoCountries(): array
    {
        return [
            'CA', // Canada
            'CN', // Cina
            'KR', // Corea del Sud
            'AE', // Emirati Arabi Uniti
            'JP', // Giappone
            'HK', // Hong Kong
            'IL', // Israele
            'US', // Stati Uniti d'America
            'TR', // Turchia
        ];
    }

    private function oceaniaCountries(): array
    {
        return [
            'AU', // Australia
            'NZ', // Nuova Zelanda
        ];
    }
}
