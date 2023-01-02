<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(PermissionTableSeeder::class);
//        $this->call(Role::class);
        $this->call(AdminUserSeeder::class);
        $this->call(SizeSeeder::class);
        $this->call(BannerSeeder::class);
    }
}
