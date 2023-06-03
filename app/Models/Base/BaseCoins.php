<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property string $cg_id
 * @property string $symbol
 * @property string $name
 * @property string $icon
 * @property string $network
 * @property string $address
 * @property integer $market_cap_rank
 * @property json $sparkline
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
class BaseCoins extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'coins';
    protected string $comment = '';
    protected $fillable = ['cg_id', 'symbol', 'name', 'icon', 'network', 'address', 'market_cap_rank', 'sparkline'];


    # region relations
    public function assets(): Relations\HasMany
    {
        return $this->hasMany(Models\Assets::class, 'coins_id', 'id');
    }
    public function web3_transactions(): Relations\HasMany
    {
        return $this->hasMany(Models\Web3Transactions::class, 'coins_id', 'id');
    }

    # endregion
}
