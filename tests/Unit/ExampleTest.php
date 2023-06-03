<?php

namespace Tests\Unit;

use App\Enums\AssetsPendingStatusEnum;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_that_true_is_true()
    {
        AssetsPendingStatusEnum::toJson();
    }
}
