<?php

namespace App\Http\Requests;

use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function usesAutomaticQuotationNumber(): bool
    {
        return $this->route('quotation') === null && $this->input('quotation_number_mode') === 'auto';
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function ($item): array {
                return [
                    'description' => trim((string) data_get($item, 'description', '')),
                    'quantity' => data_get($item, 'quantity'),
                    'unit_price' => data_get($item, 'unit_price'),
                ];
            })
            ->filter(fn (array $item): bool => $item['description'] !== '' || filled($item['quantity']) || filled($item['unit_price']))
            ->values()
            ->all();

        $this->merge([
            'valid_until' => $this->input('valid_until') ?: null,
            'customer_email' => $this->input('customer_email') ?: null,
            'customer_phone' => $this->input('customer_phone') ?: null,
            'customer_address' => $this->input('customer_address') ?: null,
            'notes' => $this->input('notes') ?: null,
            'items' => $items,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Quotation|null $quotation */
        $quotation = $this->route('quotation');
        $businessProfileId = $this->user()?->business_profile_id;
        $quotationNumberRules = ['required', 'string', 'max:50'];

        if (! $this->usesAutomaticQuotationNumber()) {
            $quotationNumberRules[] = Rule::unique('quotations', 'quotation_number')
                ->where(fn ($query) => $query->where('business_profile_id', $businessProfileId))
                ->ignore($quotation?->id);
        }

        return [
            'quotation_number_mode' => ['sometimes', Rule::in(['auto', 'manual'])],
            'quotation_number' => $quotationNumberRules,
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:1000'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
        ];
    }
}
