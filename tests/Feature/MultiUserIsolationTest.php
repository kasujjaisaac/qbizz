<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiUserIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_different_users_can_reuse_the_same_document_numbers(): void
    {
        $firstUser = $this->createReadyUser('Alpha Supply');
        $secondUser = $this->createReadyUser('Bravo Supply');

        $this->actingAs($firstUser)->post(route('invoices.store'), [
            'invoice_number' => 'INV-2026-0001',
            'customer_name' => 'Acme Client',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Consulting package',
                    'quantity' => 2,
                    'unit_price' => 150,
                ],
            ],
        ])->assertRedirect();

        $this->actingAs($secondUser)->post(route('invoices.store'), [
            'invoice_number' => 'INV-2026-0001',
            'customer_name' => 'Beta Client',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Design package',
                    'quantity' => 1,
                    'unit_price' => 200,
                ],
            ],
        ])->assertRedirect();

        $this->actingAs($firstUser)->post(route('quotations.store'), [
            'quotation_number' => 'QT-2026-0001',
            'customer_name' => 'Acme Client',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Consulting package',
                    'quantity' => 2,
                    'unit_price' => 150,
                ],
            ],
        ])->assertRedirect();

        $this->actingAs($secondUser)->post(route('quotations.store'), [
            'quotation_number' => 'QT-2026-0001',
            'customer_name' => 'Beta Client',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Design package',
                    'quantity' => 1,
                    'unit_price' => 200,
                ],
            ],
        ])->assertRedirect();

        $this->actingAs($firstUser)->post(route('receipts.store'), [
            'receipt_source' => 'standalone',
            'receipt_number' => 'RCP-2026-0001',
            'payer_name' => 'Walk-in Customer',
            'amount_received' => 75000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
        ])->assertRedirect();

        $this->actingAs($secondUser)->post(route('receipts.store'), [
            'receipt_source' => 'standalone',
            'receipt_number' => 'RCP-2026-0001',
            'payer_name' => 'Walk-in Customer',
            'amount_received' => 65000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
        ])->assertRedirect();

        $this->assertSame(2, Invoice::where('invoice_number', 'INV-2026-0001')->count());
        $this->assertSame(2, Quotation::where('quotation_number', 'QT-2026-0001')->count());
        $this->assertSame(2, Receipt::where('receipt_number', 'RCP-2026-0001')->count());
    }

    public function test_users_only_see_their_own_documents_in_lists_and_direct_links(): void
    {
        $owner = $this->createReadyUser('Owner Supply');
        $viewer = $this->createReadyUser('Viewer Supply');
        $invoice = $this->createInvoiceFor($owner, 'INV-2026-0099');
        $quotation = $this->createQuotationFor($owner, 'QT-2026-0099');
        $receipt = $this->createStandaloneReceiptFor($owner, 'RCP-2026-0099');

        $this->actingAs($viewer)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertDontSee('INV-2026-0099');

        $this->actingAs($viewer)
            ->get(route('quotations.index'))
            ->assertOk()
            ->assertDontSee('QT-2026-0099');

        $this->actingAs($viewer)
            ->get(route('receipts.index'))
            ->assertOk()
            ->assertDontSee('RCP-2026-0099');

        $this->actingAs($viewer)
            ->get(route('invoices.show', $invoice))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('quotations.show', $quotation))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('receipts.show', $receipt))
            ->assertForbidden();
    }

    public function test_teammates_can_share_company_documents_and_numbering(): void
    {
        $owner = $this->createReadyUser('Shared Supply');
        $teammate = User::factory()->create([
            'business_profile_id' => $owner->businessProfile->id,
        ]);

        $invoice = $this->createInvoiceFor($owner, 'INV-2026-0001');
        $quotation = $this->createQuotationFor($owner, 'QT-2026-0001');
        $receipt = $this->createStandaloneReceiptFor($owner, 'RCP-2026-0001');

        $this->actingAs($teammate)
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('INV-2026-0001');

        $this->actingAs($teammate)
            ->get(route('quotations.index'))
            ->assertOk()
            ->assertSee('QT-2026-0001');

        $this->actingAs($teammate)
            ->get(route('receipts.index'))
            ->assertOk()
            ->assertSee('RCP-2026-0001');

        $this->actingAs($teammate)->get(route('invoices.show', $invoice))->assertOk();
        $this->actingAs($teammate)->get(route('quotations.show', $quotation))->assertOk();
        $this->actingAs($teammate)->get(route('receipts.show', $receipt))->assertOk();

        $this->actingAs($teammate)->post(route('invoices.store'), [
            'invoice_number_mode' => 'auto',
            'invoice_number' => 'INV-2026-0001',
            'customer_name' => 'Team Client',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'description' => 'Follow-up work',
                    'quantity' => 1,
                    'unit_price' => 250,
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'user_id' => $teammate->id,
            'business_profile_id' => $owner->businessProfile->id,
            'invoice_number' => 'INV-2026-0002',
        ]);
    }

    protected function createReadyUser(string $businessName): User
    {
        $user = User::factory()->create();

        $businessProfile = BusinessProfile::create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'contact_email' => strtolower(str_replace(' ', '', $businessName)).'@example.com',
            'phone' => '+256700000000',
            'address_line_1' => 'Plot 12 Market Street',
            'city' => 'Kampala',
            'state' => 'Central',
            'postal_code' => '256',
            'country' => 'Uganda',
            'logo_path' => 'business-logos/logo.png',
            'setup_completed_at' => now(),
        ]);

        $user->forceFill([
            'business_profile_id' => $businessProfile->id,
        ])->save();

        return $user;
    }

    protected function createInvoiceFor(User $user, string $invoiceNumber): Invoice
    {
        return Invoice::create([
            'user_id' => $user->id,
            'business_profile_id' => $user->businessProfile->id,
            'invoice_number' => $invoiceNumber,
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

    protected function createQuotationFor(User $user, string $quotationNumber): Quotation
    {
        $quotation = Quotation::create([
            'user_id' => $user->id,
            'business_profile_id' => $user->businessProfile->id,
            'quotation_number' => $quotationNumber,
            'customer_name' => 'Acme Client',
            'customer_email' => 'client@example.com',
            'customer_phone' => '+256711111111',
            'customer_address' => 'Kampala Industrial Area',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'notes' => 'Pricing valid for two weeks.',
            'status' => Quotation::STATUS_DRAFT,
            'total_amount' => 300,
        ]);

        $quotation->items()->create([
            'description' => 'Consulting package',
            'quantity' => 2,
            'unit_price' => 150,
            'line_total' => 300,
            'position' => 1,
        ]);

        return $quotation;
    }

    protected function createStandaloneReceiptFor(User $user, string $receiptNumber): Receipt
    {
        return Receipt::create([
            'user_id' => $user->id,
            'business_profile_id' => $user->businessProfile->id,
            'receipt_number' => $receiptNumber,
            'payer_name' => 'Walk-in Customer',
            'amount_received' => 75000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
            'issued_at' => now(),
        ]);
    }
}
