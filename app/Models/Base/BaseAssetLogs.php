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
 * @property integer $assets_id
 * @property string $type
 * @property numeric $before
 * @property numeric $amount
 * @property numeric $after
 * @property string $remark
 * @property string $reason
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
class BaseAssetLogs extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'asset_logs';
    protected string $comment = '';
    protected $fillable = ['users_id', 'assets_id', 'type', 'before', 'amount', 'after', 'remark', 'reason'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function asset(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Assets::class, 'assets_id', 'id');
    }

    # endregion
}
