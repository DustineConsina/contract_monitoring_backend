<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$pdo = new PDO('sqlite:database/database.sqlite');
$pdo->exec('UPDATE contracts SET last_notification_sent = NULL WHERE contract_number IN ("CNT-000002", "CNT-000003")');
$result = $pdo->query('SELECT contract_number, last_notification_sent FROM contracts WHERE contract_number IN ("CNT-000002", "CNT-000003")');
echo "Cleared last_notification_sent:\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "  Contract {$row['contract_number']}: last_notification_sent = {$row['last_notification_sent']}\n";
}
$result = $pdo->query('SELECT COUNT(*) as count FROM notifications WHERE type = "contract_renewal"');
$count = $result->fetch(PDO::FETCH_ASSOC);
echo "\nTotal contract_renewal notifications in DB: {$count['count']}\n";
?>
