<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property json $name
 * @property json $intro
 * @property json $icon
 * @property integer $_lft
 * @property integer $_rgt
 * @property integer $parent_id
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
class BaseArticleCategories extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'article_categories';
    protected string $comment = '';
    protected $fillable = ['name', 'intro', 'icon', '_lft', '_rgt', 'parent_id'];


    # region relations
    public function articles(): Relations\HasMany
    {
        return $this->hasMany(Models\Articles::class, 'article_categories_id', 'id');
    }

    # endregion
}
