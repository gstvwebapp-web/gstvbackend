<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use Carbon\Carbon;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert multiple records
        Module::insert([
            [
                'title' => 'Categories',
                'moduleLink' => '/category',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'SubCategories',
                'moduleLink' => '/subcategory',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'News',
                'moduleLink' => '/news',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Pages/CMS',
                'moduleLink' => '/pages',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Users',
                'moduleLink' => '/user',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Change Password',
                'moduleLink' => '/changepassword',
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Setting',
                'moduleLink' => '/setting',
                'status' => 'Inactive',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],            
        ]);
    }
}
