<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property string $product_type
 * @property string $risk_type
 * @property string $name
 * @property integer $main_coins_id
 * @property integer $sub_coins_id
 * @property json $profits
 * @property integer $duration
 * @property numeric $apr_start
 * @property numeric $apr_end
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
class BaseFunds extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'funds';
    protected string $comment = '';
    protected $fillable = ['product_type', 'risk_type', 'name', 'main_coins_id', 'sub_coins_id', 'profits', 'duration', 'apr_start', 'apr_end'];


    # region relations
    public function main_coin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Coins::class, 'main_coins_id', 'id');
    }
    public function sub_coin(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Coins::class, 'sub_coins_id', 'id');
    }
    public function pledges_has_funds(): Relations\HasMany
    {
        return $this->hasMany(Models\PledgesHasFunds::class, 'funds_id', 'id');
    }

    # endregion
}
