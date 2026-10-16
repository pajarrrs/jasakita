<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * @return HasOne<PartnerProfile, $this>
     */
    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    /**
     * Profil mitra milik pengguna, dibuat bila belum ada.
     */
    public function partnerProfileOrCreate(): PartnerProfile
    {
        return $this->partnerProfile()->firstOrCreate([]);
    }

    /**
     * Request jasa yang dibuat pengguna ini sebagai pelanggan.
     *
     * @return HasMany<ServiceRequest, $this>
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    /**
     * Job yang dikerjakan pengguna ini sebagai mitra.
     *
     * @return HasManyThrough<ServiceRequest, PartnerProfile, $this>
     */
    public function assignedJobs(): HasManyThrough
    {
        return $this->hasManyThrough(
            ServiceRequest::class,
            PartnerProfile::class,
            'user_id',
            'partner_profile_id',
        );
    }

    /**
     * Ulasan yang ditulis pengguna ini sebagai pelanggan.
     *
     * @return HasMany<Review, $this>
     */
    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function isCustomer(): bool
    {
        return $this->role === Role::Customer;
    }

    public function isPartner(): bool
    {
        return $this->role === Role::Partner;
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            Role::Partner => 'partner.dashboard',
            Role::Admin => 'admin.dashboard',
            default => 'customer.dashboard',
        };
    }
}
