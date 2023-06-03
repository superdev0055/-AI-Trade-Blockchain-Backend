<?php

namespace LaravelCommon\App\Helpers;


use Tests\TestCase;

class GenHelperTest extends TestCase
{

    public function testGenTableRelations()
    {
        $table = TableHelper::GetTable('bonuses');
        GenHelper::GenTableRelations($table);

    }
}
