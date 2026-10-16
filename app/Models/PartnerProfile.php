<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Database\Factories\PartnerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'bio',
    'city',
    'years_of_experience',
    'is_available',
])]
class PartnerProfile extends Model
{
    /** @use HasFactory<PartnerProfileFactory> */
    use HasFactory;

    /**
     * Default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_available' => true,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Jasa yang ditawarkan mitra ini, lengkap dengan harga kustomnya.
     *
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'partner_services')
            ->using(PartnerService::class)
            ->withPivot(['custom_price', 'note'])
            ->withTimestamps();
    }

    /**
     * Request yang sedang/sudah dikerjakan mitra ini.
     *
     * @return HasMany<ServiceRequest, $this>
     */
    public function assignedRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'partner_profile_id');
    }

    /**
     * Penawaran yang dikirim mitra ini.
     *
     * @return HasMany<Offer, $this>
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Ulasan pelanggan untuk mitra ini, terbaru lebih dulu.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     * Pengajuan verifikasi identitas (KYC) mitra ini.
     *
     * @return HasOne<PartnerVerification, $this>
     */
    public function verification(): HasOne
    {
        return $this->hasOne(PartnerVerification::class);
    }

    /**
     * Apakah identitas mitra ini sudah terverifikasi admin.
     */
    public function isVerified(): bool
    {
        return $this->verificationStatus() === VerificationStatus::Approved;
    }

    /**
     * Status verifikasi terakhir mitra, null bila belum pernah mengajukan.
     */
    public function verificationStatus(): ?VerificationStatus
    {
        $verification = $this->relationLoaded('verification')
            ? $this->verification
            : $this->verification()->first();

        return $verification?->status;
    }

    /**
     * Rata-rata rating mitra, null bila belum ada ulasan.
     */
    public function ratingAverage(): ?float
    {
        $average = $this->reviews()->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }

    /**
     * Label rating siap tampil, mis. "4,8" atau tanda pisah bila belum ada ulasan.
     */
    public function ratingLabel(): string
    {
        $average = $this->ratingAverage();

        return $average === null
            ? '—'
            : number_format($average, 1, ',', '.');
    }

    /**
     * Harga yang dipakai mitra untuk sebuah jasa: harga kustom bila diatur, selain itu harga dasar jasa.
     */
    public function effectivePriceFor(Service $service): ?string
    {
        $offered = $this->relationLoaded('services')
                ? $this->services->firstWhere('id', $service->id)
                : $this->services()->whereKey($service->id)->first();

        return $offered?->pivot->custom_price ?? $service->base_price;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'years_of_experience' => 'integer',
            'is_available' => 'boolean',
        ];
    }
}
