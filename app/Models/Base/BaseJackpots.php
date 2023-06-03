<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property numeric $goal
 * @property numeric $balance
 * @property numeric $send_airdrop
 * @property date $started_at
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
class BaseJackpots extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'jackpots';
    protected string $comment = '';
    protected $fillable = ['goal', 'balance', 'send_airdrop', 'started_at', 'status'];


    # region relations
    public function jackpot_logs(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotLogs::class, 'jackpots_id', 'id');
    }
    public function jackpots_has_users(): Relations\HasMany
    {
        return $this->hasMany(Models\JackpotsHasUsers::class, 'jackpots_id', 'id');
    }

    # endregion
}
