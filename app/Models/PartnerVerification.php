<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Database\Factories\PartnerVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'partner_profile_id',
    'full_name',
    'id_number',
    'id_photo_path',
    'selfie_photo_path',
    'status',
    'rejection_reason',
    'reviewed_by',
    'reviewed_at',
])]
class PartnerVerification extends Model
{
    /** @use HasFactory<PartnerVerificationFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<PartnerProfile, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_profile_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === VerificationStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === VerificationStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === VerificationStatus::Rejected;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
