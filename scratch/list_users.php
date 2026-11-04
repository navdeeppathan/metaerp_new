<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\User::orderBy('name')->take(10)->get() as $u) {
    echo $u->name . ' | ' . $u->username . ' | ' . $u->displayName . ' | ' . ($u->active ? '1' : '0') . PHP_EOL;
}
