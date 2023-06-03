<?php

namespace App\Models;

use App\Models\Base\BaseAdmins;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method static where(string $string, mixed $username)
 */
class Admins extends BaseAdmins
{
    use HasApiTokens, HasRoles;

    protected $hidden = ['password'];
    protected string $guard_name = 'sanctum';

}
