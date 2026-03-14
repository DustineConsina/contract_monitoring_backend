<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;
use App\Models\User;
use App\Models\Payment;
use App\Models\DemandLetter;
use App\Console\Commands\UpdateExpiredContracts;
use App\Console\Commands\GenerateDemandLetters;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractRenewalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a contract status changes to "for_renewal" when 2 months before expiry
     */
    public function test_contract_status_changes_to_for_renewal_two_months_before_expiry()
    {
        // Create user, tenant, and rental space
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create(['status' => 'occupied']);

        // Create contract with end date exactly 2 months from now
        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(2),
            'start_date' => Carbon::now()->subMonths(10),
            'monthly_rental' => 5000,
        ]);

        // Verify contract is currently active
        $this->assertEquals('active', $contract->status);

        // Run the update contracts command
        $command = new UpdateExpiredContracts();
        $command->handle();

        // Refresh contract from database
        $contract->refresh();

        // Verify contract status changed to for_renewal
        $this->assertEquals('for_renewal', $contract->status);
    }

    /**
     * Test that a contract status changes to "for_renewal" when within 2 months of expiry
     */
    public function test_contract_status_changes_to_for_renewal_within_two_months()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create(['status' => 'occupied']);

        // Create contract expiring in 45 days (within 2 months, but not yet 2 months)
        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addDays(45),
            'start_date' => Carbon::now()->subMonths(11),
        ]);

        // Run command
        $command = new UpdateExpiredContracts();
        $command->handle();

        // Verify status changed to for_renewal
        $this->assertEquals('for_renewal', $contract->fresh()->status);
    }

    /**
     * Test that active contracts beyond 2 months remain active
     */
    public function test_active_contract_beyond_two_months_remains_active()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create();

        // Create contract expiring in 6 months
        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
            'start_date' => Carbon::now()->subMonths(6),
        ]);

        // Run command
        $command = new UpdateExpiredContracts();
        $command->handle();

        // Verify contract remains active
        $this->assertEquals('active', $contract->fresh()->status);
    }

    /**
     * Test that for_renewal contracts that expired now become expired
     */
    public function test_for_renewal_contract_that_expired_becomes_expired()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create(['status' => 'occupied']);

        // Create contract marked as for_renewal but already expired
        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'for_renewal',
            'end_date' => Carbon::now()->subDays(5),
            'start_date' => Carbon::now()->subMonths(12),
        ]);

        // Run command
        $command = new UpdateExpiredContracts();
        $command->handle();

        // Verify contract status changed to expired
        $this->assertEquals('expired', $contract->fresh()->status);
        
        // Verify rental space is now available
        $rentalSpace->refresh();
        $this->assertEquals('available', $rentalSpace->status);
    }

    /**
     * Test that contract isForRenewal() method works correctly
     */
    public function test_contract_is_for_renewal_method()
    {
        $contract = Contract::factory()->create([
            'status' => 'for_renewal',
            'end_date' => Carbon::now()->addMonths(1),
        ]);

        $this->assertTrue($contract->isForRenewal());
    }

    /**
     * Test that contract needsRenewal() method returns true when within 2 months
     */
    public function test_contract_needs_renewal_method()
    {
        $contract = Contract::factory()->create([
            'status' => 'active',
            'end_date' => Carbon::now()->addDays(45),
        ]);

        $this->assertTrue($contract->needsRenewal());
    }

    /**
     * Test that contract needsRenewal() method returns false when beyond 2 months
     */
    public function test_contract_needs_renewal_returns_false_beyond_two_months()
    {
        $contract = Contract::factory()->create([
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(4),
        ]);

        $this->assertFalse($contract->needsRenewal());
    }

    /**
     * Test that a contract status changes to expired after end date
     */
    public function test_contract_status_changes_to_expired_after_end_date()
    {
        // Create user, tenant, and rental space
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create(['status' => 'occupied']);

        // Create contract with end date in the past
        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->subDays(5), // Already expired
            'start_date' => Carbon::now()->subMonths(12),
            'monthly_rental' => 5000,
        ]);

        // Verify contract is currently active
        $this->assertEquals('active', $contract->status);

        // Run the update expired contracts command
        $command = new UpdateExpiredContracts();
        $command->handle();

        // Refresh contract from database
        $contract->refresh();

        // Verify contract status changed to expired
        $this->assertEquals('expired', $contract->status);
        
        // Verify rental space status changed to available
        $rentalSpace->refresh();
        $this->assertEquals('available', $rentalSpace->status);
    }

    /**
     * Test that multiple expired contracts are updated correctly
     */
    public function test_multiple_expired_contracts_are_updated()
    {
        // Create multiple users, tenants, and rental spaces
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tenant1 = Tenant::factory()->create(['user_id' => $user1->id]);
        $tenant2 = Tenant::factory()->create(['user_id' => $user2->id]);
        $space1 = RentalSpace::factory()->create(['status' => 'occupied']);
        $space2 = RentalSpace::factory()->create(['status' => 'occupied']);

        // Create multiple active contracts with expired end dates
        $contract1 = Contract::factory()->create([
            'tenant_id' => $tenant1->id,
            'rental_space_id' => $space1->id,
            'status' => 'active',
            'end_date' => Carbon::now()->subDays(10),
            'start_date' => Carbon::now()->subMonths(12),
        ]);

        $contract2 = Contract::factory()->create([
            'tenant_id' => $tenant2->id,
            'rental_space_id' => $space2->id,
            'status' => 'active',
            'end_date' => Carbon::now()->subDays(5),
            'start_date' => Carbon::now()->subMonths(12),
        ]);

        // Create one contract that should NOT be updated (still active)
        $activeContract = Contract::factory()->create([
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
            'start_date' => Carbon::now()->subMonths(6),
        ]);

        // Run command
        $command = new UpdateExpiredContracts();
        $command->handle();

        // Verify both expired contracts are now marked as expired
        $this->assertEquals('expired', $contract1->fresh()->status);
        $this->assertEquals('expired', $contract2->fresh()->status);
        
        // Verify active contract remains active
        $this->assertEquals('active', $activeContract->fresh()->status);
    }

    /**
     * Test that a contract is considered expiring soon (within 30 days)
     */
    public function test_contract_is_expiring_soon()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create();

        // Create contract expiring in 15 days
        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addDays(15),
            'start_date' => Carbon::now()->subMonths(12),
        ]);

        // Verify contract is expiring soon
        $this->assertTrue($contract->isExpiringSoon());
        $this->assertFalse($contract->isExpired());
    }

    /**
     * Test that overdue payment automatically generates demand letter
     */
    public function test_overdue_payment_generates_demand_letter()
    {
        // Create user, tenant, and contract
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create();

        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
            'start_date' => Carbon::now()->subMonths(6),
            'monthly_rental' => 5000,
            'interest_rate' => 3,
        ]);

        // Create an overdue payment
        $payment = Payment::factory()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'status' => 'overdue',
            'balance' => 1000, // Has remaining balance
            'due_date' => Carbon::now()->subDays(10),
            'amount_due' => 5000,
            'total_amount' => 5000,
            'amount_paid' => 4000,
        ]);

        // Verify no demand letter exists yet
        $this->assertFalse(
            DemandLetter::where('payment_id', $payment->id)->exists()
        );

        // Run generate demand letters command
        $command = new GenerateDemandLetters();
        $command->handle();

        // Verify demand letter was created
        $this->assertTrue(
            DemandLetter::where('payment_id', $payment->id)->exists()
        );

        // Verify demand letter details
        $demandLetter = DemandLetter::where('payment_id', $payment->id)->first();
        $this->assertEquals('issued', $demandLetter->status);
        $this->assertEquals($payment->balance, $demandLetter->outstanding_balance);
        $this->assertEquals($contract->id, $demandLetter->contract_id);
        $this->assertEquals($tenant->id, $demandLetter->tenant_id);
    }

    /**
     * Test that paid payments do not generate demand letters
     */
    public function test_paid_payment_does_not_generate_demand_letter()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create();

        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
        ]);

        // Create a paid payment (balance = 0)
        $payment = Payment::factory()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'status' => 'paid',
            'balance' => 0,
            'due_date' => Carbon::now()->subDays(10),
            'amount_due' => 5000,
            'total_amount' => 5000,
            'amount_paid' => 5000,
        ]);

        // Run generate demand letters command
        $command = new GenerateDemandLetters();
        $command->handle();

        // Verify no demand letter was created
        $this->assertFalse(
            DemandLetter::where('payment_id', $payment->id)->exists()
        );
    }

    /**
     * Test that multiple overdue payments generate multiple demand letters
     */
    public function test_multiple_overdue_payments_generate_demand_letters()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create();

        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
            'monthly_rental' => 5000,
        ]);

        // Create multiple overdue payments
        $payment1 = Payment::factory()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'status' => 'overdue',
            'balance' => 1000,
            'due_date' => Carbon::now()->subDays(10),
        ]);

        $payment2 = Payment::factory()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'status' => 'overdue',
            'balance' => 2000,
            'due_date' => Carbon::now()->subDays(20),
        ]);

        // Run command
        $command = new GenerateDemandLetters();
        $command->handle();

        // Verify both demand letters were created
        $this->assertTrue(
            DemandLetter::where('payment_id', $payment1->id)->exists()
        );
        $this->assertTrue(
            DemandLetter::where('payment_id', $payment2->id)->exists()
        );

        // Verify count
        $this->assertEquals(2, DemandLetter::count());
    }

    /**
     * Test that demand letter is not created twice for same payment
     */
    public function test_demand_letter_not_created_twice_for_same_payment()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create(['user_id' => $user->id]);
        $rentalSpace = RentalSpace::factory()->create();

        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
        ]);

        $payment = Payment::factory()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'status' => 'overdue',
            'balance' => 1000,
            'due_date' => Carbon::now()->subDays(10),
        ]);

        // Run command first time
        $command = new GenerateDemandLetters();
        $command->handle();

        $firstDemandLetterCount = DemandLetter::count();
        $this->assertEquals(1, $firstDemandLetterCount);

        // Run command again
        $command->handle();

        // Verify no duplicate was created
        $this->assertEquals(1, DemandLetter::count());
    }

    /**
     * Test demand letter data accuracy
     */
    public function test_demand_letter_contains_correct_data()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create([
            'user_id' => $user->id,
            'contact_person' => 'John Doe',
        ]);
        $rentalSpace = RentalSpace::factory()->create(['name' => 'Unit 101']);

        $contract = Contract::factory()->create([
            'tenant_id' => $tenant->id,
            'rental_space_id' => $rentalSpace->id,
            'contract_number' => 'CNT-2024-001',
            'status' => 'active',
            'end_date' => Carbon::now()->addMonths(6),
        ]);

        $payment = Payment::factory()->create([
            'contract_id' => $contract->id,
            'tenant_id' => $tenant->id,
            'status' => 'overdue',
            'balance' => 2500,
            'due_date' => Carbon::now()->subDays(10),
            'amount_due' => 5000,
            'total_amount' => 5000,
            'amount_paid' => 2500,
        ]);

        // Run command
        $command = new GenerateDemandLetters();
        $command->handle();

        $demandLetter = DemandLetter::where('payment_id', $payment->id)->first();

        // Verify all data is correct
        $this->assertNotNull($demandLetter);
        $this->assertEquals($contract->id, $demandLetter->contract_id);
        $this->assertEquals($tenant->id, $demandLetter->tenant_id);
        $this->assertEquals($payment->id, $demandLetter->payment_id);
        $this->assertEquals(2500, $demandLetter->outstanding_balance);
        $this->assertEquals(5000, $demandLetter->total_amount_demanded);
        $this->assertEquals('issued', $demandLetter->status);
        $this->assertStringContainsString('DL-', $demandLetter->demand_number);
        
        // Verify due date is 5 days from issue date
        $expectedDueDate = Carbon::now()->addDays(5)->format('Y-m-d');
        $this->assertEquals($expectedDueDate, $demandLetter->due_date->format('Y-m-d'));
    }
}
