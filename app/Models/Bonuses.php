<?php

namespace App\Models;

use App\Models\Base\BaseBonuses;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static who(Users $user)
 * @method static where(string $string, int $id)
 */
class Bonuses extends BaseBonuses
{
    public function from(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'from_users_id', 'id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'to_users_id', 'id');
    }
}
