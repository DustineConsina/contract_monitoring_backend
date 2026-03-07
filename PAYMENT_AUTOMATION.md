# Payment & Demand Letter Automation System

This document explains the automated payment and demand letter system integrated into the contract management system.

## Features

### 1. **Automated Payment Tracking**
- Monthly payments are automatically generated for active contracts with built-in 3% interest
- Overdue status is automatically assigned when due date passes
- Balances from unpaid months carry forward to next billing period

### 2. **3% Interest as Built-in Monthly Cost**
- Every monthly payment includes a **fixed 3% interest component**
- This is NOT a penalty - it's part of the regular billing
- Example: Monthly rent 500 → Interest 3% (15) → **Total due: 515**
- The 3% is automatically calculated and included when payment is generated
- Interest is not conditional on late payment - it's in every payment

### 3. **Automatic Demand Letter Generation**
- System automatically generates demand letters **immediately when payment becomes overdue**
- No grace period - as soon as due date passes, demand letter is issued
- Example: If payment is due on January 15, it becomes overdue on January 16, and demand letter is generated on January 16
- Demand letters are issued with a 5-day settlement period from issue date
- Supports email notifications to tenants
- Tracks demand letter status: issued → sent → paid/cancelled

## Timeline Example

```
Monthly Rent: 500
Interest (3%): 15
Monthly Billing: 515

**Contract starts on January 15:**

January 15 - February 14 - Billing period (Month 1)
February 15 - Due date (one month from contract start)
February 16-20 - 5-day grace period (no action)
February 20 - Payment becomes "overdue" (5 days after due date)
February 20 - Demand letter automatically generated
February 20 - Email sent to tenant with demand letter
February 25 - Demand letter settlement deadline (5 days from issue)

February 15 - March 14 - Billing period (Month 2)
March 15 - Due date (follows contract anniversary)
March 20 - Becomes overdue (5 days after due date)
```

## Scheduled Commands

Two scheduler commands run automatically:

### Command 1: Update Overdue Status
**Schedule:** Daily at 7:00 AM  
**File:** `app/Console/Commands/UpdateOverdueStatus.php`  
**Function:** Updates payment status from "pending" to "overdue" when **5 days after the due date** has passed

```bash
php artisan payments:update-overdue-status
```

**Grace Period:** 5 days after the due date before marking as overdue  
**Example:** If due date is January 31, overdue status triggers on February 5

### Command 2: Generate Demand Letters
**Schedule:** Daily at 8:00 AM  
**File:** `app/Console/Commands/GenerateDemandLetters.php`  
**Function:** Generates and sends demand letters for all payments marked as overdue

```bash
php artisan payments:generate-demand-letters
```

To enable scheduled tasks, ensure Laravel Scheduler is configured in your cron:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

### Payment Management

#### 1. Get Contract Payment Summary
```
GET /api/contracts/{contractId}/payment-summary
```

Returns comprehensive payment summary including:
- Base monthly rent amount
- 3% interest amount per month
- Total monthly billing (rent + interest)
- Lease period information
- Payment statistics (paid, pending, overdue)
- Total interest collected
- Total outstanding balance
- Number of demand letters issued

**Response:**
```json
{
  "success": true,
  "data": {
    "contract_number": "CON-2024-001",
    "tenant_name": "ABC Trading",
    "space_name": "Space A1",
    "base_monthly_rent": 500,
    "interest_percentage": 3,
    "interest_amount_per_month": 15,
    "monthly_billing_amount": 515,
    "billing_description": "Monthly rent includes 3% built-in interest",
    "lease_period": "Jan 01, 2024 - Dec 31, 2024",
    "total_payments": 12,
    "paid_payments": 10,
    "pending_payments": 1,
    "overdue_payments": 1,
    "total_billed": 6180,
    "total_interest_collected": 180,
    "total_paid": 5150,
    "total_outstanding": 1030,
    "demand_letters_issued": 1
  }
}
```

#### 2. Get Demand Letters for Contract
```
GET /api/contracts/{contractId}/demand-letters
```

