<?php

namespace Webkul\FedExShipping\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FedExFICPSeeder extends Seeder
{
    // Country name → ISO2. Covers all countries in prices.json and fiel_surcharge.json.
    private array $iso2 = [
        'Algeria' => 'DZ',
        'American Samoa' => 'AS',
        'Andorra' => 'AD',
        'Angola' => 'AO',
        'Anguilla' => 'AI',
        'Antigua & Barbuda' => 'AG',
        'Argentina' => 'AR',
        'Armenia' => 'AM',
        'Aruba' => 'AW',
        'Australia' => 'AU',
        'Austria' => 'AT',
        'Azerbaijan' => 'AZ',
        'Bahama' => 'BS',
        'Bahrain' => 'BH',
        'Bangladesh' => 'BD',
        'Barbados' => 'BB',
        'Belarus' => 'BY',
        'Belgium' => 'BE',
        'Belize' => 'BZ',
        'Benin' => 'BJ',
        'Bermuda' => 'BM',
        'Bhutan' => 'BT',
        'Bolivia' => 'BO',
        'Bosnia and Herzegovina' => 'BA',
        'Bosnia-Herzegovina' => 'BA',
        'Botswana' => 'BW',
        'Brazil' => 'BR',
        'British Virgin Islands' => 'VG',
        'Brunei' => 'BN',
        'Bulgaria' => 'BG',
        'Burkina Faso' => 'BF',
        'Burundi' => 'BI',
        'Cambodia' => 'KH',
        'Cameroon' => 'CM',
        'Canada' => 'CA',
        'Cape Verde' => 'CV',
        'Cayman Islands' => 'KY',
        'Chad' => 'TD',
        'Chile' => 'CL',
        'China' => 'CN',
        'Colombia' => 'CO',
        'Congo' => 'CG',
        'Cook Islands' => 'CK',
        'Costa Rica' => 'CR',
        'Croatia' => 'HR',
        'Curacao' => 'CW',
        'Cyprus' => 'CY',
        'Czech Republic' => 'CZ',
        "Côte D'ivoire (Ivory Coast)" => 'CI',
        'Democratic Republic of the Congo' => 'CD',
        'Denmark' => 'DK',
        'Djibouti' => 'DJ',
        'Dominica' => 'DM',
        'Dominican Republic' => 'DO',
        'East Timor' => 'TL',
        'Ecuador' => 'EC',
        'Egypt' => 'EG',
        'El Salvador' => 'SV',
        'Eritrea' => 'ER',
        'Estonia' => 'EE',
        'Ethiopia' => 'ET',
        'Faroe Islands' => 'FO',
        'Finland' => 'FI',
        'France' => 'FR',
        'French Guiana' => 'GF',
        'French Polynesia' => 'PF',
        'Gabon' => 'GA',
        'Gambia' => 'GM',
        'Georgia' => 'GE',
        'Germany' => 'DE',
        'Ghana' => 'GH',
        'Gibraltar' => 'GI',
        'Greece' => 'GR',
        'Greenland' => 'GL',
        'Grenada' => 'GD',
        'Guadeloupe' => 'GP',
        'Guam' => 'GU',
        'Guatemala' => 'GT',
        'Guinea' => 'GN',
        'Guinea-Bissau' => 'GW',
        'Guyana' => 'GY',
        'Haiti' => 'HT',
        'Honduras' => 'HN',
        'Hong Kong SAR, China' => 'HK',
        'Hong Kong SAR China' => 'HK',
        'Hungary' => 'HU',
        'Iceland' => 'IS',
        'India' => 'IN',
        'Indonesia' => 'ID',
        'Iraq' => 'IQ',
        'Ireland' => 'IE',
        'Israel' => 'IL',
        'Jamaica' => 'JM',
        'Japan' => 'JP',
        'Jordan' => 'JO',
        'Kazakhstan' => 'KZ',
        'Kenya' => 'KE',
        'Kuwait' => 'KW',
        'Kyrgyzstan' => 'KG',
        'Laos' => 'LA',
        'Latvia' => 'LV',
        'Lebanon' => 'LB',
        'Lesotho' => 'LS',
        'Liberia' => 'LR',
        'Libya' => 'LY',
        'Liechtenstein' => 'LI',
        'Lithuania' => 'LT',
        'Luxembourg' => 'LU',
        'Macau SAR, China' => 'MO',
        'Macau SAR China' => 'MO',
        'Macedonia' => 'MK',
        'Madagascar' => 'MG',
        'Malawi' => 'MW',
        'Malaysia' => 'MY',
        'Maldives' => 'MV',
        'Mali' => 'ML',
        'Malta' => 'MT',
        'Marshall Islands' => 'MH',
        'Martinique' => 'MQ',
        'Mauritania' => 'MR',
        'Mauritius' => 'MU',
        'Mexico' => 'MX',
        'Micronesia' => 'FM',
        'Moldova' => 'MD',
        'Republic of Moldova' => 'MD',
        'Monaco' => 'MC',
        'Mongolia' => 'MN',
        'Montenegro' => 'ME',
        'Montserrat' => 'MS',
        'Morocco' => 'MA',
        'Mozambique' => 'MZ',
        'Myanmar' => 'MM',
        'Namibia' => 'NA',
        'Nauru' => 'NR',
        'Nepal' => 'NP',
        'Netherlands' => 'NL',
        'New Caledonia' => 'NC',
        'New Zealand' => 'NZ',
        'Nicaragua' => 'NI',
        'Niger' => 'NE',
        'Nigeria' => 'NG',
        'North Macedonia' => 'MK',
        'Northern Mariana Islands' => 'MP',
        'Norway' => 'NO',
        'Oman' => 'OM',
        'Pakistan' => 'PK',
        'Palau' => 'PW',
        'Palestinian Territory' => 'PS',
        'Panama' => 'PA',
        'Papua New Guinea' => 'PG',
        'Paraguay' => 'PY',
        'Peru' => 'PE',
        'Philippines' => 'PH',
        'Poland' => 'PL',
        'Portugal' => 'PT',
        'Puerto Rico' => 'PR',
        'Qatar' => 'QA',
        'Réunion' => 'RE',
        'Romania' => 'RO',
        'Rwanda' => 'RW',
        'Saint Lucia' => 'LC',
        'Samoa' => 'WS',
        'San Marino' => 'SM',
        'Saudi Arabia' => 'SA',
        'Serbia' => 'RS',
        'Seychelles' => 'SC',
        'Singapore' => 'SG',
        'Slovakia' => 'SK',
        'Slovenia' => 'SI',
        'Solomon Islands' => 'SB',
        'South Africa' => 'ZA',
        'South Korea' => 'KR',
        'Spain' => 'ES',
        'Sri Lanka' => 'LK',
        'St. Kitts and Nevis' => 'KN',
        'St. Maarten' => 'SX',
        'St. Vincent & the Grenadines' => 'VC',
        'Suriname' => 'SR',
        'Swaziland' => 'SZ',
        'Sweden' => 'SE',
        'Switzerland' => 'CH',
        'Syrian Arab Republic' => 'SY',
        'Taiwan' => 'TW',
        'Tanzania, United Republic of' => 'TZ',
        'Thailand' => 'TH',
        'Togo' => 'TG',
        'Tonga' => 'TO',
        'Trinidad & Tobago' => 'TT',
        'Tunisia' => 'TN',
        'Turkey' => 'TR',
        'Tuvalu' => 'TV',
        'Uganda' => 'UG',
        'Ukraine' => 'UA',
        'United Arab Emirates' => 'AE',
        'United Kingdom' => 'GB',
        'United States Virgin Islands' => 'VI',
        'Uruguay' => 'UY',
        'USA' => 'US',
        'Uzbekistan' => 'UZ',
        'Vanuatu' => 'VU',
        'Venezuela' => 'VE',
        'Vietnam' => 'VN',
        'Wallis & Futuna' => 'WF',
        'Yemen' => 'YE',
        'Zambia' => 'ZM',
        'Zimbabwe' => 'ZW',
    ];

