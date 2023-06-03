<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $users_id
 * @property string $type
 * @property integer $coins_id
 * @property string $symbol
 * @property string $icon
 * @property numeric $balance
 * @property date $staking_ended_at
 * @property numeric $withdrawable_snapshot
 * @property string $pending_type
 * @property numeric $pending_fee
 * @property numeric $reward_loyalty_amount
 * @property string $pending_status
 * @property string $pending_withdrawal_type
 * @property json $pending_withdrawal_approve_users
 * @property integer $pending_withdrawal_approve_users_id
 * @property integer $pledge_profits_id
 * @property integer $web3_transactions_id
 * @property string $message
 * @property date $created_at
 * @property date $updated_at
 * @property boolean $use_free_fee

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
class BaseAssets extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'assets';
    protected string $comment = '';
    protected $fillable = ['users_id', 'type', 'coins_id', 'symbol', 'icon', 'balance', 'staking_ended_at', 'withdrawable_snapshot', 'pending_type', 'pending_fee', 'reward_loyalty_amount', 'pending_status', 'pending_withdrawal_type', 'pending_withdrawal_approve_users', 'pending_withdrawal_approve_users_id', 'pledge_profits_id', 'web3_transactions_id', 'message', 'use_free_fee'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function coin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Coins::class, 'coins_id', 'id');
    }
    public function pending_withdrawal_approve_user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'pending_withdrawal_approve_users_id', 'id');
    }
    public function pledge_profit(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\PledgeProfits::class, 'pledge_profits_id', 'id');
    }
    public function web3_transaction(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Web3Transactions::class, 'web3_transactions_id', 'id');
    }
    public function asset_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\AssetLogs::class, 'assets_id', 'id');
    }

    # endregion
}
