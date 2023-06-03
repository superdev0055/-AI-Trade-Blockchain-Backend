<?php

namespace App\Models;

use App\Models\Base\BaseUserEarningSnapshots;

/**
 * @method static where(string $string, int $id)
 */
class UserEarningSnapshots extends BaseUserEarningSnapshots
{
    protected $casts = [
        'earning' => 'double'
    ];
}
