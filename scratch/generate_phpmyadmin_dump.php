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

class FormatterMySqlBuilder extends MySqlBuilder {
    public static array $createStatements = [];
    public static array $alterStatements = [];

    protected function build(Blueprint $blueprint) {
        $statements = $blueprint->toSql($this->connection, $this->grammar);
        foreach ($statements as $stmt) {
            if (preg_match('/^create table/i', $stmt)) {
                self::$createStatements[$blueprint->getTable()] = $stmt;
            } else {
                self::$alterStatements[] = $stmt;
            }
        }
    }
}

class FormatterMySqlConnection extends MySqlConnection {
    public function getSchemaBuilder() {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new FormatterMySqlBuilder($this);
    }
}

$mockConn = new FormatterMySqlConnection(new DummyPdo(), 'metaerp', '', [
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'engine' => 'InnoDB'
]);
$grammar = new MySqlGrammar();
$mockConn->setSchemaGrammar($grammar);

$app['config']->set('database.connections.fmt_mysql', [
    'driver' => 'fmt_mysql',
    'database' => 'metaerp',
    'prefix' => '',
]);

$app['db']->extend('fmt_mysql', function() use ($mockConn) {
    return $mockConn;
});

DB::purge();
DB::setDefaultConnection('fmt_mysql');

// Run migrations in order
$migrationFiles = glob(__DIR__ . '/../database/migrations/*.php');
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    $migration = require $file;
    if (is_object($migration) && method_exists($migration, 'up')) {
        $migration->up();
    }
}

// Function to format a single-line create table into a multi-line formatted CREATE TABLE IF NOT EXISTS
function formatCreateTable(string $stmt): string {
    // Replace create table with CREATE TABLE IF NOT EXISTS
    $stmt = preg_replace('/^create table `([^`]+)` \((.*)\) (default character set.*)$/i', 'CREATE TABLE IF NOT EXISTS `$1` ($2) $3', $stmt);
    
    if (!preg_match('/^CREATE TABLE IF NOT EXISTS `([^`]+)` \((.*)\) (default character set.*engine = InnoDB)$/i', $stmt, $m)) {
        return $stmt . ";";
    }

    $tableName = $m[1];
    $columnsPart = $m[2];
    $enginePart = $m[3];

    // Standardize engine and charset
    $enginePart = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Split columns by comma, but be careful with parentheses in defaults or types like decimal(10, 2)
    $tokens = [];
    $depth = 0;
    $curr = '';
    for ($i = 0; $i < strlen($columnsPart); $i++) {
        $char = $columnsPart[$i];
        if ($char === '(') $depth++;
        elseif ($char === ')') $depth--;
        elseif ($char === ',' && $depth === 0) {
            $tokens[] = trim($curr);
            $curr = '';
            continue;
        }
        $curr .= $char;
    }
    if (trim($curr) !== '') {
        $tokens[] = trim($curr);
    }

    $lines = [];
    $primaryKeys = [];
    $regularCols = [];

    foreach ($tokens as $token) {
        if (preg_match('/^primary key \((.*)\)$/i', $token, $pkMatch)) {
            $primaryKeys[] = "  PRIMARY KEY (" . $pkMatch[1] . ")";
        } elseif (preg_match('/^(.*) auto_increment primary key(.*)$/i', $token, $aiMatch)) {
            // e.g. `id` bigint unsigned not null auto_increment primary key
            $cleanCol = trim($aiMatch[1] . " auto_increment" . $aiMatch[2]);
            $regularCols[] = "  " . $cleanCol;
            preg_match('/^`([^`]+)`/', $token, $nameMatch);
            if (!empty($nameMatch[1])) {
                $primaryKeys[] = "  PRIMARY KEY (`" . $nameMatch[1] . "`)";
            }
        } else {
            $regularCols[] = "  " . $token;
        }
    }

    $allParts = array_merge($regularCols, $primaryKeys);
    $formatted = "CREATE TABLE IF NOT EXISTS `$tableName` (\n" . implode(",\n", $allParts) . "\n) $enginePart;";
    return $formatted;
}

