<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users= [
          ['name'=>'Super Admin', 'email'=> 'super@gmail.com','password'=>'super123','status'=>true],
          ['name'=>'Admin', 'email'=> 'admin@gmail.com','password'=>'admin123','status'=>true]
        ];
        foreach ( $users as $user){

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bcrypt($user['password']),
                'status' => true,
                'email_verified_at' =>Carbon::now()
            ]);
        }

    }
}
