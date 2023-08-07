<?php

use Illuminate\Database\Seeder;

class CancelReasonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        
        DB::table('cancel_reasons')->delete();

        DB::table('cancel_reasons')->insert([
        	["reason" => "Driver Not Available", "cancelled_by" => "Rider"],
        	["reason" => "Driver not respond proper	", "cancelled_by" => "Rider"],
        	["reason" => "Wrong Route", "cancelled_by" => "Rider"],
        	["reason" => "Rider Not Available", "cancelled_by" => "Driver"],
        	["reason" => "Rider not respond proper", "cancelled_by" => "Driver"],
        	["reason" => "Rider not yet come", "cancelled_by" => "Driver"],
        	["reason" => "Rider ask for Cancel", "cancelled_by" => "Admin"],
        	["reason" => "Other Reasons", "cancelled_by" => "Admin"],
        	["reason" => "Rider Cancelled", "cancelled_by" => "Company"],
        ]);
    }
}
