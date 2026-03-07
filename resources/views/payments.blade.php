<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-8">Payment Management</h1>

        <!-- Record Payment Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Record Payment</h2>
            <form id="recordPaymentForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Contract</label>
                        <select id="contractSelect" class="w-full border rounded px-3 py-2">
                            <option value="">-- Select a contract --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Payment</label>
                        <select id="paymentSelect" class="w-full border rounded px-3 py-2">
                            <option value="">-- Select a payment --</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Due Date (Auto-filled from Contract)</label>
                        <input type="text" id="dueDateDisplay" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-300 text-gray-700 cursor-not-allowed" disabled readonly placeholder="Select payment to auto-fill">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Method</label>
                        <select id="paymentMethod" class="w-full border rounded px-3 py-2">
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Amount (Auto-filled from Contract)</label>
                        <input type="number" id="paymentAmount" step="0.01" class="w-full border rounded px-3 py-2 bg-gray-200 cursor-not-allowed" readonly placeholder="0.00">
                    </div>
                    <div></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reference Number</label>
                    <input type="text" id="referenceNumber" class="w-full border rounded px-3 py-2" placeholder="e.g., Check #123">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Remarks</label>
                    <textarea id="remarks" class="w-full border rounded px-3 py-2" rows="2"></textarea>
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Record Payment
                </button>
            </form>
        </div>

        <!-- Payments List -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Payments</h2>
            
            <!-- Filters -->
            <div class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
                <h3 class="font-semibold mb-3">Filter Payments</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Month</label>
                        <input type="month" id="filterMonth" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select id="filterStatus" class="w-full border rounded px-3 py-2">
                            <option value="">-- All Status --</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">&nbsp;</label>
                        <button onclick="applyFilters()" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <div id="paymentsList" class="space-y-4">
                <p class="text-gray-500">Loading payments...</p>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
                <h3 class="text-2xl font-bold mb-4">Edit Payment</h3>
                
                <form id="editForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Amount Due</label>
                        <input type="number" id="editAmount" step="0.01" class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Due Date (Auto-calculated from contract)</label>
                        <input type="date" id="editDueDate" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Method</label>
                        <select id="editPaymentMethod" class="w-full border rounded px-3 py-2">
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select id="editStatus" class="w-full border rounded px-3 py-2">
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Remarks</label>
                        <textarea id="editRemarks" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700">
                            Save Changes
                        </button>
                        <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-800 rounded px-4 py-2 hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPaymentId = null;
        const API_BASE = '/api';

        // Get auth token from localStorage or session
        function getAuthToken() {
            return localStorage.getItem('auth_token');
        }

        // Load payments with optional filters
        async function loadPayments(filters = {}) {
            try {
                const token = getAuthToken();
                const response = await axios.get(`${API_BASE}/payments`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                let payments = response.data.data.data || response.data.data;

                // Apply filters
                if (filters.month) {
                    payments = payments.filter(payment => {
                        const paymentDate = new Date(payment.due_date);
                        const filterDate = new Date(filters.month + '-01');
                        return paymentDate.getFullYear() === filterDate.getFullYear() &&
                               paymentDate.getMonth() === filterDate.getMonth();
                    });
                }

                if (filters.status) {
                    payments = payments.filter(p => p.status === filters.status);
                }

                const html = payments.map(payment => `
                    <div class="border rounded p-4 flex justify-between items-center">
                        <div>
                            <p class="font-bold">${payment.payment_number}</p>
                            <p class="text-sm text-gray-600">Billing Period: ${payment.billing_period_start} to ${payment.billing_period_end}</p>
                            <p class="text-sm text-gray-600">Due Date: <strong>${payment.due_date}</strong></p>
                            <p class="text-sm text-gray-600">Amount: ₱${parseFloat(payment.amount_due).toFixed(2)}</p>
                            <p class="text-sm text-gray-600">Interest (3%): ₱${parseFloat(payment.interest_amount).toFixed(2)}</p>
                            <p class="text-sm text-gray-600" style="font-weight: bold; color: #dc2626;">Total with Interest: ₱${parseFloat(payment.total_amount).toFixed(2)}</p>
                            <p class="text-sm text-gray-600">Balance: ₱${parseFloat(payment.balance).toFixed(2)}</p>
                            <p class="text-sm"><span class="inline-block px-2 py-1 rounded text-white text-xs ${getStatusColor(payment.status)}">${payment.status.toUpperCase()}</span></p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openEditModal(${payment.id}, ${payment.amount_due}, '${payment.due_date}', '${payment.payment_method}', '${payment.status}', '${payment.remarks || ''}')" 
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Edit
                            </button>
                            <button onclick="recordPayment(${payment.id})" 
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                Record Payment
                            </button>
                        </div>
                    </div>
                `).join('');

                document.getElementById('paymentsList').innerHTML = html || '<p class="text-gray-500">No payments found</p>';
            } catch (error) {
                console.error('Error loading payments:', error);
                document.getElementById('paymentsList').innerHTML = `<p class="text-red-500">Error: ${error.message}</p>`;
            }
        }

        // Apply filters function
        function applyFilters() {
            const month = document.getElementById('filterMonth').value;
            const status = document.getElementById('filterStatus').value;

            const filters = {};
            if (month) filters.month = month;
            if (status) filters.status = status;

            loadPayments(filters);
        }

        function getStatusColor(status) {
            const colors = {
                'pending': 'bg-yellow-500',
                'paid': 'bg-green-500',
                'partial': 'bg-blue-500',
                'overdue': 'bg-red-500'
            };
            return colors[status] || 'bg-gray-500';
        }

        function openEditModal(id, amount, dueDate, method, status, remarks) {
            currentPaymentId = id;
            document.getElementById('editAmount').value = amount;
            document.getElementById('editDueDate').value = dueDate;
            document.getElementById('editPaymentMethod').value = method;
            document.getElementById('editStatus').value = status;
            document.getElementById('editRemarks').value = remarks;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
            currentPaymentId = null;
        }

        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const token = getAuthToken();
                const data = {
                    amount_due: parseFloat(document.getElementById('editAmount').value),
                    payment_method: document.getElementById('editPaymentMethod').value,
                    status: document.getElementById('editStatus').value,
                    remarks: document.getElementById('editRemarks').value
                };

                const response = await axios.put(`${API_BASE}/payments/${currentPaymentId}`, data, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                alert('Payment updated successfully!');
                closeModal();
                loadPayments();
            } catch (error) {
                console.error('Error updating payment:', error);
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        });

        async function recordPayment(id) {
            const amount = prompt('Enter payment amount:');
            if (!amount) return;

            try {
                const token = getAuthToken();
                const response = await axios.post(`${API_BASE}/payments/${id}/record`, {
                    amount: parseFloat(amount),
                    payment_method: 'cash'
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                alert('Payment recorded successfully!');
                loadPayments();
            } catch (error) {
                console.error('Error recording payment:', error);
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        }

        // Load contracts for dropdown
        async function loadContracts() {
            try {
                const token = getAuthToken();
                const response = await axios.get(`${API_BASE}/payable-contracts`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                const contracts = response.data.data;
                const contractSelect = document.getElementById('contractSelect');
                
                contractSelect.innerHTML = '<option value="">-- Select a contract --</option>';
                
                contracts.forEach(contract => {
                    const option = document.createElement('option');
                    option.value = contract.id;
                    option.textContent = contract.label;
                    option.dataset.contractId = contract.id;
                    contractSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading contracts:', error);
            }
        }

        // Load payments for selected contract
        async function loadPaymentsForContract(contractId) {
            const paymentSelect = document.getElementById('paymentSelect');
            
            if (!contractId) {
                paymentSelect.innerHTML = '<option value="">-- Select a payment --</option>';
                return;
            }

            try {
                const token = getAuthToken();
                const response = await axios.get(`${API_BASE}/payments?contract_id=${contractId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                const allPayments = response.data.data.data || response.data.data;
                // Filter to only pending, overdue, and partial payments
                const payments = allPayments.filter(p => ['pending', 'overdue', 'partial'].includes(p.status));
                
                paymentSelect.innerHTML = '<option value="">-- Select a payment --</option>';
                
                payments.forEach(payment => {
                    const option = document.createElement('option');
                    option.value = payment.id;
                    option.textContent = `${payment.payment_number} - Due: ${payment.due_date} - Balance: ₱${parseFloat(payment.balance).toFixed(2)}`;
                    option.dataset.paymentId = payment.id;
                    option.dataset.balance = payment.balance;
                    option.dataset.dueDate = payment.due_date;
                    paymentSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading payments:', error);
            }
        }

        // Contract selection handler
        document.getElementById('contractSelect').addEventListener('change', function(e) {
            loadPaymentsForContract(this.value);
        });

        // Payment selection handler - auto-fill the amount and due date
        document.getElementById('paymentSelect').addEventListener('change', function(e) {
            const selectedOption = this.options[this.selectedIndex];
            const balance = selectedOption.dataset.balance;
            const dueDate = selectedOption.dataset.dueDate;
            const amountInput = document.getElementById('paymentAmount');
            const dueDateInput = document.getElementById('dueDateDisplay');
            
            if (balance) {
                amountInput.value = parseFloat(balance).toFixed(2);
            } else {
                amountInput.value = '';
            }
            
            if (dueDate) {
                dueDateInput.value = dueDate;
            } else {
                dueDateInput.value = '';
            }
        });

        // Record payment form submission
        document.getElementById('recordPaymentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const paymentId = document.getElementById('paymentSelect').value;
            const amount = parseFloat(document.getElementById('paymentAmount').value);
            const paymentMethod = document.getElementById('paymentMethod').value;
            const referenceNumber = document.getElementById('referenceNumber').value;
            const remarks = document.getElementById('remarks').value;

            if (!paymentId) {
                alert('Please select a payment');
                return;
            }

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            try {
                const token = getAuthToken();
                const response = await axios.post(`${API_BASE}/payments/${paymentId}/record`, {
                    amount: amount,
                    payment_method: paymentMethod,
                    reference_number: referenceNumber,
                    remarks: remarks
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                alert('Payment recorded successfully!');
                // Reset form
                document.getElementById('recordPaymentForm').reset();
                loadContracts();
                loadPayments();
            } catch (error) {
                console.error('Error recording payment:', error);
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        });

        // Load contracts and payments on page load
        loadContracts();
        loadPayments();
    </script>
</body>
</html>
