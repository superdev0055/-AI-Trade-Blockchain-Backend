<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Relations;
use App\Models;

/**
 * @property integer $id
 * @property integer $article_categories_id
 * @property json $title
 * @property json $intro
 * @property json $markdown
 * @property json $cover_img
 * @property integer $order_column
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
class BaseArticles extends Model
{
    use HasFactory, ModelTrait;

    protected $table = 'articles';
    protected string $comment = '';
    protected $fillable = ['article_categories_id', 'title', 'intro', 'markdown', 'cover_img', 'order_column'];


    # region relations
    public function article_category(): Relations\BelongsTo
    {
        return $this->belongsTo(Models\ArticleCategories::class, 'article_categories_id', 'id');
    }

    # endregion
}
