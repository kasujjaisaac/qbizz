<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfile extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'contact_email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'website',
        'tax_id',
        'issuer_title',
        'logo_path',
        'signature_path',
        'setup_completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'setup_completed_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function requiredFieldLabels(): array
    {
        return [
            'logo_path' => 'Business logo',
            'business_name' => 'Business name',
            'contact_email' => 'Business email',
            'phone' => 'Telephone',
            'address_line_1' => 'Address',
            'city' => 'City',
            'state' => 'State / Region',
            'postal_code' => 'Postal code',
            'country' => 'Country',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function completionPercentage(): int
    {
        $requiredFields = array_keys(static::requiredFieldLabels());

        $completedFields = collect($requiredFields)
            ->filter(fn (string $field): bool => filled($this->{$field}))
            ->count();

        return (int) round(($completedFields / count($requiredFields)) * 100);
    }

    public function isComplete(): bool
    {
        return $this->completionPercentage() === 100;
    }

    /**
     * @return list<string>
     */
    public function missingFields(): array
    {
        return collect(static::requiredFieldLabels())
            ->filter(fn (string $label, string $field): bool => blank($this->{$field}))
            ->values()
            ->all();
    }

    public function formattedAddress(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            collect([$this->city, $this->state, $this->postal_code])->filter()->implode(', '),
            $this->country,
        ])->filter()->implode("\n");
    }
}
