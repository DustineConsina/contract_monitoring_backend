<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=pfda_contract_db',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check contract 6
    $stmt = $pdo->query("SELECT id, contract_number, tenant_id, status FROM contracts WHERE id = 6");
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Contract 6:\n";
    echo json_encode($contract, JSON_PRETTY_PRINT) . "\n";
    
    // Check tenant
    if ($contract) {
        $stmt = $pdo->prepare("SELECT id, contact_person, business_name FROM tenants WHERE id = ?");
        $stmt->execute([$contract['tenant_id']]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTenant:\n";
        echo json_encode($tenant, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Check payments
    if ($contract) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE contract_id = ?");
        $stmt->execute([$contract['id']]);
        $payment_count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nPayment count: " . $payment_count['count'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
