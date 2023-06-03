<?php

namespace App\Models;

use App\Enums\PledgesStatusEnum;
use App\Models\Base\BasePledges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method sync(array $sync)
 * @method static firstOrFail()
 * @method static onGoing()
 * @method static where(string $string, int $id)
 * @method static withCount(string $string)
 * @method static withUser()
 * @property mixed $funds
 * @property mixed $user
 */
class Pledges extends BasePledges
{
    public function funds(): BelongsToMany
    {
        return $this->belongsToMany(Funds::class, 'pledges_has_funds', 'pledges_id', 'funds_id')
            ->withPivot('users_id', 'pledges_id', 'funds_id', 'main_coins_id', 'sub_coins_id', 'profits', 'main_coin_price', 'apy_current');
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnGoing(Builder $query): Builder
    {
        return $query->where('status', PledgesStatusEnum::OnGoing->name);
    }
}
