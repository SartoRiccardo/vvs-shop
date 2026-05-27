<?php

namespace Webkul\PosteShipping\Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\PosteShipping\Models\PosteService;
use Webkul\PosteShipping\Models\PosteZone;
use Webkul\PosteShipping\Models\PosteCountryZone;
use Webkul\PosteShipping\Models\PosteRate;

class PosteMiniBoxExpresSeeder extends Seeder
{
    // Prices: raw * 1.22 + 1, rounded to 2 decimals.
    // Weight bands in kg: 0.250, 0.500, 0.750, 1.000
    private function price(float $raw): float
    {
        return round($raw * 1.22 + 1, 2);
    }

    public function run(): void
    {
        $service = PosteService::firstOrCreate(
            ['name' => 'PostMiniBox Expres'],
            ['description' => 'Poste Italiane PostMiniBox Expres', 'active' => true]
        );

        $this->seedZone($service, 'Europa', 'European countries', $this->europaCountries(), [
            '0.2500' => $this->price(6.85),
            '0.5000' => $this->price(7.60),
            '0.7500' => $this->price(9.80),
            '1.0000' => $this->price(10.90),
        ]);

        $this->seedZone($service, 'Resto del mondo', 'Rest of the world', $this->restoDelMondoCountries(), [
            '0.2500' => $this->price(9.25),
            '0.5000' => $this->price(10.90),
            '0.7500' => $this->price(16.80),
            '1.0000' => $this->price(18.70),
        ]);

        $this->seedZone($service, 'Oceania', 'Oceania countries', $this->oceaniaCountries(), [
            '0.2500' => $this->price(11.35),
            '0.5000' => $this->price(13.90),
            '0.7500' => $this->price(21.15),
            '1.0000' => $this->price(23.45),
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
            'AL', // Albania
            'AD', // Andorra
            'AT', // Austria
            'BE', // Belgio
            'BY', // Bielorussia
            'BA', // Bosnia-Erzegovina
            'BG', // Bulgaria
            'IC', // Canarie (Isole)
            'EA', // Ceuta & Melilla
            'CY', // Cipro
            'HR', // Croazia
            'DK', // Danimarca
            'EE', // Estonia
            'FO', // Faroe (Isole)
            'FI', // Finlandia
            'FR', // Francia
            'DE', // Germania
            'GI', // Gibilterra
            'GB', // Gran Bretagna e Irlanda del Nord
            'GR', // Grecia
            'GG', // Guernsey
            'IE', // Irlanda
            'IS', // Islanda
            'JE', // Jersey
            'LV', // Lettonia
            'LI', // Liechtenstein
            'LT', // Lituania
            'LU', // Lussemburgo
            'MK', // Macedonia del Nord
            'MT', // Malta
            'IM', // Man (Isole)
            'MD', // Moldavia
            'MC', // Monaco
            'ME', // Montenegro
            'NO', // Norvegia
            'NL', // Paesi Bassi
            'PL', // Polonia
            'PT', // Portogallo
            'CZ', // Repubblica Ceca
            'RO', // Romania
            'SM', // San Marino
            'RS', // Serbia
            'SK', // Slovacchia
            'SI', // Slovenia
            'ES', // Spagna
            'SE', // Svezia
            'CH', // Svizzera
            'UA', // Ucraina
            'HU', // Ungheria
            'VA', // Vaticano
        ];
    }

    private function restoDelMondoCountries(): array
    {
        return [
            'AF', // Afghanistan
            'DZ', // Algeria
            'AO', // Angola
            'AI', // Anguilla
            'AG', // Antigua & Barbuda
            'SA', // Arabia Saudita
            'AR', // Argentina
            'AM', // Armenia
            'AW', // Aruba
            'AC', // Ascension (Isole)
            'AZ', // Azerbaidjan
            'BS', // Bahamas
            'BH', // Bahrain
            'BD', // Bangladesh
            'BB', // Barbados
            'BZ', // Belize
            'BJ', // Benin
            'BM', // Bermuda
            'BT', // Bhutan
            'BO', // Bolivia
            'BQ', // Bonaire / Saba / Sint Eustatius (Antille Olandesi)
            'BW', // Botswana
            'BR', // Brasile
            'BN', // Brunei
            'BF', // Burkina Faso
            'BI', // Burundi
            'KH', // Cambogia
            'CM', // Camerun
            'CA', // Canada
            'CV', // Capo Verde
            'KY', // Cayman (Isole)
            'TD', // Ciad
            'CL', // Cile
            'CN', // Cina
            'CO', // Colombia
            'KM', // Comore
            'CD', // Congo (Rep. Democratica)
            'KR', // Corea del Sud
            'CI', // Costa d'Avorio
            'CR', // Costa Rica
            'CU', // Cuba
            'CW', // Curacao
            'DM', // Dominica
            'EC', // Ecuador
            'EG', // Egitto
            'SV', // El Salvador
            'AE', // Emirati Arabi Uniti
            'ER', // Eritrea
            'ET', // Etiopia
            'FK', // Falklands (Isole)
            'PH', // Filippine
            'GA', // Gabon
            'GM', // Gambia
            'GE', // Georgia
            'GS', // Georgia del Sud e Isole Sandwich Australi
            'GH', // Ghana
            'JM', // Giamaica
            'JP', // Giappone
            'DJ', // Gibuti
            'JO', // Giordania
            'GQ', // Guinea Equatoriale
            'GD', // Grenada
            'GL', // Groenlandia
            'GP', // Guadalupa
            'GT', // Guatemala
            'GN', // Guinea
            'GW', // Guinea Bissau
            'GY', // Guyana
            'GF', // Guyana Francese
            'HT', // Haiti
            'HN', // Honduras
            'HK', // Hong Kong
            'IN', // India
            'ID', // Indonesia
            'IR', // Iran
            'IQ', // Iraq
            'VI', // Isole Vergini Americane
            'VG', // Isole Vergini Britanniche
            'IL', // Israele
            'KZ', // Kazakistan
            'KE', // Kenya
            'KG', // Kirghizistan
            'KW', // Kuwait
            'LA', // Laos
            'LS', // Lesotho
            'LB', // Libano
            'LR', // Liberia
            'LY', // Libia
            'MO', // Macao
            'MG', // Madagascar
            'MW', // Malawi
            'MV', // Maldive
            'MY', // Malesia
            'ML', // Mali
            'MA', // Marocco
            'MQ', // Martinica
            'MR', // Mauritania
            'MU', // Mauritius
            'YT', // Mayotte
            'MX', // Messico
            'MN', // Mongolia
            'MS', // Montserrat
            'MZ', // Mozambico
            'MM', // Myanmar (Birmania)
            'NA', // Namibia
            'NP', // Nepal
            'NI', // Nicaragua
            'NE', // Niger
            'NG', // Nigeria
            'OM', // Oman
            'PK', // Pakistan
            'PA', // Panama
            'PY', // Paraguay
            'PE', // Peru
            'PR', // Porto Rico
            'QA', // Qatar
            'CF', // Repubblica Centrafricana
            'CG', // Repubblica del Congo
            'DO', // Repubblica Dominicana
            'RE', // Reunion (Isole)
            'RW', // Ruanda
            'RU', // Russia
            'KN', // Saint Cristoforo (Saint Kitts and Nevis)
            'ST', // Saint Tommaso e Principe
            'BL', // Saint Barthelemy
            'LC', // Saint Lucia
            'PM', // Saint Pierre & Miquelon
            'VC', // Saint Vincent e Grenadine
            'SH', // Sant'Elena (Isola di) / Tristan Da Cunha
            'SN', // Senegal
            'SC', // Seychelles
            'SL', // Sierra Leone
            'SG', // Singapore
            'SX', // Sint Maarten
            'LK', // Sri Lanka
            'US', // Stati Uniti d'America
            'ZA', // Sud Africa
            'SD', // Sudan
            'SR', // Suriname
            'SZ', // Swaziland (Eswatini)
            'TJ', // Tagikistan
            'TH', // Tailandia
            'TW', // Taiwan
            'TZ', // Tanzania
            'IO', // Territorio Britannico dell'Oceano Indiano
            'TL', // Timor Orientale
            'TG', // Togo
            'TT', // Trinidad & Tobago
            'TN', // Tunisia
            'TR', // Turchia
            'TM', // Turkmenistan
            'UG', // Uganda
            'UY', // Uruguay
            'UZ', // Uzbekistan
            'VE', // Venezuela
            'VN', // Vietnam
            'ZM', // Zambia
            'ZW', // Zimbabwe
        ];
    }

    private function oceaniaCountries(): array
    {
        return [
            'AU', // Australia
            'CK', // Cook (Isole)
            'FJ', // Fiji (Isole)
            'GU', // Guam
            'KI', // Kiribati
            'MH', // Marshall (Isole)
            'FM', // Micronesia (Isole Caroline)
            'NR', // Nauru
            'NU', // Niue
            'NF', // Norfolk Island
            'NC', // Nuova Caledonia
            'NZ', // Nuova Zelanda
            'PW', // Palau
            'PG', // Papua Nuova Guinea
            'PN', // Pitcairn
            'PF', // Polinesia Francese
            'MP', // Saipan (Isole Marianne Settentrionali)
            'SB', // Salomone (Isole)
            'AS', // Samoa Americane
            'WS', // Samoa
            'TF', // Terre Australi e Antartiche Francesi / Scattered Islands
            'TK', // Tokelau
            'TO', // Tonga (Isole)
            'TC', // Turks and Caicos
            'TV', // Tuvalu
            'VU', // Vanuatu
            'UM', // Wake (Isole Minori Esterne degli USA)
            'WF', // Wallis & Futuna (Isole)
        ];
    }
}
