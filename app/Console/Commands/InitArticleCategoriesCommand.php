<?php

namespace App\Console\Commands;

use App\Models\ArticleCategories;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class InitArticleCategoriesCommand extends Command
{
    protected $signature = 'InitArticleCategoriesCommand';
    protected $description = 'Command description';

    /**
     * @return int
     */
    public function handle(): int
    {
        ArticleCategories::create([
            'name' => [
                'en' => 'Crypto basics',
                'zh_TW' => '加密货币基础'
            ], #
            'intro' => [
                'en' => 'New to crypto? Not for long — start with these guides and explainers',
                'zh_TW' => '加密貨幣新手？ 不長時間-從這些指南和解釋開始'
            ], #
//            'icon' => '', #
        ]);
        return CommandAlias::SUCCESS;
    }
}
