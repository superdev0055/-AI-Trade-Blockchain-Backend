<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property string $name
 * @property string $guard_name
 * @property string $title
 * @property string $url
 * @property integer $_lft
 * @property integer $_rgt
 * @property integer $parent_id
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
class BaseSysPermissions extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'sys_permissions';
    protected string $comment = '';
    protected $fillable = ['name', 'guard_name', 'title', 'url', '_lft', '_rgt', 'parent_id'];


    # region relations

    # endregion
}
