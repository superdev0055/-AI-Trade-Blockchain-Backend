<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('admins')->delete();
        
        \DB::table('admins')->insert(array (
            0 => 
            array (
                'id' => 1,
                'username' => 'hello',
                'password' => '$2y$10$F/XZHDWaGiXuxAV0WkHj9.xe4jOvmr5eEFaN8NGmV2YxGl6n0rpgG',
                'last_login_ip' => NULL,
                'last_login_time' => NULL,
                'login_failed_count' => 0,
                'locked_util' => NULL,
                'created_at' => '2022-11-14 12:07:50',
                'updated_at' => '2022-11-14 12:07:50',
            ),
            1 => 
            array (
                'id' => 2,
                'username' => 'world',
                'password' => '$2y$10$sGTZJHFYxud2E9Bn.GHdW.aZ9ACm6g26rJtLvm7KY4CRKNT/SsOy6',
                'last_login_ip' => NULL,
                'last_login_time' => NULL,
                'login_failed_count' => 0,
                'locked_util' => NULL,
                'created_at' => '2022-11-14 12:07:50',
                'updated_at' => '2022-11-14 12:07:50',
            ),
            2 => 
            array (
                'id' => 3,
                'username' => 'coinearn8',
                'password' => '$2y$10$v5WCNAzyHa3QFgLxwMYTuu6CrOf8d7huE0LlDrFozAvYjZYoPLMgi',
                'last_login_ip' => NULL,
                'last_login_time' => NULL,
                'login_failed_count' => 0,
                'locked_util' => NULL,
                'created_at' => '2022-11-14 12:07:50',
                'updated_at' => '2022-11-14 12:07:50',
            ),
            3 => 
            array (
                'id' => 4,
                'username' => 'kuai',
                'password' => '$2y$10$yIYzI91iD.dpVHkcWhLopec/gm.kEJTeE2Ry0vWH4FUhBaRbJDth6',
                'last_login_ip' => NULL,
                'last_login_time' => NULL,
                'login_failed_count' => 0,
                'locked_util' => NULL,
                'created_at' => '2023-02-09 14:58:06',
                'updated_at' => '2023-02-09 14:58:06',
            ),
        ));
        
        
    }
}