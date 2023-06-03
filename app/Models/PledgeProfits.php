<?php

namespace App\Models;

use App\Models\Base\BasePledgeProfits;

/**
 * @method static where(string $string, int $id)
 * @method static find(int $id)
 * @method static withUser()
 * @method static today()
 */
class PledgeProfits extends BasePledgeProfits
{
    protected $casts = [
        'actual_income' => 'double'
    ];
}
