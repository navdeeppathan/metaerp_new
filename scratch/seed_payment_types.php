<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
\Illuminate\Support\Facades\DB::purge();

use App\Models\PaymentType;
use App\Models\PaymentTermsType;

// 1. PaymentTypes (14 rows)
PaymentType::truncate();
$paymentTypes = [
    'Sight LC',
    'Deferred LC',
    'CAD',
    'Barter',
    'Part Payment',
    'International Payment',
    'Swift',
    'Bacs',
    'Advance',
    'Credit',
    'Bank Cheque',
    'Wire Transfer',
    'Telegraphic Transfer (TT)',
    'Cash',
];

foreach ($paymentTypes as $pt) {
    PaymentType::create([
        'description' => $pt,
        'version' => 0,
    ]);
}
echo "PaymentTypes seeded (14 rows)!\n";

// 2. PaymentTermsTypes (9 rows)
PaymentTermsType::truncate();
$paymentTerms = [
    'Days',
    'Months',
    'Yearly',
    'Quarter',
    'Weeks',
    'Days after BL',
    '%',
    '..',
    'Against',
];

foreach ($paymentTerms as $ptt) {
    PaymentTermsType::create([
        'description' => $ptt,
        'version' => 0,
    ]);
}
echo "PaymentTermsTypes seeded (9 rows)!\n";
