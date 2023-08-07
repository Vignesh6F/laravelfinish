<?php

use Illuminate\Database\Seeder;

class VehiclesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('car_type')->insert([
            ['id'=>'1', 'car_name' =>'Micro', 'description' =>'Micro', 'is_pool' => "No", 'status' =>'Active', 'vehicle_image'=> 'GoferGo.png', 'active_image' =>'GoferGo_blue.png'],
            ['id'=>'2', 'car_name' =>'Mini', 'description' =>'Mini', 'is_pool' => "No", 'status' =>'Active', 'vehicle_image'=> 'GoferX.png', 'active_image' =>'GoferX_Blue.png'],
            ['id'=>'3', 'car_name' =>'Prime', 'description' =>'Prime', 'is_pool' => "No", 'status' =>'Active', 'vehicle_image'=> 'GoferXL.png', 'active_image' =>'GoferXL_Blue.png'],
            ['id'=>'4', 'car_name' =>'POOL', 'description' =>'POOL', 'is_pool' => "Yes", 'status' =>'Active', 'vehicle_image'=> 'Goferpool_black.png', 'active_image' =>'Goferpool.png'],
        ]);
    }
}
