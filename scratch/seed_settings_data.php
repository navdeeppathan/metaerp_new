<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
\Illuminate\Support\Facades\DB::purge();

use App\Models\Country;
use App\Models\Type;
use App\Models\CreditDebitType;

// 1. Countries
$countriesData = [
    ['name' => 'Albania', 'alpha2' => 'AL', 'alpha3' => 'ALB', 'region' => 'Balkans', 'vat' => 20],
    ['name' => 'Algeria', 'alpha2' => 'DZ', 'alpha3' => 'DZA', 'region' => 'North Africa', 'vat' => 19],
    ['name' => 'Argentina', 'alpha2' => 'AR', 'alpha3' => 'ARG', 'region' => 'South America', 'vat' => 21],
    ['name' => 'Australia', 'alpha2' => 'AU', 'alpha3' => 'AUS', 'region' => 'Oceania', 'vat' => 10],
    ['name' => 'Austria', 'alpha2' => 'AT', 'alpha3' => 'AUT', 'region' => 'Europe', 'vat' => 20],
    ['name' => 'Bahrain', 'alpha2' => 'BH', 'alpha3' => 'BHR', 'region' => 'Middle East', 'vat' => 10],
];

foreach ($countriesData as $c) {
    Country::updateOrCreate(
        ['name' => $c['name']],
        [
            'alpha2' => $c['alpha2'],
            'alpha3' => $c['alpha3'],
            'region' => $c['region'],
            'vat' => $c['vat'],
            'active' => 1,
            'version' => 0,
        ]
    );
}
echo "Countries seeded successfully!\n";

// 2. Company Types
$companyTypes = [
    'Buyer',
    'Seller',
    'Trader',
    'Metal Agent',
    'Galvanizer',
    'Recycler',
];

foreach ($companyTypes as $tName) {
    Type::firstOrCreate(
        ['description' => $tName],
        ['version' => 0]
    );
}
echo "Company types seeded successfully!\n";

// 3. Credit/Debit Types
$cdTypes = ['Credit', 'Debit'];
foreach ($cdTypes as $cd) {
    CreditDebitType::firstOrCreate(
        ['description' => $cd],
        ['version' => 0]
    );
}
echo "Credit/Debit types seeded successfully!\n";
