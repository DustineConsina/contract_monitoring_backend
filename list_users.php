<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pfda_contract_db', 'root', '');
$stmt = $pdo->query('SELECT id, email, role FROM users LIMIT 5');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo implode(' | ', $row) . "\n";
}
