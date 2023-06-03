<?php

namespace App\Models;

use App\Models\Base\BaseArticles;
use Spatie\Translatable\HasTranslations;

class Articles extends BaseArticles
{
    use HasTranslations;

    public $translatable = ['title', 'intro', 'markdown'];
}
