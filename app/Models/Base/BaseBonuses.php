<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $from_users_id
 * @property integer $to_users_id
 * @property string $type
 * @property numeric $friend_bonus
 * @property numeric $bonus_rate
 * @property numeric $bonus
 * @property string $status
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
class BaseBonuses extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'bonuses';
    protected string $comment = '';
    protected $fillable = ['from_users_id', 'to_users_id', 'type', 'friend_bonus', 'bonus_rate', 'bonus', 'status'];


    # region relations
    public function from_user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'from_users_id', 'id');
    }
    public function to_user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'to_users_id', 'id');
    }

    # endregion
}
