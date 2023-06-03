<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $jackpots_id
 * @property integer $users_id
 * @property integer $web3_transactions_id
 * @property numeric $loyalty
 * @property numeric $airdrop
 * @property integer $rank
 * @property date $expired_at
 * @property boolean $can_automatic_airdrop_bonus
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
class BaseJackpotsHasUsers extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'jackpots_has_users';
    protected string $comment = '';
    protected $fillable = ['jackpots_id', 'users_id', 'web3_transactions_id', 'loyalty', 'airdrop', 'rank', 'expired_at', 'can_automatic_airdrop_bonus', 'status'];


    # region relations
    public function jackpot(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Jackpots::class, 'jackpots_id', 'id');
    }
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function web3_transaction(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Web3Transactions::class, 'web3_transactions_id', 'id');
    }

    # endregion
}
