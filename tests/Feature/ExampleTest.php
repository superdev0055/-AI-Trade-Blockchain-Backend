<?php

namespace Tests\Feature;


use App\Models\Vips;
use Exception;
use Illuminate\Support\Facades\Http;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;
use Tests\TestCase;

class MyException extends Exception
{
}


class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testDump()
    {
        $vips = Vips::where('id', '>', 1)
            ->selectRaw('loan_charges as value, CONCAT_WS("","VIP",id-1) as label')
            ->get()
            ->toArray();
        dump($vips);
    }

    public function testReg()
    {
        $string = <<<EOD
/**
* @color red
*/
EOD;
        $regex = '/@color\s+(\w+)/';
        preg_match($regex, $string, $matches);
        dump($matches[1]);
    }
}
