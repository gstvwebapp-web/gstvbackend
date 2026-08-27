<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'GSTV Admin',
            'email' => 'dineshgstv@gmail.com',
            'password' => Hash::make('dineshgstv'),
            'remember_token' => \Illuminate\Support\Str::random(50),
            'catID' => '1',
            'moduleID' => '1',
            'roleID' => '1',
            'status' => 'Active',
            'email_verified_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            // Add other fields as needed
        ]);
    }
}
