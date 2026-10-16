<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_request_id',
    'partner_profile_id',
    'price',
    'duration_estimate',
    'message',
    'status',
])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ServiceRequest, $this>
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * @return BelongsTo<PartnerProfile, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_profile_id');
    }

    public function isFrom(PartnerProfile $partner): bool
    {
        return $this->partner_profile_id === $partner->id;
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => OfferStatus::class,
        ];
    }
}
