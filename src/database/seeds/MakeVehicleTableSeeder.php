<?php

use Illuminate\Database\Seeder;

class MakeVehicleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('vehicle_make')->delete();
    	$dateTimeStr = date('Y-m-d H:i:s');

        DB::table('vehicle_make')->insert([
            ['make_vehicle_name' =>'Maruti','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['make_vehicle_name' =>'Hyundai','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['make_vehicle_name' =>'Nissan','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
        ]);
    }
}
