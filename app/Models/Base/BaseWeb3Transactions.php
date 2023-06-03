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
 * @property integer $coins_id
 * @property string $operator_type
 * @property integer $operator_id
 * @property string $type
 * @property string $coin_network
 * @property string $coin_symbol
 * @property string $coin_address
 * @property numeric $coin_amount
 * @property numeric $usd_price
 * @property string $from_address
 * @property string $to_address
 * @property json $send_transaction
 * @property string $hash
 * @property string $block_number
 * @property json $receipt
 * @property string $message
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
class BaseWeb3Transactions extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'web3_transactions';
    protected string $comment = '';
    protected $fillable = ['users_id', 'coins_id', 'operator_type', 'operator_id', 'type', 'coin_network', 'coin_symbol', 'coin_address', 'coin_amount', 'usd_price', 'from_address', 'to_address', 'send_transaction', 'hash', 'block_number', 'receipt', 'message', 'status'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function coin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Coins::class, 'coins_id', 'id');
    }
    public function assets(): Relations\HasMany
    {
        return $this->hasMany(Models\Assets::class, 'web3_transactions_id', 'id');
    }
    public function jackpots_has_users(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotsHasUsers::class, 'web3_transactions_id', 'id');
    }

    # endregion
}
