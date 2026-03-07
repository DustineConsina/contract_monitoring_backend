INSERT INTO users (name, email, phone, address, status, role, password, created_at, updated_at) VALUES
('John Doe', 'john.doe@example.com', '09123456789', '123 Main St', 'active', 'tenant', '$2y$12$GQjLPpfB1dJw1bJ4c7s8JeL8q1c8q1c8q1c8q1c8q1c8q1c8q1c8', NOW(), NOW()),
('Jane Smith', 'jane.smith@example.com', '09987654321', '456 Oak Ave', 'active', 'tenant', '$2y$12$GQjLPpfB1dJw1bJ4c7s8JeL8q1c8q1c8q1c8q1c8q1c8q1c8q1c8', NOW(), NOW()),
('Bob Wilson', 'bob.wilson@example.com', '09234567890', '789 Pine Rd', 'active', 'tenant', '$2y$12$GQjLPpfB1dJw1bJ4c7s8JeL8q1c8q1c8q1c8q1c8q1c8q1c8q1c8', NOW(), NOW()),
('Alice Johnson', 'alice.johnson@example.com', '09345678901', '321 Elm St', 'active', 'tenant', '$2y$12$GQjLPpfB1dJw1bJ4c7s8JeL8q1c8q1c8q1c8q1c8q1c8q1c8q1c8', NOW(), NOW()),
('Charlie Brown', 'charlie.brown@example.com', '09456789012', '654 Maple Dr', 'active', 'tenant', '$2y$12$GQjLPpfB1dJw1bJ4c7s8JeL8q1c8q1c8q1c8q1c8q1c8q1c8q1c8', NOW(), NOW());

INSERT INTO tenants (user_id, tenant_code, business_name, business_type, tin, business_address, contact_person, contact_number, status, created_at, updated_at) VALUES
(2, 'TEN-2026-0001', 'John Doe Trading', 'Retail', '123456789', '123 Main St, City', 'John Doe', '09123456789', 'active', NOW(), NOW()),
(3, 'TEN-2026-0002', 'Jane Smith Enterprises', 'Wholesale', '987654321', '456 Oak Ave, City', 'Jane Smith', '09987654321', 'active', NOW(), NOW()),
(4, 'TEN-2026-0003', 'Bob Wilson Services', 'Services', '456789123', '789 Pine Rd, City', 'Bob Wilson', '09234567890', 'active', NOW(), NOW()),
(5, 'TEN-2026-0004', 'Alice Johnson LLC', 'Manufacturing', '789123456', '321 Elm St, City', 'Alice Johnson', '09345678901', 'active', NOW(), NOW()),
(6, 'TEN-2026-0005', 'Charlie Brown Holdings', 'Distribution', '321456789', '654 Maple Dr, City', 'Charlie Brown', '09456789012', 'active', NOW(), NOW());

SELECT 'Test tenants restored!' as status;
SELECT COUNT(*) as users FROM users;
SELECT COUNT(*) as tenants FROM tenants;
SELECT COUNT(*) as contracts FROM contracts;
