<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$pdo->exec("UPDATE accessgroups SET active = 0 WHERE id = 1");
$cnt = $pdo->query("SELECT COUNT(*) FROM accessgroups WHERE active = 1")->fetchColumn();
echo "Active access groups: {$cnt}\n";
