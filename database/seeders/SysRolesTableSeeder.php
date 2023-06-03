<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SysRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sys_roles')->delete();
        
        \DB::table('sys_roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => '管理员',
                'guard_name' => 'sanctum',
                'color' => '#3f6600',
                'created_at' => '2022-10-05 11:08:42',
                'updated_at' => '2022-10-05 11:08:42',
            ),
        ));
        
        
    }
}