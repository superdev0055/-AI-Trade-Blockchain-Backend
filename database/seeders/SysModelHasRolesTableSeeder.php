<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SysModelHasRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sys_model_has_roles')->delete();
        
        \DB::table('sys_model_has_roles')->insert(array (
            0 => 
            array (
                'role_id' => 1,
                'model_type' => 'App\\Models\\Admins',
                'model_id' => 1,
            ),
            1 => 
            array (
                'role_id' => 1,
                'model_type' => 'App\\Models\\Admins',
                'model_id' => 2,
            ),
            2 => 
            array (
                'role_id' => 1,
                'model_type' => 'App\\Models\\Admins',
                'model_id' => 3,
            ),
            3 => 
            array (
                'role_id' => 1,
                'model_type' => 'App\\Models\\Admins',
                'model_id' => 4,
            ),
        ));
        
        
    }
}