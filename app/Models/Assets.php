<?php

namespace App\Models;

use App\Models\Base\BaseAssets;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static who(Users $user)
 * @method static where(string $string, int $id)
 * @method static find(mixed $assets_id)
 * @method static lockForUpdate()
 * @method static today($now)
 */
class Assets extends BaseAssets
{
}
