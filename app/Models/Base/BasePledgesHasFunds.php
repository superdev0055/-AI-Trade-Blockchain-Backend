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
 * @property integer $pledges_id
 * @property integer $funds_id
 * @property integer $main_coins_id
 * @property integer $sub_coins_id
 * @property json $profits
 * @property numeric $main_coin_price
 * @property numeric $apy_current
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
class BasePledgesHasFunds extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'pledges_has_funds';
    protected string $comment = '';
    protected $fillable = ['users_id', 'pledges_id', 'funds_id', 'main_coins_id', 'sub_coins_id', 'profits', 'main_coin_price', 'apy_current'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function pledge(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Pledges::class, 'pledges_id', 'id');
    }
    public function fund(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Funds::class, 'funds_id', 'id');
    }
    public function main_coin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Coins::class, 'main_coins_id', 'id');
    }
    public function sub_coin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Coins::class, 'sub_coins_id', 'id');
    }

    # endregion
}
