<?php

$sqlitePath = __DIR__ . '/../database/database.sqlite';
$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

        // Determine MySQL type
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

echo "=== COUNTRIES DDL ===\n";
echo generateMysqlDdl($pdo, 'countries') . "\n\n";

echo "=== CURRENCY DDL ===\n";
echo generateMysqlDdl($pdo, 'currency') . "\n\n";

echo "=== USERS DDL ===\n";
echo generateMysqlDdl($pdo, 'users') . "\n\n";
