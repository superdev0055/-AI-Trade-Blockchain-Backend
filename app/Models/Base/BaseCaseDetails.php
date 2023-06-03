<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $cases_id
 * @property integer $users_id
 * @property string $answer
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
class BaseCaseDetails extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'case_details';
    protected string $comment = '';
    protected $fillable = ['cases_id', 'users_id', 'answer'];


    # region relations
    public function case(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Cases::class, 'cases_id', 'id');
    }
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }

    # endregion
}