Returns all demand letters issued for a contract.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "demand_number": "DL-20240223-1-0001",
      "issued_date": "Feb 23, 2024",
      "due_date": "Feb 28, 2024",
      "outstanding_balance": 10000,
      "total_amount_demanded": 10000,
      "status": "sent",
      "sent_date": "Feb 23, 2024 08:15",
      "days_remaining": 5
    }
  ]
}
```

#### 3. Record Payment
```
POST /api/payments/{paymentId}/record
```

Record a payment made by tenant.

**Request Body:**
```json
{
  "amount_paid": 5000,
  "payment_method": "bank_transfer",
  "reference_number": "REF-20240223-001",
  "remarks": "Partial payment"
}
```

## Database Schema

### payments table
- `id` - Primary key
- `payment_number` - Unique identifier
- `contract_id` - Foreign key to contracts
- `tenant_id` - Foreign key to tenants
- `billing_period_start` - Period start date
- `billing_period_end` - Period end date
- `due_date` - Payment due date
- `amount_due` - Base amount due
- `interest_amount` - Interest on overdue
- `total_amount` - Total including interest
- `amount_paid` - Amount paid by tenant
- `balance` - Outstanding balance
- `payment_date` - Date payment was made
- `payment_method` - Method of payment
- `reference_number` - Payment reference
- `status` - pending/partial/paid/overdue

### demand_letters table
- `id` - Primary key
- `demand_number` - Unique identifier (format: DL-YYYYMMDD-paymentid-sequence)
- `contract_id` - Foreign key to contracts
- `tenant_id` - Foreign key to tenants
- `payment_id` - Foreign key to payments
- `outstanding_balance` - Amount demanded
- `total_amount_demanded` - Total with fees if any
- `issued_date` - Date letter was created
- `due_date` - Settlement deadline (5 days from issue)
- `status` - issued/sent/paid/cancelled
- `sent_date` - Date email was sent
- `email_sent_to` - Tenant email address
- `remarks` - Additional notes

## Services

### PaymentService
Located in `app/Services/PaymentService.php`

**Methods:**

1. **generateMonthlyPayment($contract, $billingDate)**
   - Creates monthly payment with base rent + 3% interest included
   - Carries forward previous month's outstanding balance
   - Sets due date to first day of next month
   - Interest is calculated as: base_rent × 0.03

2. **recordPayment($payment, $amount, $method, $reference)**
   - Records payment made by tenant
   - Updates balance and status
   - No additional interest calculation (already included)

3. **getOverduePayments($contract)**
   - Returns all overdue payments for contract

4. **getTotalOutstandingBalance($contract)**
   - Sums all unpaid balances (includes interest)

5. **getPaymentSummary($contract)**
   - Returns comprehensive payment statistics

## Models

### Payment Model (`app/Models/Payment.php`)
- Relations: contract(), tenant(), demandLetters()
- Methods: isOverdue(), daysOverdue(), calculateInterest(), recordPayment()
- **Protected Field:** `due_date` - Not mass-assignable, auto-calculated based on contract start date
- **UI Protection:** Due date field is disabled in edit forms to prevent accidental modification

### DemandLetter Model (`app/Models/DemandLetter.php`)
- Relations: contract(), tenant(), payment()
- Methods: isActive(), isDueDatePassed()

### Contract Model (Updated)
- Added relation: demandLetters()

## Usage Examples

### Generate Monthly Payment
```php
use App\Services\PaymentService;
use Carbon\Carbon;

$service = new PaymentService();
$payment = $service->generateMonthlyPayment($contract, Carbon::now());

