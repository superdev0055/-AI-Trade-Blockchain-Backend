<?php

namespace App\Models;

use App\Models\Base\BaseJackpots;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static order()
 * @method static where(string $string, string $value)
 */
class Jackpots extends BaseJackpots
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Users::class, 'jackpots_has_users', 'jackpots_id', 'users_id')
            ->withPivot('earnings', 'airdrop');
    }
}
