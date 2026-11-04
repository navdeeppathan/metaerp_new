<?php
/**
 * MetaERP MySQL Export Generator for phpMyAdmin
 * Dynamically extracts all table schemas and data directly from SQLite
 * Guarantees 100% column parity between DDL and INSERT statements
 */

$sqlitePath = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$outputPath = __DIR__ . '/../metaerp_database_mysql.sql';
$out = fopen($outputPath, 'w');

function writeSql($str) {
    global $out;
    fwrite($out, $str . "\n");
}

writeSql("-- phpMyAdmin SQL Dump");
writeSql("-- MetaERP Enterprise Database Dump (100% Dynamic Schema Sync)");
writeSql("-- Generated on: " . date('Y-m-d H:i:s'));
writeSql("-- Compatible with MySQL 8.0+ / MariaDB 10.4+ / phpMyAdmin");
writeSql("");
writeSql("SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";");
writeSql("START TRANSACTION;");
writeSql("SET time_zone = \"+00:00\";");
writeSql("");
writeSql("/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;");
writeSql("/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;");
writeSql("/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;");
writeSql("/*!40101 SET NAMES utf8mb4 */;");
writeSql("");
writeSql("SET FOREIGN_KEY_CHECKS=0;");
writeSql("");

function generateMysqlDdl($pdo, $tableName) {
    $cols = $pdo->query("PRAGMA table_info(`{$tableName}`)")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cols)) return null;

    $colDefs = [];
    $primaryKeys = [];

    foreach ($cols as $col) {
        $name = $col['name'];
        $type = strtoupper($col['type']);
        $notnull = $col['notnull'] ? 'NOT NULL' : 'NULL';
        $dflt = $col['dflt_value'];

        if ($col['pk'] == 1 && ($type === 'INTEGER' || $type === 'INT')) {
            $mysqlType = "bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT";
            $colDefs[] = "  `{$name}` {$mysqlType}";
            $primaryKeys[] = "`{$name}`";
            continue;
        }

        if (str_contains($type, 'INT')) {
            if (str_contains($type, 'TINYINT')) {
                $mysqlType = "tinyint(1)";
            } else {
                $mysqlType = "bigint(20) UNSIGNED";
            }
        } elseif (str_contains($type, 'FLOAT') || str_contains($type, 'DOUBLE') || str_contains($type, 'REAL')) {
            $mysqlType = "double";
        } elseif (str_contains($type, 'TEXT')) {
            $mysqlType = "longtext";
        } elseif (str_contains($type, 'DATE')) {
            $mysqlType = "datetime";
        } else {
            $mysqlType = "varchar(191)";
        }

        $def = "  `{$name}` {$mysqlType} {$notnull}";
        if ($dflt !== null) {
            $def .= " DEFAULT {$dflt}";
        } elseif (!$col['notnull']) {
            $def .= " DEFAULT NULL";
        }
        $colDefs[] = $def;

        if ($col['pk'] == 1) {
            $primaryKeys[] = "`{$name}`";
        }
    }

    if (!empty($primaryKeys)) {
        $colDefs[] = "  PRIMARY KEY (" . implode(', ', $primaryKeys) . ")";
    }

    $ddl = "DROP TABLE IF EXISTS `{$tableName}`;\n";
    $ddl .= "CREATE TABLE `{$tableName}` (\n";
    $ddl .= implode(",\n", $colDefs) . "\n";
    $ddl .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    return $ddl;
}

// Fetch all tables
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

echo "Exporting " . count($tables) . " tables...\n";

foreach ($tables as $tableName) {
    $ddl = generateMysqlDdl($pdo, $tableName);
    if (!$ddl) continue;

    writeSql("-- --------------------------------------------------------");
    writeSql("--");
    writeSql("-- Table structure for table `{$tableName}`");
    writeSql("--");
    writeSql("");
    writeSql($ddl);
    writeSql("");

    // Dump Data
    $cols = $pdo->query("PRAGMA table_info(`{$tableName}`)")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'name');
    $quotedCols = array_map(fn($c) => "`{$c}`", $colNames);
    $colListStr = implode(', ', $quotedCols);

    $rows = $pdo->query("SELECT * FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        writeSql("--");
        writeSql("-- Dumping data for table `{$tableName}`");
        writeSql("--");
        writeSql("");

        $valuesRows = [];
        foreach ($rows as $row) {
            $vals = [];
            foreach ($colNames as $col) {
                $val = $row[$col];
                if ($val === null) {
                    $vals[] = "NULL";
                } elseif (is_numeric($val) && !is_string($val)) {
                    $vals[] = $val;
                } elseif (is_bool($val)) {
                    $vals[] = $val ? "1" : "0";
                } else {
                    $vals[] = "'" . addcslashes($val, "\0..\37'\x5c") . "'";
                }
            }
            $valuesRows[] = "(" . implode(', ', $vals) . ")";
        }

        writeSql("INSERT INTO `{$tableName}` ({$colListStr}) VALUES");
        writeSql(implode(",\n", $valuesRows) . ";");
        writeSql("");
    }
}

writeSql("SET FOREIGN_KEY_CHECKS=1;");
writeSql("COMMIT;");
writeSql("");
writeSql("/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;");
writeSql("/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;");
writeSql("/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;");

fclose($out);
echo "SQL Export completed successfully! Saved to: " . realpath($outputPath) . "\n";
echo "File size: " . filesize($outputPath) . " bytes\n";

// Also write split schema and data files
$fullSql = file_get_contents($outputPath);
$lines = explode("\n", $fullSql);
$schemaLines = [];
$dataLines = ["SET FOREIGN_KEY_CHECKS=0;", "START TRANSACTION;"];
$inInsertBlock = false;

foreach ($lines as $line) {
    if (str_starts_with($line, 'INSERT INTO')) {
        $inInsertBlock = true;
        $dataLines[] = $line;
        continue;
    }

    if ($inInsertBlock) {
        $dataLines[] = $line;
        if (str_ends_with(trim($line), ';')) {
            $inInsertBlock = false;
            $dataLines[] = "";
        }
        continue;
    }

    $schemaLines[] = $line;
}
$dataLines[] = "SET FOREIGN_KEY_CHECKS=1;";
$dataLines[] = "COMMIT;";

file_put_contents(__DIR__ . '/../metaerp_schema.sql', implode("\n", $schemaLines));
file_put_contents(__DIR__ . '/../metaerp_data.sql', implode("\n", $dataLines));
echo "Saved metaerp_schema.sql (" . filesize(__DIR__ . '/../metaerp_schema.sql') . " bytes)\n";
echo "Saved metaerp_data.sql (" . filesize(__DIR__ . '/../metaerp_data.sql') . " bytes)\n";
