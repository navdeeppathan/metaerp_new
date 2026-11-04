<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\MySqlBuilder;

class DummyPdo extends PDO {
    public function __construct() {}
}

class PretendMySqlBuilder extends MySqlBuilder {
    public static array $capturedSql = [];

    protected function build(Blueprint $blueprint) {
        $statements = $blueprint->toSql($this->connection, $this->grammar);
        foreach ($statements as $stmt) {
            self::$capturedSql[] = $stmt;
        }
    }
}

class PretendMySqlConnection extends MySqlConnection {
    public function getSchemaBuilder() {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new PretendMySqlBuilder($this);
    }
}

$mockConn = new PretendMySqlConnection(new DummyPdo(), 'metaerp', '', [
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'engine' => 'InnoDB'
]);
$grammar = new MySqlGrammar();
$mockConn->setSchemaGrammar($grammar);

// Set configuration
$app['config']->set('database.connections.pretend_mysql', [
    'driver' => 'pretend_mysql',
    'database' => 'metaerp',
    'prefix' => '',
]);

// Bind mockConn into Laravel's db manager
$app['db']->extend('pretend_mysql', function() use ($mockConn) {
    return $mockConn;
});

// Set as default connection
$app['config']->set('database.default', 'pretend_mysql');
DB::purge();
DB::setDefaultConnection('pretend_mysql');

// Now run one migration
$migration = require __DIR__ . '/../database/migrations/2024_01_01_000001_create_metaerp_core_lookups_tables.php';
$migration->up();

print_r(PretendMySqlBuilder::$capturedSql);
