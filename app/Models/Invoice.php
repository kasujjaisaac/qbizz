<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_SETTLED = 'settled';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'business_profile_id',
        'invoice_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'issue_date',
        'due_date',
        'notes',
        'status',
        'total_amount',
        'paid_amount',
        'settled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'settled_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class)->latest('payment_date')->latest('id');
    }

    public function latestReceipt(): HasOne
    {
        return $this->hasOne(Receipt::class)->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_PARTIAL]);
    }

    public function scopeSettled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SETTLED);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_SETTLED;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PARTIAL], true);
    }

    public function amountPaid(): float
    {
        return round((float) $this->paid_amount, 2);
    }

    public function balanceDue(): float
    {
        return max(round((float) $this->total_amount - $this->amountPaid(), 2), 0);
    }

    public function hasPayments(): bool
    {
        if ($this->relationLoaded('receipts')) {
            return $this->receipts->isNotEmpty();
        }

        if (array_key_exists('receipts_count', $this->attributes)) {
            return (int) $this->attributes['receipts_count'] > 0;
        }

        return $this->amountPaid() > 0;
    }
}
