<?php

namespace App\Models;

use App\Models\Base\BaseArticleCategories;
use Spatie\Translatable\HasTranslations;

class ArticleCategories extends BaseArticleCategories
{
    use HasTranslations;

    public $translatable = ['name', 'intro'];
}
