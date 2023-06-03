<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $pledges_id
 * @property integer $users_id
 * @property integer $parent_1_id
 * @property integer $parent_2_id
 * @property integer $vips_id
 * @property boolean $is_trail
 * @property boolean $is_new_day
 * @property date $datetime
 * @property boolean $round
 * @property numeric $staking
 * @property integer $duration
 * @property numeric $lose_staking_amount
 * @property numeric $apy
 * @property numeric $loan_apy
 * @property numeric $actual_apy
 * @property numeric $actual_loan_apy
 * @property numeric $income
 * @property numeric $actual_income
 * @property numeric $loyalty_fee
 * @property numeric $loyalty_amount
 * @property boolean $can_automatic_exchange
 * @property date $manual_exchanged_at
 * @property numeric $manual_exchange_fee_percent
 * @property numeric $manual_exchange_fee_amount
 * @property json $funds_detail_json
 * @property string $exchange_status
 * @property boolean $can_profit_guarantee
 * @property numeric $minimum_guarantee_apy
 * @property numeric $minimum_guarantee_amount
 * @property numeric $profit_guarantee_amount
 * @property boolean $done_profit_guarantee
 * @property numeric $deposit_total_amount
 * @property numeric $deposit_loyalty_amount
 * @property numeric $deposit_staking_amount
 * @property string $deposit_status
 * @property integer $deposit_web3_transactions_id
 * @property date $deposited_at
 * @property boolean $can_leveraged_investment
 * @property boolean $can_automatic_loan_repayment
 * @property integer $leverage
 * @property numeric $loan_amount
 * @property numeric $loan_charges
 * @property numeric $loan_charges_fee
 * @property boolean $can_prevent_liquidation
 * @property numeric $prevent_liquidation_amount
 * @property boolean $can_email_notification
 * @property boolean $can_automatic_airdrop_bonus
 * @property boolean $can_automatic_staking
 * @property string $staking_type
 * @property boolean $can_automatic_withdrawal
 * @property numeric $automatic_withdrawal_amount
 * @property numeric $child_1_total_income_eth
 * @property numeric $child_2_total_income_eth
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
class BasePledgeProfits extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'pledge_profits';
    protected string $comment = '';
    protected $fillable = ['pledges_id', 'users_id', 'parent_1_id', 'parent_2_id', 'vips_id', 'is_trail', 'is_new_day', 'datetime', 'round', 'staking', 'duration', 'lose_staking_amount', 'apy', 'loan_apy', 'actual_apy', 'actual_loan_apy', 'income', 'actual_income', 'loyalty_fee', 'loyalty_amount', 'can_automatic_exchange', 'manual_exchanged_at', 'manual_exchange_fee_percent', 'manual_exchange_fee_amount', 'funds_detail_json', 'exchange_status', 'can_profit_guarantee', 'minimum_guarantee_apy', 'minimum_guarantee_amount', 'profit_guarantee_amount', 'done_profit_guarantee', 'deposit_total_amount', 'deposit_loyalty_amount', 'deposit_staking_amount', 'deposit_status', 'deposit_web3_transactions_id', 'deposited_at', 'can_leveraged_investment', 'can_automatic_loan_repayment', 'leverage', 'loan_amount', 'loan_charges', 'loan_charges_fee', 'can_prevent_liquidation', 'prevent_liquidation_amount', 'can_email_notification', 'can_automatic_airdrop_bonus', 'can_automatic_staking', 'staking_type', 'can_automatic_withdrawal', 'automatic_withdrawal_amount', 'child_1_total_income_eth', 'child_2_total_income_eth'];


    # region relations
    public function pledge(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Pledges::class, 'pledges_id', 'id');
    }
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function vip(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Vips::class, 'vips_id', 'id');
    }
    public function deposit_web3_transaction(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\DepositWeb3Transactions::class, 'deposit_web3_transactions_id', 'id');
    }
    public function assets(): Relations\HasMany
    {
        return $this->hasMany(Models\Assets::class, 'pledge_profits_id', 'id');
    }
    public function jackpot_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotLogs::class, 'pledge_profits_id', 'id');
    }

    # endregion
}
