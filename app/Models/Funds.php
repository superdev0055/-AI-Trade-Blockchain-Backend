<?php

namespace App\Models;

use App\Models\Base\BaseFunds;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static find(int $id)
 */
class Funds extends BaseFunds
{
    public function mainCoin(): BelongsTo
    {
        return $this->belongsTo(Coins::class, 'main_coins_id', 'id');
    }

    public function subCoin(): BelongsTo
    {
        return $this->belongsTo(Coins::class, 'sub_coins_id', 'id');
    }
}
