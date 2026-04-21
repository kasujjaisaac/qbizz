<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function usesAutomaticReceiptNumber(): bool
    {
        return $this->route('receipt') === null && $this->input('receipt_number_mode') === 'auto';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'receipt_source' => $this->input('receipt_source', $this->filled('invoice_id') ? 'invoice' : 'standalone'),
            'invoice_id' => $this->input('invoice_id') ?: null,
            'payer_email' => $this->input('payer_email') ?: null,
            'payer_phone' => $this->input('payer_phone') ?: null,
            'payer_address' => $this->input('payer_address') ?: null,
            'payment_method' => $this->input('payment_method') ?: null,
            'reference_number' => $this->input('reference_number') ?: null,
            'notes' => $this->input('notes') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $businessProfileId = $this->user()?->business_profile_id;
        $receiptNumberRules = ['required', 'string', 'max:50'];

        if (! $this->usesAutomaticReceiptNumber()) {
            $receiptNumberRules[] = Rule::unique('receipts', 'receipt_number')
                ->where(fn ($query) => $query->where('business_profile_id', $businessProfileId));
        }

        return [
            'receipt_number_mode' => ['sometimes', Rule::in(['auto', 'manual'])],
            'receipt_source' => ['required', Rule::in(['invoice', 'standalone'])],
            'receipt_number' => $receiptNumberRules,
            'invoice_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('receipt_source') === 'invoice'),
                'integer',
                Rule::exists('invoices', 'id')->where(function ($query) {
                    $query->where('business_profile_id', $this->user()?->business_profile_id)
                        ->whereIn('status', [Invoice::STATUS_ACTIVE, Invoice::STATUS_PARTIAL]);
                }),
            ],
            'payer_name' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('receipt_source') === 'standalone'),
                'string',
                'max:255',
            ],
            'payer_email' => ['nullable', 'email', 'max:255'],
            'payer_phone' => ['nullable', 'string', 'max:50'],
            'payer_address' => ['nullable', 'string', 'max:1000'],
            'amount_received' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('receipt_source') !== 'invoice' || ! $this->filled('invoice_id') || ! $this->filled('amount_received')) {
                return;
            }

            /** @var Invoice|null $invoice */
            $invoice = $this->user()?->invoices()->open()->find($this->integer('invoice_id'));

            if (! $invoice) {
                return;
            }

            if ((float) $this->input('amount_received') > $invoice->balanceDue()) {
                $validator->errors()->add('amount_received', 'The receipt amount can not be greater than the invoice balance.');
            }
        });
    }
}