    private array $euCodes = [
        'AT',
        'BE',
        'BG',
        'HR',
        'CY',
        'CZ',
        'DK',
        'EE',
        'FI',
        'FR',
        'DE',
        'GR',
        'HU',
        'IE',
        'IT',
        'LV',
        'LT',
        'LU',
        'MT',
        'MC',
        'NL',
        'PL',
        'PT',
        'RO',
        'SK',
        'SI',
        'ES',
        'SE',
    ];

    private array $europeanZones = ['R', 'S', 'T', 'U', 'V', 'W', 'X'];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('fedex_ficp_zones')->truncate();
        DB::table('fedex_ficp_rates')->truncate();
        DB::table('fedex_ficp_demand_groups')->truncate();
        DB::table('fedex_ficp_demand_countries')->truncate();
        DB::table('fedex_ficp_settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->seedZones();
        $this->seedRates();
        $this->seedDemandGroups();
        $this->seedSettings();
    }

    private function seedZones(): void
    {
        // Zones from prices.json → international_zones.
        // Skip non-country entries (Memphis, New York, Main cities, Other cities).
        $zones = [
            'A' => ['Canada'],
            'B' => ['Cambodia', 'East Timor', 'Indonesia', 'Laos', 'Macau SAR, China', 'Malaysia', 'South Korea', 'Taiwan', 'Thailand', 'Vietnam', 'Philippines'],
            'C' => ['Algeria', 'Armenia', 'Bahrain', 'Bangladesh', 'Bhutan', 'Egypt', 'India', 'Israel', 'Jordan', 'Kuwait', 'Lebanon', 'Libya', 'Nepal', 'Oman', 'Pakistan', 'Palestinian Territory', 'Qatar', 'Saudi Arabia', 'Syrian Arab Republic', 'Tunisia', 'United Arab Emirates'],
            'D' => ['Anguilla', 'Antigua & Barbuda', 'Argentina', 'Aruba', 'Bahama', 'Barbados', 'Belize', 'Bermuda', 'Bolivia', 'Brazil', 'British Virgin Islands', 'Cayman Islands', 'Chile', 'Colombia', 'Costa Rica', 'Curacao', 'Dominica', 'Dominican Republic', 'Ecuador', 'El Salvador', 'French Guiana', 'Grenada', 'Guadeloupe', 'Guatemala', 'Guyana', 'Haiti', 'Honduras', 'Jamaica', 'Martinique', 'Mexico', 'Micronesia', 'Montserrat', 'Nicaragua', 'Panama', 'Paraguay', 'Peru', 'Puerto Rico', 'Saint Lucia', 'South Africa', 'St. Kitts and Nevis', 'St. Maarten', 'St. Vincent & the Grenadines', 'Suriname', 'Trinidad & Tobago', 'United States Virgin Islands', 'Uruguay', 'Venezuela'],
            'E' => ['American Samoa', 'Angola', 'Azerbaijan', 'Benin', 'Botswana', 'Burkina Faso', 'Burundi', 'Cameroon', 'Cape Verde', 'Chad', 'Congo', 'Cook Islands', "Côte D'ivoire (Ivory Coast)", 'Democratic Republic of the Congo', 'Djibouti', 'Eritrea', 'Ethiopia', 'Faroe Islands', 'Finland', 'French Polynesia', 'Gabon', 'Gambia', 'Georgia', 'Ghana', 'Greenland', 'Guam', 'Guinea', 'Iraq', 'Kazakhstan', 'Kenya', 'Kyrgyzstan', 'Lesotho', 'Liberia', 'Madagascar', 'Malawi', 'Maldives', 'Mali', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mongolia', 'Mozambique', 'Namibia', 'Nauru', 'New Caledonia', 'Niger', 'Nigeria', 'Northern Mariana Islands', 'Palau', 'Papua New Guinea', 'Réunion', 'Rwanda', 'Samoa', 'Seychelles', 'Solomon Islands', 'Swaziland', 'Tanzania, United Republic of', 'Togo', 'Tonga', 'Tuvalu', 'Uganda', 'Uzbekistan', 'Vanuatu', 'Wallis & Futuna', 'Zambia', 'Zimbabwe'],
            'F' => ['China'],
            'G' => ['Australia', 'New Zealand'],
            'H' => ['USA'],
            'P' => ['Hong Kong SAR, China'],
            'R' => ['Austria', 'France', 'Germany', 'Monaco', 'Slovenia'],
            'S' => ['Belgium', 'Luxembourg', 'Netherlands', 'Portugal', 'Spain'],
            'T' => ['Bulgaria', 'Czech Republic', 'Hungary', 'Poland', 'Slovakia'],
            'U' => ['Croatia', 'Estonia', 'Greece', 'Ireland', 'Latvia', 'Lithuania', 'Sweden'],
            'V' => ['Andorra', 'Bosnia and Herzegovina', 'Cyprus', 'Gibraltar', 'Iceland', 'Malta', 'Montenegro', 'North Macedonia', 'Norway', 'Serbia', 'Turkey', 'Ukraine'],
            'W' => ['Liechtenstein', 'Switzerland'],
            'X' => ['United Kingdom'],
        ];

        $rows = [];
        foreach ($zones as $zoneCode => $countries) {
            $isEu = in_array($zoneCode, $this->europeanZones);
            foreach ($countries as $name) {
                $code = $this->iso2[$name] ?? null;
                if (!$code) {
                    continue;
                }
                $rows[] = [
                    'country_name' => $name,
                    'country_code' => $code,
                    'zone_code' => $zoneCode,
                    'is_european_zone' => $isEu,
                    'is_eu' => in_array($code, $this->euCodes),
                ];
            }
        }

        DB::table('fedex_ficp_zones')->insert($rows);
    }

    private function seedRates(): void
    {
        $rows = [];

        // ── European zones (R, S, T, U, V, W, X) ────────────────────────────
        // Brackets from prices.json → export_rates → europe.
        // weight_max=2.5 → flat, weight_max=5.0 → flat, weight_max=10.0 → flat,
        // weight_max=NULL → tail (per-kg from 10 kg base).

        // String keys — prevents PHP float-to-int key casting (e.g. 2.5 → 2).
        $europeFlat = [
            '2.5' => ['R' => 7.81, 'S' => 8.00, 'T' => 9.61, 'U' => 11.29, 'V' => 20.13, 'W' => 12.93, 'X' => 11.02],
            '5.0' => ['R' => 7.81, 'S' => 8.00, 'T' => 9.61, 'U' => 11.29, 'V' => 20.13, 'W' => 12.93, 'X' => 11.02],
            '10.0' => ['R' => 10.59, 'S' => 11.05, 'T' => 12.69, 'U' => 16.18, 'V' => 30.80, 'W' => 17.27, 'X' => 15.51],
        ];
        $europePerKg = ['R' => 1.05, 'S' => 1.09, 'T' => 1.16, 'U' => 1.75, 'V' => 2.52, 'W' => 1.16, 'X' => 1.57];

        foreach (['R', 'S', 'T', 'U', 'V', 'W', 'X'] as $z) {
            foreach ($europeFlat as $wmax => $rates) {
                $rows[] = ['zone_code' => $z, 'weight_max' => $wmax, 'flat_rate' => $rates[$z], 'per_kg_rate' => 0];
            }
            // Tail row: flat_rate = base at 10 kg, per_kg_rate for extrapolation above 10.
            $rows[] = ['zone_code' => $z, 'weight_max' => null, 'flat_rate' => $europeFlat['10.0'][$z], 'per_kg_rate' => $europePerKg[$z]];
        }

        // ── World zones (A, B, C, D, E, F, G, H) ────────────────────────────
        // Discrete brackets from prices.json → export_rates → world.
        // weight_max=NULL tail → per-kg from 20 kg base.

        // String keys required — PHP silently casts float keys to int (2.5 → 2, colliding with 2.0).
        $worldFlat = [
            '0.5' => ['A' => 18.23, 'B' => 20.72, 'C' => 22.70, 'D' => 24.84, 'E' => 25.34, 'F' => 19.97, 'G' => 25.99, 'H' => 16.98],
            '1.0' => ['A' => 19.46, 'B' => 22.42, 'C' => 23.11, 'D' => 25.48, 'E' => 26.15, 'F' => 19.98, 'G' => 29.94, 'H' => 17.53],
            '1.5' => ['A' => 22.03, 'B' => 25.40, 'C' => 26.17, 'D' => 28.86, 'E' => 29.61, 'F' => 22.62, 'G' => 33.89, 'H' => 19.85],
            '2.0' => ['A' => 24.60, 'B' => 28.39, 'C' => 29.23, 'D' => 32.23, 'E' => 33.07, 'F' => 25.26, 'G' => 37.84, 'H' => 22.16],
            '2.5' => ['A' => 27.17, 'B' => 31.37, 'C' => 32.29, 'D' => 35.61, 'E' => 36.53, 'F' => 27.90, 'G' => 41.79, 'H' => 24.48],
            '3.0' => ['A' => 28.14, 'B' => 33.21, 'C' => 33.94, 'D' => 38.98, 'E' => 39.99, 'F' => 29.24, 'G' => 46.53, 'H' => 25.58],
            '5.0' => ['A' => 37.87, 'B' => 44.74, 'C' => 45.70, 'D' => 52.49, 'E' => 53.84, 'F' => 39.36, 'G' => 62.61, 'H' => 34.43],
            '10.0' => ['A' => 67.48, 'B' => 80.70, 'C' => 81.80, 'D' => 93.31, 'E' => 96.56, 'F' => 73.11, 'G' => 112.32, 'H' => 65.46],
            '20.0' => ['A' => 130.34, 'B' => 153.63, 'C' => 161.03, 'D' => 181.66, 'E' => 189.16, 'F' => 132.62, 'G' => 212.42, 'H' => 122.64],
        ];
        $worldPerKg = ['A' => 8.24, 'B' => 9.38, 'C' => 8.57, 'D' => 11.28, 'E' => 10.81, 'F' => 6.98, 'G' => 11.95, 'H' => 7.59];

        // Zone P (Hong Kong) — same rates as zone B
        $worldFlat['0.5']['P'] = 20.72;
        $worldFlat['1.0']['P'] = 22.42;
        $worldFlat['1.5']['P'] = 25.40;
        $worldFlat['2.0']['P'] = 28.39;
        $worldFlat['2.5']['P'] = 31.37;
        $worldFlat['3.0']['P'] = 33.21;
        $worldFlat['5.0']['P'] = 44.74;
        $worldFlat['10.0']['P'] = 80.70;
        $worldFlat['20.0']['P'] = 153.63;
        $worldPerKg['P'] = 9.38;

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'P'] as $z) {
            foreach ($worldFlat as $wmax => $rates) {
                $rows[] = ['zone_code' => $z, 'weight_max' => $wmax, 'flat_rate' => $rates[$z], 'per_kg_rate' => 0];
            }
            // Tail row: flat_rate = base at 20 kg, per_kg_rate for extrapolation above 20.
            $rows[] = ['zone_code' => $z, 'weight_max' => null, 'flat_rate' => $worldFlat['20.0'][$z], 'per_kg_rate' => $worldPerKg[$z]];
        }

