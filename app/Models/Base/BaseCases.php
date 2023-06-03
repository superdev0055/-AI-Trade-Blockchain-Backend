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
 * @property string $subject
 * @property string $content
 * @property string $case_id
 * @property string $status
 * @property boolean $frontend_is_new
 * @property boolean $backend_is_new
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
class BaseCases extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'cases';
    protected string $comment = '';
    protected $fillable = ['users_id', 'subject', 'content', 'case_id', 'status', 'frontend_is_new', 'backend_is_new'];


    # region relations
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\Users::class, 'users_id', 'id');
    }
    public function case_details(): Relations\HasMany
    {
        return $this->hasMany(Models\CaseDetails::class, 'cases_id', 'id');
    }

    # endregion
}