// Result:
// Base rent: 500
// Interest (3%): 15
// Amount due: 515
// This happens automatically every month
```

### Record a Payment
```php
$payment = Payment::find(1);
$service->recordPayment(
    $payment,
    amount: 5000,
    method: 'bank_transfer',
    reference: 'REF-001'
);
```

### Get Contract Payment Summary
```php
$summary = $service->getPaymentSummary($contract);
echo "Total Outstanding: " . $summary['total_outstanding'];
```

### Manual Demand Letter Generation
```bash
php artisan payments:generate-demand-letters
```

## Email Configuration

To enable email notifications for demand letters, configure your mail settings in `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@pfda.gov.ph
```

Currently, emails are logged to `storage/logs/laravel.log`. Update the `sendDemandLetterEmail()` method in `GenerateDemandLetters.php` to use Laravel's Mail facade for actual sending.

## Dashboard Display

Contract details page will show:
- Base monthly rent and next month's rent amount
- Penalty surcharge indicator (if applicable)
- Payment history and status
- Outstanding balance
- Any active demand letters
- Days until next payment due

## Troubleshooting

**Demand letters not being generated?**
- Check that scheduler is running: `php artisan schedule:work`
- Verify payments exist with status='overdue'
- Ensure 5+ days have passed after the due date
- Check logs: `storage/logs/laravel.log`

**3% interest not showing on payments?**
- Confirm payment was generated by PaymentService (not manually created)
- Interest field should be populated = base_rent × 0.03
- Check payment amount_due includes interest (base_rent × 1.03)

**Due date shows wrong date?**
- Due dates are auto-calculated based on contract start date
- The due_date field in the UI is `disabled` and cannot be edited
- Due date should match contract anniversary date (e.g., if contract starts 22nd, due date is 22nd of next month)
- If data is incorrect, clear test data and regenerate payments using PaymentService

**Balance not carrying forward?**
- Ensure previous payment exists for prior contract anniversary period
- Check that payment balance > 0 when next month generated

## Key Points About 3% Interest & Due Dates

**Due Date:**
- Due date is **one month from the contract start date** (anniversary date)
- Example: If contract starts January 15, rent is due on February 15, March 15, etc.
- Billing period follows the anniversary: Jan 15 - Feb 14 = first month, Feb 15 - Mar 14 = second month

**Grace Period:**
- Tenants have **5 days after the due date** before marked overdue
- Example: If due Feb 15, overdue status triggers on Feb 20
- No penalties during grace period

**Calculation Example:**
- Base monthly rent: 500
- 3% interest: 500 × 0.03 = 15
- **Monthly bill: 515** (tenant pays this every month)
- This applies consistently, whether paid on time or late

**What Happens When Payment is Overdue:**
- Overdue status assigned on day 5 after due date
- Demand letter auto-generated same day
- Due date for settlement: 5 days from demand letter issue
- No additional interest charges (3% already included in original bill)

## Future Enhancements

1. Automatic contract renewal processing
2. Payment installment plans
3. Multiple escalation levels for demand letters (1st, 2nd, final notice)
4. SMS notifications for payments and demand letters
5. Payment reminders (7 days before due)
6. Batch demand letter PDF generation
7. Interest rate tiers (increasing with time overdue)


Two scheduler commands run automatically:

### Command 1: Update Overdue Status
**Schedule:** Daily at 7:00 AM  
**File:** `app/Console/Commands/UpdateOverdueStatus.php`  
**Function:** Updates payment status from "pending" to "overdue" when due date has passed

```bash
php artisan payments:update-overdue-status
```

### Command 2: Generate Demand Letters
**Schedule:** Daily at 8:00 AM  
**File:** `app/Console/Commands/GenerateDemandLetters.php`  
**Function:** Generates and sends demand letters for payments that are 5+ days overdue

```bash
php artisan payments:generate-demand-letters
```

To enable scheduled tasks, ensure Laravel Scheduler is configured in your cron:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

### Payment Management

#### 1. Get Contract Payment Summary
```
GET /api/contracts/{contractId}/payment-summary
```

Returns comprehensive payment summary including:
- Original and discounted monthly rent
- Lease period information
- Payment statistics (paid, pending, overdue)
- Total outstanding balance
- Number of demand letters issued

**Response:**
```json
{
  "success": true,
  "data": {
    "contract_number": "CON-2024-001",
    "tenant_name": "ABC Trading",
    "space_name": "Space A1",
    "original_monthly_rent": 10000,
    "discounted_monthly_rent": 9700,
    "discount_percentage": 3,
    "lease_period": "Jan 01, 2024 - Dec 31, 2024",
    "is_expired": false,
    "total_payments": 12,
    "paid_payments": 10,
    "pending_payments": 1,
    "overdue_payments": 1,
    "total_billed": 120000,
    "total_paid": 100000,
    "total_outstanding": 20000,
    "demand_letters_issued": 1
  }
}
```

#### 2. Get Demand Letters for Contract
```
GET /api/contracts/{contractId}/demand-letters
```

Returns all demand letters issued for a contract.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "demand_number": "DL-20240223-1-0001",
      "issued_date": "Feb 23, 2024",
      "due_date": "Mar 02, 2024",
      "outstanding_balance": 10000,
      "total_amount_demanded": 10000,
      "status": "sent",
      "sent_date": "Feb 23, 2024 08:15",
      "days_remaining": 7
    }
  ]
}
```

#### 3. Record Payment
```
POST /api/payments/{paymentId}/record
```

Record a payment made by tenant.

**Request Body:**
```json
{
  "amount_paid": 5000,
  "payment_method": "bank_transfer",
  "reference_number": "REF-20240223-001",
  "remarks": "Partial payment"
}
```

## Database Schema

