<?php

$sql = file_get_contents(__DIR__ . '/../metaerp_database_mysql.sql');

// Split statements by semicolon at the end of lines
$lines = explode("\n", $sql);
$statements = [];
$currentStmt = '';

foreach ($lines as $line) {
    $trimmed = trim($line);
    if (empty($trimmed) || str_starts_with($trimmed, '--')) {
        continue;
    }
    $currentStmt .= $line . "\n";
    if (str_ends_with($trimmed, ';')) {
        $statements[] = trim($currentStmt);
        $currentStmt = '';
    }
}

echo "Total statements found: " . count($statements) . "\n";

$createTables = [];
$alterTables = [];
$inserts = [];
$other = [];

foreach ($statements as $stmt) {
    if (preg_match('/^create\s+table/i', $stmt)) {
        $createTables[] = $stmt;
    } elseif (preg_match('/^alter\s+table/i', $stmt)) {
        $alterTables[] = $stmt;
    } elseif (preg_match('/^insert\s+into/i', $stmt)) {
        $inserts[] = $stmt;
    } else {
        $other[] = $stmt;
    }
}

echo "CREATE TABLE statements: " . count($createTables) . "\n";
echo "ALTER TABLE statements: " . count($alterTables) . "\n";
echo "INSERT INTO statements: " . count($inserts) . "\n";
echo "Other statements: " . count($other) . "\n";
print_r($other);
