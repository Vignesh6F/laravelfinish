<?php

use Illuminate\Database\Seeder;

class SupportTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('supports')->insert([
            ['name' =>'Whatsapp', 'link' =>'911234567890', 'image' => 'whatsapp.png', 'status' =>'Active'],
            ['name' =>'Skype', 'link' =>'123456789', 'image' => 'skype.jpeg', 'status' =>'Active'],
        ]);
    }
}
