<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\MySqlBuilder;

class DummyPdo extends PDO {
    public function __construct() {}
}

class ExportMySqlBuilder extends MySqlBuilder {
    public static array $capturedSql = [];

    protected function build(Blueprint $blueprint) {
        $statements = $blueprint->toSql($this->connection, $this->grammar);
        foreach ($statements as $stmt) {
            self::$capturedSql[] = $stmt;
        }
    }
}

class ExportMySqlConnection extends MySqlConnection {
    public function getSchemaBuilder() {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new ExportMySqlBuilder($this);
    }
}

$mockConn = new ExportMySqlConnection(new DummyPdo(), 'metaerp', '', [
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'engine' => 'InnoDB'
]);
$grammar = new MySqlGrammar();
$mockConn->setSchemaGrammar($grammar);

$app['config']->set('database.connections.export_mysql', [
    'driver' => 'export_mysql',
    'database' => 'metaerp',
    'prefix' => '',
]);

$app['db']->extend('export_mysql', function() use ($mockConn) {
    return $mockConn;
});

DB::purge();
DB::setDefaultConnection('export_mysql');

// Run migrations in chronological order
$migrationFiles = glob(__DIR__ . '/../database/migrations/*.php');
sort($migrationFiles);

echo "Processing " . count($migrationFiles) . " migration files for MySQL DDL...\n";

foreach ($migrationFiles as $file) {
    $migration = require $file;
    if (is_object($migration) && method_exists($migration, 'up')) {
        $migration->up();
    }
}

echo "Captured " . count(ExportMySqlBuilder::$capturedSql) . " DDL statements.\n";

// Now switch to SQLite to extract existing data
$sqlitePdo = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Read migrations table records from SQLite
$migrationsTableDdl = "CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$sqlOutput = [];
$sqlOutput[] = "-- ============================================================================";
$sqlOutput[] = "-- MetaERP MySQL Database Dump for phpMyAdmin";
$sqlOutput[] = "-- Generated on: " . date('Y-m-d H:i:s');
$sqlOutput[] = "-- Target Database: MySQL 5.7+ / 8.0+ / MariaDB 10.3+";
$sqlOutput[] = "-- ============================================================================";
$sqlOutput[] = "";
$sqlOutput[] = "SET FOREIGN_KEY_CHECKS=0;";
$sqlOutput[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
$sqlOutput[] = "SET time_zone = '+00:00';";
$sqlOutput[] = "";
$sqlOutput[] = "-- 1. Create Database if not exists";
$sqlOutput[] = "CREATE DATABASE IF NOT EXISTS `metaerp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
$sqlOutput[] = "USE `metaerp`;";
$sqlOutput[] = "";
$sqlOutput[] = "-- 2. Migrations Tracking Table";
$sqlOutput[] = "DROP TABLE IF EXISTS `migrations`;";
$sqlOutput[] = $migrationsTableDdl;
$sqlOutput[] = "";

// Collect table creation statements
$createdTables = [];
foreach (ExportMySqlBuilder::$capturedSql as $stmt) {
    // If it's a create table statement, add DROP TABLE IF EXISTS first
    if (preg_match('/create table `([^`]+)`/i', $stmt, $m)) {
        $tableName = $m[1];
        $createdTables[] = $tableName;
        $sqlOutput[] = "-- ----------------------------------------------------------------------------";
        $sqlOutput[] = "-- Table structure for `$tableName`";
        $sqlOutput[] = "-- ----------------------------------------------------------------------------";
        $sqlOutput[] = "DROP TABLE IF EXISTS `$tableName`;";
        $sqlOutput[] = $stmt . ";";
        $sqlOutput[] = "";
    } else {
        // Alter table or add foreign key
        $sqlOutput[] = $stmt . ";";
        $sqlOutput[] = "";
    }
}

// Add migrations table data
$sqlOutput[] = "-- ----------------------------------------------------------------------------";
$sqlOutput[] = "-- Data for table `migrations`";
$sqlOutput[] = "-- ----------------------------------------------------------------------------";
$migStmt = $sqlitePdo->query("SELECT * FROM migrations ORDER BY id ASC");
$migRows = $migStmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($migRows)) {
    $migInserts = [];
    foreach ($migRows as $row) {
        $migInserts[] = sprintf("(%d, %s, %d)", $row['id'], $sqlitePdo->quote($row['migration']), $row['batch']);
    }
    $sqlOutput[] = "INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES\n" . implode(",\n", $migInserts) . ";";
    $sqlOutput[] = "";
}

// Extract and dump data for all tables
$sqlOutput[] = "-- ============================================================================";
$sqlOutput[] = "-- Table Data / Initial Seed Records";
$sqlOutput[] = "-- ============================================================================";
$sqlOutput[] = "";

$tablesStmt = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations' ORDER BY name");
$allTables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

$totalRowsCount = 0;

foreach ($allTables as $table) {
    $countStmt = $sqlitePdo->query("SELECT COUNT(*) FROM `{$table}`");
    $rowCount = (int) $countStmt->fetchColumn();
    if ($rowCount === 0) {
        continue;
    }

    $totalRowsCount += $rowCount;
    $rowsStmt = $sqlitePdo->query("SELECT * FROM `{$table}`");
    $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlOutput[] = "-- Data for table `$table` ($rowCount rows)";
    $columns = array_keys($rows[0]);
    $quotedColumns = array_map(fn($col) => "`$col`", $columns);
    $columnListStr = implode(', ', $quotedColumns);

    $valuesClauses = [];
    foreach ($rows as $row) {
        $rowValues = [];
        foreach ($row as $val) {
            if ($val === null) {
                $rowValues[] = "NULL";
            } elseif (is_numeric($val) && !is_string($val)) {
                $rowValues[] = $val;
            } elseif (is_string($val)) {
                $escaped = str_replace(["\\", "\0", "\n", "\r", "\x1a", "'"], ["\\\\", "\\0", "\\n", "\\r", "\\Z", "\'"], $val);
                $rowValues[] = "'" . $escaped . "'";
            } else {
                $rowValues[] = "'" . addslashes((string)$val) . "'";
            }
        }
        $valuesClauses[] = "(" . implode(", ", $rowValues) . ")";
    }

    // Chunk in groups of 50 for clean reading in phpMyAdmin
    $chunks = array_chunk($valuesClauses, 50);
    foreach ($chunks as $chunk) {
        $sqlOutput[] = "INSERT INTO `$table` ($columnListStr) VALUES\n" . implode(",\n", $chunk) . ";";
    }
    $sqlOutput[] = "";
}

$sqlOutput[] = "SET FOREIGN_KEY_CHECKS=1;";
$sqlOutput[] = "COMMIT;";
$sqlOutput[] = "";

$destFile = __DIR__ . '/../metaerp_database_mysql.sql';
file_put_contents($destFile, implode("\n", $sqlOutput));

echo "SUCCESS! Exported " . count($createdTables) . " tables and $totalRowsCount data rows to:\n";
echo realpath($destFile) . "\n";
echo "File size: " . number_format(filesize($destFile)) . " bytes\n";