### payments table
- `id` - Primary key
- `payment_number` - Unique identifier
- `contract_id` - Foreign key to contracts
- `tenant_id` - Foreign key to tenants
- `billing_period_start` - Period start date
- `billing_period_end` - Period end date
- `due_date` - Payment due date
- `amount_due` - Base amount due
- `interest_amount` - Interest on overdue
- `total_amount` - Total including interest
- `amount_paid` - Amount paid by tenant
- `balance` - Outstanding balance
- `payment_date` - Date payment was made
- `payment_method` - Method of payment
- `reference_number` - Payment reference
- `status` - pending/partial/paid/overdue

### demand_letters table
- `id` - Primary key
- `demand_number` - Unique identifier (format: DL-YYYYMMDD-paymentid-sequence)
- `contract_id` - Foreign key to contracts
- `tenant_id` - Foreign key to tenants
- `payment_id` - Foreign key to payments
- `outstanding_balance` - Amount demanded
- `total_amount_demanded` - Total with fees if any
- `issued_date` - Date letter was created
- `due_date` - Settlement deadline
- `status` - issued/sent/paid/cancelled
- `sent_date` - Date email was sent
- `email_sent_to` - Tenant email address
- `remarks` - Additional notes

## Services

### PaymentService
Located in `app/Services/PaymentService.php`

**Methods:**

1. **generateMonthlyPayment($contract, $billingDate)**
   - Creates monthly payment record
   - Carries forward previous balance
   - Applies 3% discount if after contract expiry

2. **calculateRentAmount($contract, $billingDate)**
   - Returns base rent or discounted amount
   - Checks if contract is expired for discount eligibility

3. **recordPayment($payment, $amount, $method, $reference)**
   - Records payment made by tenant
   - Updates balance and status

4. **getOverduePayments($contract)**
   - Returns all overdue payments for contract

5. **getTotalOutstandingBalance($contract)**
   - Sums all unpaid balances

6. **getPaymentSummary($contract)**
   - Returns comprehensive payment statistics

## Models

### Payment Model (`app/Models/Payment.php`)
- Relations: contract(), tenant(), demandLetters()
- Methods: isOverdue(), daysOverdue(), calculateInterest(), recordPayment()

### DemandLetter Model (`app/Models/DemandLetter.php`)
- Relations: contract(), tenant(), payment()
- Methods: isActive(), isDueDatePassed()

### Contract Model (Updated)
- Added relation: demandLetters()

## Usage Examples

### Generate Monthly Payment
```php
use App\Services\PaymentService;
use Carbon\Carbon;

$service = new PaymentService();
$payment = $service->generateMonthlyPayment($contract, Carbon::now());
```

### Record a Payment
```php
$payment = Payment::find(1);
$service->recordPayment(
    $payment,
    amount: 5000,
    method: 'bank_transfer',
    reference: 'REF-001'
);
```

### Get Contract Payment Summary
```php
$summary = $service->getPaymentSummary($contract);
echo "Total Outstanding: " . $summary['total_outstanding'];
```

### Manual Demand Letter Generation
```bash
php artisan payments:generate-demand-letters
```

## Email Configuration

To enable email notifications for demand letters, configure your mail settings in `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@pfda.gov.ph
```

Currently, emails are logged to `storage/logs/laravel.log`. Update the `sendDemandLetterEmail()` method in `GenerateDemandLetters.php` to use Laravel's Mail facade for actual sending.

## Dashboard Display

Contract details page will show:
- Current rent amount with discount indicator
- Payment history and status
- Outstanding balance
- Any active demand letters
- Days until next payment due

## Troubleshooting

**Demand letters not being generated?**
- Check that scheduler is running: `php artisan schedule:work`
- Verify payments exist with status='overdue'
- Ensure 5+ days have passed after the due date
- Check logs: `storage/logs/laravel.log`

**3% interest not showing on payments?**
- Confirm payment was generated by PaymentService (not manually created)
- Interest field should be populated = base_rent × 0.03
- Check payment amount_due includes interest (base_rent × 1.03)

**Due dates not at month-end?**
- Confirm due_date is set to endOfMonth() in PaymentService
- Check: due_date should be 30th/31st (last day of month)

**Balance not carrying forward?**
- Ensure previous payment exists for prior month
- Check that payment balance > 0 when next month generated

## Future Enhancements

1. Automatic contract renewal processing
2. Payment installment plans
3. Multiple escalation levels for demand letters (1st, 2nd, final notice)
4. SMS notifications for payments and demand letters
5. Payment reminders (7 days before due)
6. Batch demand letter PDF generation
7. Interest rate tiers (increasing with time overdue)
