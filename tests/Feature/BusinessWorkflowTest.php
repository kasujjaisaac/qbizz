<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_a_completed_business_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('business-profile.edit'));
    }

    public function test_user_can_complete_the_business_profile_setup(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('business-profile.update'), [
            'business_name' => 'Butende Supplies',
            'contact_email' => 'hello@butende.com',
            'phone' => '+256700000000',
            'address_line_1' => 'Plot 12 Market Street',
            'city' => 'Kampala',
            'state' => 'Central',
            'postal_code' => '256',
            'country' => 'Uganda',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('business_profiles', [
            'user_id' => $user->id,
            'business_name' => 'Butende Supplies',
            'country' => 'Uganda',
        ]);
    }

    public function test_user_can_create_and_settle_an_invoice_with_a_receipt(): void
    {
        $user = User::factory()->create();

        BusinessProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Butende Supplies',
            'contact_email' => 'hello@butende.com',
            'phone' => '+256700000000',
            'address_line_1' => 'Plot 12 Market Street',
            'city' => 'Kampala',
            'state' => 'Central',
            'postal_code' => '256',
            'country' => 'Uganda',
            'logo_path' => 'business-logos/logo.png',
            'setup_completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'invoice_number' => 'INV-2026-0001',
            'customer_name' => 'Acme Client',
            'customer_email' => 'client@example.com',
            'customer_phone' => '+256711111111',
            'customer_address' => 'Kampala Industrial Area',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'notes' => 'Thank you for your business.',
            'items' => [
                [
                    'description' => 'Consulting package',
                    'quantity' => 2,
                    'unit_price' => 150,
                ],
            ],
        ]);

        $invoice = Invoice::firstOrFail();

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => Invoice::STATUS_ACTIVE,
            'total_amount' => 300,
        ]);

        $printResponse = $this->actingAs($user)->get(route('invoices.print', $invoice));

        $printResponse->assertOk();
        $printResponse->assertSee('INV-2026-0001');

        $receiptResponse = $this->actingAs($user)->post(route('receipts.store'), [
            'receipt_source' => 'invoice',
            'receipt_number' => 'RCP-2026-0001',
            'invoice_id' => $invoice->id,
            'amount_received' => 300,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'Cash',
        ]);

        $receiptResponse->assertRedirect();

        $invoice->refresh();

        $this->assertSame(Invoice::STATUS_SETTLED, $invoice->status);
        $this->assertSame(300.0, $invoice->amountPaid());
        $this->assertSame(0.0, $invoice->balanceDue());
        $this->assertNotNull($invoice->settled_at);
    }
}
