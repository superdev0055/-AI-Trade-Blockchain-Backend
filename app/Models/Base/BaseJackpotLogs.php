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
 * @property integer $jackpots_id
 * @property integer $pledges_id
 * @property integer $pledge_profits_id
 * @property numeric $before
 * @property numeric $amount
 * @property numeric $after
 * @property string $remark
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
class BaseJackpotLogs extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'jackpot_logs';
    protected string $comment = '';
    protected $fillable = ['users_id', 'jackpots_id', 'pledges_id', 'pledge_profits_id', 'before', 'amount', 'after', 'remark'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function jackpot(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Jackpots::class, 'jackpots_id', 'id');
    }
    public function pledge(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Pledges::class, 'pledges_id', 'id');
    }
    public function pledge_profit(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\PledgeProfits::class, 'pledge_profits_id', 'id');
    }

    # endregion
}
