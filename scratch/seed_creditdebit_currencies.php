<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
\Illuminate\Support\Facades\DB::purge();

use App\Models\CreditDebitType;
use App\Models\Currency;

// 1. CreditDebitTypes
CreditDebitType::truncate();

$cdRows = [
    'Delay',
    'Container Damage',
    'Free Time Not applied from Shipping Line',
];

foreach ($cdRows as $desc) {
    CreditDebitType::create([
        'description' => $desc,
        'version' => 0,
    ]);
}
echo "CreditDebitTypes seeded (3 rows)!\n";

// 2. Currencies
$currencyRows = [
    [
        'name' => 'United States Dollar',
        'abbreviation' => 'USD',
        'symbol' => '$',
        'htmlEncodeSymbol' => '&#36;',
        'description' => 'USD',
        'rate' => 1.25,
    ],
    [
        'name' => 'Saudi Riyal',
        'abbreviation' => 'SAR',
        'symbol' => 'R',
        'htmlEncodeSymbol' => 'R',
        'description' => 'SAR',
        'rate' => 3.75,
    ],
    [
        'name' => 'Euro',
        'abbreviation' => 'EUR',
        'symbol' => '€',
        'htmlEncodeSymbol' => '&euro;',
        'description' => 'EUR',
        'rate' => 1.15,
    ],
    [
        'name' => 'Sterling',
        'abbreviation' => 'GBP',
        'symbol' => '£',
        'htmlEncodeSymbol' => '&pound;',
        'description' => 'GBP',
        'rate' => 1.0,
    ],
    [
        'name' => 'Australian Dollar',
        'abbreviation' => 'AUD',
        'symbol' => '$',
        'htmlEncodeSymbol' => '&#36;',
        'description' => 'AUD',
        'rate' => 1.85,
    ],
    [
        'name' => 'Canadian Dollar',
        'abbreviation' => 'CAD',
        'symbol' => '$',
        'htmlEncodeSymbol' => '&#36;',
        'description' => 'CAD',
        'rate' => 1.70,
    ],
];

Currency::truncate();

foreach ($currencyRows as $c) {
    Currency::create([
        'name' => $c['name'],
        'abbreviation' => $c['abbreviation'],
        'symbol' => $c['symbol'],
        'htmlEncodeSymbol' => $c['htmlEncodeSymbol'],
        'description' => $c['description'],
        'rate' => $c['rate'],
        'version' => 0,
    ]);
}
echo "Currencies seeded (6 rows)!\n";
