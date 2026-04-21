<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'business_profile_id',
        'is_admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function ownedBusinessProfile(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'business_profile_id', 'business_profile_id');
    }

    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'business_profile_id', 'business_profile_id');
    }

    public function createdQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'business_profile_id', 'business_profile_id');
    }

    public function createdReceipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }
}
