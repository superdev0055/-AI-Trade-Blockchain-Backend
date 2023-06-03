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
 * @property string $address
 * @property string $private_key
 * @property string $nickname
 * @property string $avatar
 * @property string $email
 * @property string $parent_address
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
class BaseFakeUsers extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'fake_users';
    protected string $comment = '';
    protected $fillable = ['users_id', 'address', 'private_key', 'nickname', 'avatar', 'email', 'parent_address'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }

    # endregion
}
