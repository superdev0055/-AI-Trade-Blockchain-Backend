<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PersonalAccessTokensTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('personal_access_tokens')->delete();
        
        \DB::table('personal_access_tokens')->insert(array (
            0 => 
            array (
                'id' => 11,
                'tokenable_type' => 'App\\Models\\Admins',
                'tokenable_id' => 4,
                'name' => 'admin',
                'token' => '6514ac73b7ba461c88ef3231a2f40e3c5a039caa0356398f50ec378cadb8c005',
                'abilities' => '["admin"]',
                'last_used_at' => '2023-02-11 03:41:35',
                'expires_at' => NULL,
                'created_at' => '2023-02-11 03:15:16',
                'updated_at' => '2023-02-11 03:41:35',
            ),
            1 => 
            array (
                'id' => 12,
                'tokenable_type' => 'App\\Models\\Admins',
                'tokenable_id' => 2,
                'name' => 'admin',
                'token' => 'b41251c90b5def55d704184e48d0c0fb56be353aafb1ee423a68f17d3a7cb6c9',
                'abilities' => '["admin"]',
                'last_used_at' => '2023-02-11 03:38:42',
                'expires_at' => NULL,
                'created_at' => '2023-02-11 03:24:08',
                'updated_at' => '2023-02-11 03:38:42',
            ),
            2 => 
            array (
                'id' => 13,
                'tokenable_type' => 'App\\Models\\Admins',
                'tokenable_id' => 1,
                'name' => 'admin',
                'token' => '355a387f6f7fa127fec771626eb0c8db76ec96600aaf2039d57240ba8285525a',
                'abilities' => '["admin"]',
                'last_used_at' => '2023-03-07 16:23:44',
                'expires_at' => NULL,
                'created_at' => '2023-03-07 15:20:32',
                'updated_at' => '2023-03-07 16:23:44',
            ),
        ));
        
        
    }
}