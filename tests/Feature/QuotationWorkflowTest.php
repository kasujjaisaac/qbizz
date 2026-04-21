<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_quotation(): void
    {
        $user = $this->createReadyUser();

        $response = $this->actingAs($user)->post(route('quotations.store'), [
            'quotation_number' => 'QT-2026-0001',
            'customer_name' => 'Acme Client',
            'customer_email' => 'client@example.com',
            'customer_phone' => '+256711111111',
            'customer_address' => 'Kampala Industrial Area',
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'notes' => 'Pricing valid for two weeks.',
            'items' => [
                [
                    'description' => 'Consulting package',
                    'quantity' => 2,
                    'unit_price' => 150,
                ],
            ],
        ]);

        $quotation = Quotation::firstOrFail();

        $response->assertRedirect(route('quotations.show', $quotation));
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => Quotation::STATUS_DRAFT,
            'total_amount' => 300,
        ]);
        $this->assertDatabaseCount('quotation_items', 1);
    }

    public function test_accepted_quotation_can_be_converted_to_an_invoice(): void
    {
        $user = $this->createReadyUser();
        $quotation = $this->createQuotationFor($user);

        $this->actingAs($user)->patch(route('quotations.accept', $quotation));

        $response = $this->actingAs($user)->post(route('quotations.convert', $quotation));

        $invoice = Invoice::firstOrFail();
        $quotation->refresh();

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertSame(Quotation::STATUS_CONVERTED, $quotation->status);
        $this->assertSame($invoice->id, $quotation->converted_invoice_id);
        $this->assertNotNull($quotation->converted_at);
        $this->assertSame('Acme Client', $invoice->customer_name);
        $this->assertSame(300.0, (float) $invoice->total_amount);
        $this->assertSame(1, $invoice->items()->count());
    }

    public function test_rejected_quotation_cannot_be_converted_to_an_invoice(): void
    {
        $user = $this->createReadyUser();
        $quotation = $this->createQuotationFor($user);

        $this->actingAs($user)->patch(route('quotations.reject', $quotation));

        $response = $this->actingAs($user)->post(route('quotations.convert', $quotation));

        $quotation->refresh();

        $response->assertRedirect(route('quotations.show', $quotation));
        $this->assertSame(Quotation::STATUS_REJECTED, $quotation->status);
        $this->assertSame(0, Invoice::count());
        $this->assertNull($quotation->converted_invoice_id);
    }

    public function test_stale_quotation_create_forms_get_the_next_available_number_in_auto_mode(): void
    {
        $user = $this->createReadyUser();
        $payload = [
            'quotation_number_mode' => 'auto',
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
        ];

        $this->actingAs($user)->post(route('quotations.store'), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('quotations.store'), $payload)->assertRedirect();

        $this->assertDatabaseHas('quotations', [
            'user_id' => $user->id,
            'quotation_number' => 'QT-2026-0001',
        ]);
        $this->assertDatabaseHas('quotations', [
            'user_id' => $user->id,
            'quotation_number' => 'QT-2026-0002',
        ]);
    }

    protected function createReadyUser(): User
    {
        $user = User::factory()->create();

        $businessProfile = BusinessProfile::create([
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

        $user->forceFill([
            'business_profile_id' => $businessProfile->id,
        ])->save();

        return $user;
    }

    protected function createQuotationFor(User $user): Quotation
    {
        $quotation = Quotation::create([
            'user_id' => $user->id,
            'business_profile_id' => $user->businessProfile->id,
            'quotation_number' => 'QT-2026-0001',
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
}
