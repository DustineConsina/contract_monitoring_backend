<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=pfda_contract_db',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Find agatha carungcong
    echo "=== SEARCHING FOR AGATHA ===\n";
    $stmt = $pdo->query("SELECT id, contact_person, business_name, business_type, tin FROM tenants WHERE contact_person LIKE '%agatha%' OR business_name LIKE '%agatha%'");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tenants, JSON_PRETTY_PRINT) . "\n";
    
    if ($tenants) {
        $tenant_id = $tenants[0]['id'];
        
        // Find contracts for this tenant
        echo "\n=== CONTRACTS FOR TENANT $tenant_id ===\n";
        $stmt = $pdo->prepare("SELECT id, contract_number, status FROM contracts WHERE tenant_id = ?");
        $stmt->execute([$tenant_id]);
        $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($contracts, JSON_PRETTY_PRINT) . "\n";
        
        if ($contracts) {
            $contract_id = $contracts[0]['id'];
            
            // Check tenant with full details
            echo "\n=== CONTRACT $contract_id WITH RELATIONSHIPS ===\n";
            $stmt = $pdo->prepare("SELECT * FROM contracts WHERE id = ?");
            $stmt->execute([$contract_id]);
            $contract = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Contract: " . json_encode($contract, JSON_PRETTY_PRINT) . "\n";
            
            // Tenant full details
            $stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
            $stmt->execute([$tenant_id]);
            $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Tenant: " . json_encode($tenant, JSON_PRETTY_PRINT) . "\n";
            
            // Payments
            $stmt = $pdo->prepare("SELECT * FROM payments WHERE contract_id = ?");
            $stmt->execute([$contract_id]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Payments (" . count($payments) . "): " . json_encode($payments, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
