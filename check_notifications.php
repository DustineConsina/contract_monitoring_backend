<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;
use App\Models\Contract;

// Check contracts
$contracts = Contract::where('status', 'active')->take(2)->get();
echo "Checking contracts:\n";
foreach ($contracts as $c) {
    echo "  - Contract {$c->contract_number}: tenant_id={$c->tenant_id}, last_notification_sent={$c->last_notification_sent}\n";
}

// Check notifications  
$notifications = Notification::where('type', 'contract_renewal')->get();
echo "\nContract renewal notifications: " . $notifications->count() . "\n";
foreach ($notifications as $n) {
    echo "  - ID {$n->id}: user_id={$n->user_id}, title={$n->title}, is_read={$n->is_read}\n";
    if ($n->data) {
        echo "    Data: " . json_encode($n->data) . "\n";
    }
}

?>
