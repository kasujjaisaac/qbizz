<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'business_profile_id',
        'invoice_id',
        'receipt_number',
        'payer_name',
        'payer_email',
        'payer_phone',
        'payer_address',
        'amount_received',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'invoice_total_snapshot',
        'balance_before',
        'balance_after',
        'issued_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_received' => 'decimal:2',
            'invoice_total_snapshot' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'payment_date' => 'date',
            'issued_at' => 'datetime',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isLinkedToInvoice(): bool
    {
        return $this->invoice_id !== null;
    }
}
