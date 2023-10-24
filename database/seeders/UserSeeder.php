<?php

namespace Database\Seeders;

use App\Enums\Constant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            'email' => 'admin@gmail.com',
            'password' => Hash::make(123123),
            'avatar' => '',
            'display_name' => 'admin',
            'phone_number' => '12345678901',
            'status' => User::$active,
            'role_id' => User::$admin,
            'has_edit' => User::$has_edit,
            'verify' => User::$verify,
            'detail_address' => 'example address 1',
            'device_token' => 'xxx111xxx',
        ]);
        User::insert([
            'email' => 'user@gmail.com',
            'password' => Hash::make(123123),
            'avatar' => '',
            'display_name' => 'user',
            'phone_number' => '12345678901',
            'status' => User::$active,
            'role_id' => User::$user,
            'has_edit' => User::$has_edit,
            'verify' => User::$verify,
            'detail_address' => 'example address 2',
            'device_token' => 'xxx222xxx',
        ]);
        User::insert([
            'email' => 'staff@gmail.com',
            'password' => Hash::make(123123),
            'avatar' => '',
            'display_name' => 'staff',
            'phone_number' => '12345678901',
            'status' => User::$active,
            'role_id' => User::$staff,
            'has_edit' => User::$has_edit,
            'verify' => User::$verify,
            'detail_address' => 'example address 3',
            'device_token' => 'xxx222xxx',
        ]);
    }
}
