<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=pfda_contract_db',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check contract 7
    $stmt = $pdo->query("SELECT id, contract_number, tenant_id, status, monthly_rental FROM contracts WHERE id = 7");
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Contract 7:\n";
    echo json_encode($contract, JSON_PRETTY_PRINT) . "\n";
    
    // Get all contracts
    echo "\n\nAll Contracts:\n";
    $stmt = $pdo->query("SELECT id, contract_number, tenant_id, monthly_rental, status FROM contracts LIMIT 10");
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($contracts, JSON_PRETTY_PRINT) . "\n";
    
    // Check if payment ID 1 exists anywhere
    echo "\n\nPayments with ID <= 10:\n";
    $stmt = $pdo->query("SELECT id, payment_number, contract_id, amount_due, interest_amount, total_amount, balance FROM payments WHERE id <= 10 ORDER BY id");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($payments)) {
        echo "No payments with ID <= 10\n";
    } else {
        echo json_encode($payments, JSON_PRETTY_PRINT) . "\n";
    }
    
    // Check tenant
    if ($contract) {
        $stmt = $pdo->prepare("SELECT id, contact_person, business_name FROM tenants WHERE id = ?");
        $stmt->execute([$contract['tenant_id']]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nTenant:\n";
        echo json_encode($tenant, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
