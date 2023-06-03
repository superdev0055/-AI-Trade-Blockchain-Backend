<?php

namespace App\Models;

use App\Models\Base\BaseSysPermissions;
use Kalnoy\Nestedset\NodeTrait;

/**
 * @method static defaultOrder()
 */
class SysPermissions extends BaseSysPermissions
{
    use NodeTrait;
    protected $hidden = ['_lft', '_rgt', 'created_at', 'updated_at'];
}
