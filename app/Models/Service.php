<?php

namespace App\Models;

use App\Enums\PriceUnit;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'service_category_id',
    'name',
    'slug',
    'description',
    'base_price',
    'price_unit',
    'duration_estimate',
    'is_active',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ServiceCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Mitra yang menawarkan jasa ini.
     *
     * @return BelongsToMany<PartnerProfile, $this>
     */
    public function partnerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(PartnerProfile::class, 'partner_services')
            ->using(PartnerService::class)
            ->withPivot(['custom_price', 'note'])
            ->withTimestamps();
    }

    /**
     * Request pelanggan untuk jasa ini.
     *
     * @return HasMany<ServiceRequest, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'price_unit' => PriceUnit::class,
            'is_active' => 'boolean',
        ];
    }
}
