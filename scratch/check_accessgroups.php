<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$groups = $pdo->query("SELECT * FROM accessgroups")->fetchAll(PDO::FETCH_ASSOC);
echo "Access Groups in DB:\n";
print_r($groups);

$access = $pdo->query("SELECT * FROM access LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "Sample access rows:\n";
print_r($access);
