<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property json $trail
 * @property json $trail_kill
 * @property json $user_kill
 * @property json $vip_kill
 * @property json $address
 * @property json $gift
 * @property json $profit
 * @property json $fee
 * @property json $other
 * @property json $staking_reward_loyalty
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
class BaseConfigs extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'configs';
    protected string $comment = '';
    protected $fillable = ['trail', 'trail_kill', 'user_kill', 'vip_kill', 'address', 'gift', 'profit', 'fee', 'other', 'staking_reward_loyalty'];


    # region relations

    # endregion
}
