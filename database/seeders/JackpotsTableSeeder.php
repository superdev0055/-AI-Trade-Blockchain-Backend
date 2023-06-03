<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class JackpotsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('jackpots')->delete();

        \DB::table('jackpots')->insert(array (
            0 =>
            array (
                'balance' => '0',
                'created_at' => '2022-12-27 11:21:15',
                'goal' => '1000000.000000',
                'send_airdrop' => '500000.000000',
                'id' => 1,
                'started_at' => '2022-12-27 11:21:15',
                'status' => 'OnGoing',
                'updated_at' => '2022-12-27 12:20:54',
            ),
        ));


    }
}
