<?php

namespace App\Models;

use App\Models\Base\BaseGiftDetails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, int $id)
 */
class GiftDetails extends BaseGiftDetails
{
    public function from(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'from_users_id', 'id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'to_users_id', 'id');
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeWithGift(Builder $builder): Builder
    {
        return $builder->with(['gift' => function ($query) {
            $query->withUser();
        }]);
    }
}
