<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        dump('Artisan db:cache');
        Artisan::call('db:cache');
        dump('Artisan update:models');
        Artisan::call('update:models');

        $this->call(AdminsTableSeeder::class);
        $this->call(SysRolesTableSeeder::class);
        $this->call(SysPermissionsTableSeeder::class);
        $this->call(SysModelHasRolesTableSeeder::class);
        $this->call(SysRoleHasPermissionsTableSeeder::class);
        $this->call(PersonalAccessTokensTableSeeder::class);

        $this->call(VipsTableSeeder::class);
        $this->call(CoinsTableSeeder::class);
        $this->call(FundsTableSeeder::class);
        $this->call(JackpotsTableSeeder::class);

//        Artisan::call('MakeCoinsCommand');
//        Artisan::call('MakeFundsCommand');
//        Artisan::call('MakeJackpotCommand');
        $this->call(ConfigsTableSeeder::class);
//        $this->call(UsersTableSeeder::class);
    }
}
