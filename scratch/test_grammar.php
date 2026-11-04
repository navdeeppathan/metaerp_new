<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\MySqlBuilder;

// Create dummy PDO for MySQL grammar
class DummyMySqlPdo extends PDO {
    public function __construct() {}
}

$pdo = new DummyMySqlPdo();
$conn = new MySqlConnection($pdo, 'metaerp', '', ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']);
$grammar = new MySqlGrammar();
$conn->setSchemaGrammar($grammar);

$schema = new MySqlBuilder($conn);

$queries = [];
$conn->listen(function ($query) use (&$queries) {
    $queries[] = $query->sql;
});

// Test on one migration
$migration = require __DIR__ . '/../database/migrations/2024_01_01_000001_create_metaerp_core_lookups_tables.php';

// Temporarily swap Schema facade or use schema builder
$ref = new ReflectionClass(Illuminate\Support\Facades\Schema::class);
// Let's see if we can use Schema::setFacadeApplication or just use connection directly
echo "Script loaded successfully\n";
