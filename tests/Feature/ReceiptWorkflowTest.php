<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_standalone_receipt(): void
    {
        $user = $this->createReadyUser();

        $response = $this->actingAs($user)->post(route('receipts.store'), [
            'receipt_source' => 'standalone',
            'receipt_number' => 'RCP-2026-0001',
            'payer_name' => 'Walk-in Customer',
            'payer_email' => 'walkin@example.com',
            'amount_received' => 75000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('receipts', [
            'user_id' => $user->id,
            'receipt_number' => 'RCP-2026-0001',
            'invoice_id' => null,
            'payer_name' => 'Walk-in Customer',
        ]);
    }

    public function test_invoice_receipt_can_record_a_partial_payment(): void
    {
        $user = $this->createReadyUser();
        $invoice = $this->createInvoiceFor($user);

        $response = $this->actingAs($user)->post(route('receipts.store'), [
            'receipt_source' => 'invoice',
            'receipt_number' => 'RCP-2026-0001',
            'invoice_id' => $invoice->id,
            'amount_received' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank transfer',
        ]);

        $response->assertRedirect();

        $invoice->refresh();
        $receipt = $invoice->receipts()->firstOrFail();

        $this->assertSame(Invoice::STATUS_PARTIAL, $invoice->status);
        $this->assertSame(100.0, $invoice->amountPaid());
        $this->assertSame(200.0, $invoice->balanceDue());
        $this->assertSame(300.0, (float) $receipt->balance_before);
        $this->assertSame(200.0, (float) $receipt->balance_after);
    }

    public function test_second_receipt_can_settle_a_partially_paid_invoice(): void
    {
        $user = $this->createReadyUser();
        $invoice = $this->createInvoiceFor($user);

        $this->actingAs($user)->post(route('receipts.store'), [
            'receipt_source' => 'invoice',
            'receipt_number' => 'RCP-2026-0001',
            'invoice_id' => $invoice->id,
            'amount_received' => 100,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Bank transfer',
        ]);

        $response = $this->actingAs($user)->post(route('receipts.store'), [
            'receipt_source' => 'invoice',
            'receipt_number' => 'RCP-2026-0002',
            'invoice_id' => $invoice->id,
            'amount_received' => 200,
            'payment_date' => now()->addDay()->toDateString(),
            'payment_method' => 'Cash',
        ]);

        $response->assertRedirect();

        $invoice->refresh();
        $latestReceipt = $invoice->receipts()->firstOrFail();

        $this->assertSame(Invoice::STATUS_SETTLED, $invoice->status);
        $this->assertSame(300.0, $invoice->amountPaid());
        $this->assertSame(0.0, $invoice->balanceDue());
        $this->assertNotNull($invoice->settled_at);
        $this->assertSame(2, $invoice->receipts()->count());
        $this->assertSame(0.0, (float) $latestReceipt->balance_after);
    }

    protected function createReadyUser(): User
    {
        $user = User::factory()->create();

        BusinessProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Developers Roots Foundation',
            'contact_email' => 'hello@example.com',
            'phone' => '+256700000000',
            'address_line_1' => 'Plot 12 Market Street',
            'city' => 'Kampala',
            'state' => 'Central',
            'postal_code' => '256',
            'country' => 'Uganda',
            'logo_path' => 'business-logos/logo.png',
            'setup_completed_at' => now(),
        ]);

        return $user;
    }

    protected function createInvoiceFor(User $user): Invoice
    {
        return $user->invoices()->create([
            'business_profile_id' => $user->businessProfile->id,
            'invoice_number' => 'INV-2026-0001',
            'customer_name' => 'Acme Client',
            'customer_email' => 'client@example.com',
            'customer_phone' => '+256711111111',
            'customer_address' => 'Kampala Industrial Area',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Thank you for your business.',
            'status' => Invoice::STATUS_ACTIVE,
            'total_amount' => 300,
            'paid_amount' => 0,
        ]);
    }
}
