<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
\Illuminate\Support\Facades\DB::purge();

use App\Models\Department;
use App\Models\PackType;

// 1. Departments (Screenshot 1)
Department::truncate();
$departments = [
    'Sales',
    'Marketing',
    'Finance',
    'Purchase',
    'Technology',
    'Accountant',
];

foreach ($departments as $dept) {
    Department::create([
        'name' => $dept,
        'description' => $dept,
        'version' => 0,
    ]);
}
echo "Departments seeded (6 rows)!\n";

// 2. PackTypes (Screenshots 3 & 4: exactly 13 rows)
PackType::truncate();
$packTypes = [
    'Big Bags',
    'Pallets',
    'Drums',
    'Wooden Boxes',
    'Metal Boxes',
    'Half Drums',
    'Loose',
    'Bundle',
    'Boxes',
    'Packages',
    'Bags',
    'Bulk',
    'Cartons',
];

foreach ($packTypes as $pt) {
    PackType::create([
        'description' => $pt,
        'version' => 0,
    ]);
}
echo "PackTypes seeded (13 rows)!\n";
