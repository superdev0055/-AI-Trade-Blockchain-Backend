<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property date $day
 * @property numeric $staking_amount
 * @property numeric $withdraw_amount
 * @property numeric $exchange_airdrop_amount
 * @property numeric $deposit_staking_amount
 * @property numeric $staking_reward_loyalty_amount
 * @property numeric $income_amount
 * @property numeric $actual_income_amount
 * @property numeric $withdrawable_amount
 * @property integer $user_register_count
 * @property integer $user_login_count
 * @property integer $trail_count
 * @property integer $staking_count
 * @property integer $withdraw_count
 * @property integer $exchange_airdrop_count
 * @property integer $deposit_staking_count
 * @property integer $staking_reward_loyalty_count
 * @property integer $income_count
 * @property integer $actual_income_count
 * @property integer $withdrawable_count
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
class BaseReports extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'reports';
    protected string $comment = '';
    protected $fillable = ['day', 'staking_amount', 'withdraw_amount', 'exchange_airdrop_amount', 'deposit_staking_amount', 'staking_reward_loyalty_amount', 'income_amount', 'actual_income_amount', 'withdrawable_amount', 'user_register_count', 'user_login_count', 'trail_count', 'staking_count', 'withdraw_count', 'exchange_airdrop_count', 'deposit_staking_count', 'staking_reward_loyalty_count', 'income_count', 'actual_income_count', 'withdrawable_count'];


    # region relations

    # endregion
}
