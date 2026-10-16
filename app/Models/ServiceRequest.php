<?php

namespace App\Models;

use App\Enums\RequestMode;
use App\Enums\RequestStatus;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'customer_id',
    'service_id',
    'partner_profile_id',
    'mode',
    'status',
    'title',
    'description',
    'address',
    'city',
    'scheduled_at',
    'agreed_price',
    'cancellation_reason',
    'completed_at',
])]
class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<PartnerProfile, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_profile_id');
    }

    /**
     * @return HasMany<Offer, $this>
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return HasMany<RequestStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Ulasan pelanggan untuk pekerjaan ini.
     *
     * @return HasOne<Review, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Pesan chat antara pelanggan dan mitra untuk pekerjaan ini.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Apakah request ini sudah punya mitra sehingga chat bisa dibuka.
     */
    public function canBeChatted(): bool
    {
        return $this->partner_profile_id !== null;
    }

    /**
     * Apakah status saat ini masih memperbolehkan kirim pesan baru.
     */
    public function isChatOpen(): bool
    {
        return in_array($this->status, [
            RequestStatus::Assigned,
            RequestStatus::Scheduled,
            RequestStatus::InProgress,
            RequestStatus::Completed,
        ], true);
    }

    /**
     * Jumlah pesan dari lawan bicara yang belum dibaca pengguna ini.
     */
    public function unreadMessagesCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Apakah pekerjaan ini sudah diulas pelanggan.
     */
    public function isReviewed(): bool
    {
        return $this->relationLoaded('review')
            ? $this->review !== null
            : $this->review()->exists();
    }

    /**
     * Ubah status request sekaligus mencatat riwayatnya.
     */
    public function recordStatus(RequestStatus $status, ?User $changedBy = null, ?string $note = null): RequestStatusHistory
    {
        $this->update(['status' => $status]);

        return $this->statusHistories()->create([
            'status' => $status,
            'note' => $note,
            'changed_by' => $changedBy?->id,
        ]);
    }

    public function isOpen(): bool
    {
        return $this->status === RequestStatus::Open;
    }

    public function isCompleted(): bool
    {
        return $this->status === RequestStatus::Completed;
    }

    public function canBeScheduled(): bool
    {
        return in_array($this->status, [RequestStatus::Assigned, RequestStatus::Scheduled], true);
    }

    public function canBeStarted(): bool
    {
        return in_array($this->status, [RequestStatus::Assigned, RequestStatus::Scheduled], true);
    }

    public function isInProgress(): bool
    {
        return $this->status === RequestStatus::InProgress;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status->isCancellableByCustomer();
    }

    public function canBeCancelledByPartner(): bool
    {
        return $this->status->isCancellableByPartner();
    }

    /**
     * Apakah mitra ini sudah mengirim penawaran untuk request ini.
     */
    public function hasOfferFrom(PartnerProfile $partner): bool
    {
        return $this->offers->contains(fn (Offer $offer) => $offer->partner_profile_id === $partner->id);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => RequestMode::class,
            'status' => RequestStatus::class,
            'scheduled_at' => 'datetime',
            'agreed_price' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }
}
