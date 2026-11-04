<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Blueprint;

class DummyPdo extends PDO {
    public function __construct() {}
}

$conn = new MySqlConnection(new DummyPdo(), 'metaerp', '', [
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'engine' => 'InnoDB'
]);
$grammar = new MySqlGrammar();
$conn->setSchemaGrammar($grammar);

$blueprint = new Blueprint('test_table');
$blueprint->create();
$blueprint->id();
$blueprint->string('name');
$blueprint->timestamps();

$statements = $blueprint->toSql($conn, $grammar);
print_r($statements);
