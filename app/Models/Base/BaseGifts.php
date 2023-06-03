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
 * @property string $code
 * @property numeric $amount
 * @property string $type
 * @property integer $total_count
 * @property integer $received_count
 * @property numeric $fee
 * @property numeric $fee_amount
 * @property string $status
 * @property json $formula
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
class BaseGifts extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'gifts';
    protected string $comment = '';
    protected $fillable = ['users_id', 'code', 'amount', 'type', 'total_count', 'received_count', 'fee', 'fee_amount', 'status', 'formula'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function gift_details(): Relations\HasMany
    {
        return $this->hasMany(Models\GiftDetails::class, 'gifts_id', 'id');
    }

    # endregion
}
