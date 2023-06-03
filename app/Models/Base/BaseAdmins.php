<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $last_login_ip
 * @property date $last_login_time
 * @property boolean $login_failed_count
 * @property date $locked_util
 * @property date $created_at
 * @property date $updated_at

 * @method static ifWhere(array $params, string $string)
 * @method static ifWhereLike(array $params, string $string)
 * @method static ifWhereIn(array $params, string $string)
 * @method static ifRange(array $params, string $string)
 * @method static create(array $array)
 * @method static unique(array $params, array $array, string $string)
 * @method static idp(array $params)
 * @method static findOrFail(int $id)
 * @method static selectRaw(string $string)
 * @method static withTrashed()
 
 */
class BaseAdmins extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'admins';
    protected string $comment = '';
    protected $fillable = ['username', 'password', 'last_login_ip', 'last_login_time', 'login_failed_count', 'locked_util'];


    # region relations
    public function admin_login_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\AdminLoginLogs::class, 'admins_id', 'id');
    }
    public function admin_operate_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\AdminOperateLogs::class, 'admins_id', 'id');
    }

    # endregion
}
