<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $admins_id
 * @property string $route
 * @property json $params
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
class BaseAdminOperateLogs extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'admin_operate_logs';
    protected string $comment = '';
    protected $fillable = ['admins_id', 'route', 'params'];


    # region relations
    public function admin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Admins::class, 'admins_id', 'id');
    }

    # endregion
}
