<?php

use Illuminate\Database\Seeder;

class vehicleModelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vehicle_model')->delete();
    	$dateTimeStr = date('Y-m-d H:i:s');

    	DB::table('vehicle_model')->insert([
            ['vehicle_make_id' =>'1','model_name' => 'Swift','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['vehicle_make_id' =>'1','model_name' => 'Wagon R','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['vehicle_make_id' =>'2','model_name' => 'Elite i20','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['vehicle_make_id' =>'2','model_name' => 'Grand i10','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['vehicle_make_id' =>'3','model_name' => 'Terrano','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
            ['vehicle_make_id' =>'3','model_name' => 'Sunny','status' =>'Active','created_at' =>$dateTimeStr,'updated_at' => $dateTimeStr],
        ]);
    }
}