// Connect to SQLite to read current data
$sqlitePdo = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$out = [];
$out[] = "-- phpMyAdmin SQL Dump";
$out[] = "-- MetaERP Enterprise Modernization";
$out[] = "-- Database: `metaerp`";
$out[] = "-- Generated on: " . date('Y-m-d H:i:s');
$out[] = "";
$out[] = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";";
$out[] = "START TRANSACTION;";
$out[] = "SET time_zone = \"+00:00\";";
$out[] = "";
$out[] = "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;";
$out[] = "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;";
$out[] = "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;";
$out[] = "/*!40101 SET NAMES utf8mb4 */;";
$out[] = "";
$out[] = "-- ----------------------------------------------------------------------------";
$out[] = "-- OPTIONAL DATABASE CREATION (Uncomment if you haven't created a database yet):";
$out[] = "-- CREATE DATABASE IF NOT EXISTS `metaerp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
$out[] = "-- USE `metaerp`;";
$out[] = "-- ----------------------------------------------------------------------------";
$out[] = "";
$out[] = "SET FOREIGN_KEY_CHECKS=0;";
$out[] = "";

// 1. Migrations table
$out[] = "-- --------------------------------------------------------";
$out[] = "--";
$out[] = "-- Table structure for table `migrations`";
$out[] = "--";
$out[] = "";
$out[] = "CREATE TABLE IF NOT EXISTS `migrations` (";
$out[] = "  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,";
$out[] = "  `migration` varchar(255) NOT NULL,";
$out[] = "  `batch` int(11) NOT NULL,";
$out[] = "  PRIMARY KEY (`id`)";
$out[] = ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$out[] = "";

// 2. All other tables
foreach (FormatterMySqlBuilder::$createStatements as $tableName => $createSql) {
    $out[] = "-- --------------------------------------------------------";
    $out[] = "--";
    $out[] = "-- Table structure for table `$tableName`";
    $out[] = "--";
    $out[] = "";
    $out[] = formatCreateTable($createSql);
    $out[] = "";
}

// 3. Table indexes & non-FK alters
$fkStatements = [];
$indexStatements = [];

foreach (FormatterMySqlBuilder::$alterStatements as $alterStmt) {
    if (preg_match('/foreign key/i', $alterStmt)) {
        $fkStatements[] = $alterStmt . ";";
    } else {
        $indexStatements[] = $alterStmt . ";";
    }
}

if (!empty($indexStatements)) {
    $out[] = "-- --------------------------------------------------------";
    $out[] = "--";
    $out[] = "-- Indexes for dumped tables";
    $out[] = "--";
    $out[] = "";
    foreach ($indexStatements as $idx) {
        $out[] = $idx;
    }
    $out[] = "";
}

// 4. Data dumps
$out[] = "-- ========================================================";
$out[] = "-- Dumping data for tables";
$out[] = "-- ========================================================";
$out[] = "";

// Migrations data
$migStmt = $sqlitePdo->query("SELECT * FROM migrations ORDER BY id ASC");
$migRows = $migStmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($migRows)) {
    $out[] = "-- Dumping data for table `migrations`";
    $migInserts = [];
    foreach ($migRows as $row) {
        $migInserts[] = sprintf("(%d, %s, %d)", $row['id'], $sqlitePdo->quote($row['migration']), $row['batch']);
    }
    $out[] = "INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES\n" . implode(",\n", $migInserts) . ";";
    $out[] = "";
}

$tablesStmt = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations' ORDER BY name");
$allTables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($allTables as $table) {
    $countStmt = $sqlitePdo->query("SELECT COUNT(*) FROM `{$table}`");
    $rowCount = (int) $countStmt->fetchColumn();
    if ($rowCount === 0) continue;

    $rowsStmt = $sqlitePdo->query("SELECT * FROM `{$table}`");
    $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

    $out[] = "-- Dumping data for table `$table` ($rowCount rows)";
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

    $chunks = array_chunk($valuesClauses, 50);
    foreach ($chunks as $chunk) {
        $out[] = "INSERT INTO `$table` ($columnListStr) VALUES\n" . implode(",\n", $chunk) . ";";
    }
    $out[] = "";
}

// 5. Constraints / Foreign Keys at the very end
if (!empty($fkStatements)) {
    $out[] = "-- ========================================================";
    $out[] = "-- Constraints for dumped tables (Foreign Keys)";
    $out[] = "-- ========================================================";
    $out[] = "";
    foreach ($fkStatements as $fk) {
        $out[] = $fk;
    }
    $out[] = "";
}

$out[] = "SET FOREIGN_KEY_CHECKS=1;";
$out[] = "COMMIT;";
$out[] = "";
$out[] = "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;";
$out[] = "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;";
$out[] = "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
$out[] = "";

$finalSql = implode("\n", $out);
file_put_contents(__DIR__ . '/../metaerp_database_mysql.sql', $finalSql);

echo "Successfully wrote clean phpMyAdmin formatted SQL!\n";
echo "File size: " . number_format(filesize(__DIR__ . '/../metaerp_database_mysql.sql')) . " bytes\n";