        DB::table('fedex_ficp_rates')->insert($rows);
    }

    private function seedDemandGroups(): void
    {
        // Groups from fiel_surcharge.json → surcharge_rates.
        $groups = [
            ['group_name' => 'intra_europe', 'base_rate' => 0.00, 'per_kg_rate' => 0.00],
            ['group_name' => 'usa', 'base_rate' => 0.00, 'per_kg_rate' => 0.00],
            ['group_name' => 'canada', 'base_rate' => 0.00, 'per_kg_rate' => 0.00],
            ['group_name' => 'lac', 'base_rate' => 0.00, 'per_kg_rate' => 0.00],
            ['group_name' => 'israel', 'base_rate' => 0.90, 'per_kg_rate' => 1.00],
            ['group_name' => 'meisa', 'base_rate' => 0.90, 'per_kg_rate' => 1.50],
            ['group_name' => 'apac', 'base_rate' => 0.90, 'per_kg_rate' => 0.09],
            ['group_name' => 'oceania', 'base_rate' => 0.90, 'per_kg_rate' => 0.09],
            ['group_name' => 'default', 'base_rate' => 0.90, 'per_kg_rate' => 0.70],
        ];

        DB::table('fedex_ficp_demand_groups')->insert($groups);

        $groupIds = DB::table('fedex_ficp_demand_groups')->pluck('id', 'group_name');

        $countryGroups = [
            'intra_europe' => ['Albania', 'Andorra', 'Austria', 'Belarus', 'Belgium', 'Bosnia-Herzegovina', 'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Estonia', 'Finland', 'France', 'Germany', 'Greece', 'Hungary', 'Ireland', 'Italy', 'Latvia', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Malta', 'Monaco', 'Montenegro', 'Netherlands', 'Norway', 'Poland', 'Portugal', 'Republic of Moldova', 'Romania', 'San Marino', 'Serbia', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Turkey', 'Ukraine', 'United Kingdom', 'Vatican City'],
            'usa' => ['USA'],
            'canada' => ['Canada'],
            'lac' => ['Anguilla', 'Antigua & Barbuda', 'Argentina', 'Aruba', 'Bahama', 'Barbados', 'Belize', 'Bermuda', 'Bolivia', 'Brazil', 'British Virgin Islands', 'Cayman Islands', 'Chile', 'Colombia', 'Costa Rica', 'Curacao', 'Dominica', 'Dominican Republic', 'Ecuador', 'El Salvador', 'Grenada', 'Guadeloupe', 'Guatemala', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Jamaica', 'Martinique', 'Mexico', 'Montserrat', 'Nicaragua', 'Panama', 'Paraguay', 'Peru', 'Puerto Rico', 'Saint Lucia', 'Suriname', 'Trinidad & Tobago', 'Uruguay', 'Venezuela'],
            'israel' => ['Israel'],
            'meisa' => ['Algeria', 'Armenia', 'Bahrain', 'Bangladesh', 'Bhutan', 'Egypt', 'Georgia', 'India', 'Jordan', 'Kuwait', 'Lebanon', 'Libya', 'Morocco', 'Myanmar', 'Nepal', 'Oman', 'Pakistan', 'Palestinian Territory', 'Qatar', 'Saudi Arabia', 'Sri Lanka', 'Syrian Arab Republic', 'Tunisia', 'United Arab Emirates', 'Yemen'],
            'apac' => ['Cambodia', 'East Timor', 'Indonesia', 'Japan', 'Laos', 'Macau SAR, China', 'Malaysia', 'Philippines', 'Singapore', 'South Korea', 'Taiwan', 'Thailand', 'Vietnam', 'China', 'Hong Kong SAR, China'],
            'oceania' => ['Australia', 'New Zealand'],
        ];

        $rows = [];
        foreach ($countryGroups as $groupName => $countries) {
            $groupId = $groupIds[$groupName];
            foreach ($countries as $name) {
                $code = $this->iso2[$name] ?? null;
                if (!$code) {
                    continue;
                }
                // Avoid duplicates (same country can't be in two groups in this list, but just in case)
                $rows[$code] = ['country_code' => $code, 'group_id' => $groupId];
            }
        }

        DB::table('fedex_ficp_demand_countries')->insert(array_values($rows));
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'fuel_surcharge_rate', 'value' => '0.48'],
            ['key' => 'eu_fuel_discount', 'value' => '0.50'],
            ['key' => 'vat_rate', 'value' => '22'],
        ];

        DB::table('fedex_ficp_settings')->insertOrIgnore($settings);
    }
}
