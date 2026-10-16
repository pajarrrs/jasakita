<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PartnerService extends Pivot
{
    protected $table = 'partner_services';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'custom_price' => 'decimal:2',
        ];
    }
}
