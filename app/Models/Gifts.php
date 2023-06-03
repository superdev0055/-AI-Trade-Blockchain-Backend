<?php

namespace App\Models;

use App\Models\Base\BaseGifts;

/**
 * @method static find(int $id)
 * @method static lockForUpdate()
 * @method static who(Users $user)
 */
class Gifts extends BaseGifts
{
    protected $hidden = ['formula'];
}
