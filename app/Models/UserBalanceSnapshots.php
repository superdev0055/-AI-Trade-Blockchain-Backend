<?php

namespace App\Models;

use App\Models\Base\BaseUserBalanceSnapshots;

/**
 * @method static where(string $string, int $id)
 */
class UserBalanceSnapshots extends BaseUserBalanceSnapshots
{
    protected $casts = [
        'balance' => 'double'
    ];
}
