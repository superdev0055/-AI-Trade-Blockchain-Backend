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
 * @property boolean $is_trail
 * @property date $started_at
 * @property date $ended_at
 * @property date $canceled_at
 * @property numeric $staking
 * @property numeric $estimate_apy
 * @property numeric $actual_apy
 * @property numeric $actual_loan_apy
 * @property numeric $earnings_this_node
 * @property numeric $earnings_today
 * @property boolean $auto_joined_funds
 * @property boolean $next_round_is_1
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
class BasePledges extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'pledges';
    protected string $comment = '';
    protected $fillable = ['users_id', 'is_trail', 'started_at', 'ended_at', 'canceled_at', 'staking', 'estimate_apy', 'actual_apy', 'actual_loan_apy', 'earnings_this_node', 'earnings_today', 'auto_joined_funds', 'next_round_is_1', 'status'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function jackpot_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotLogs::class, 'pledges_id', 'id');
    }
    public function pledge_profits(): Relations\HasMany
    {
        return $this->hasMany(Models\PledgeProfits::class, 'pledges_id', 'id');
    }
    public function pledges_has_funds(): Relations\HasMany
    {
        return $this->hasMany(Models\PledgesHasFunds::class, 'pledges_id', 'id');
    }

    # endregion
}
