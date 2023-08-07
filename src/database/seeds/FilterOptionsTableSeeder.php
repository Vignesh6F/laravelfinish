<?php

use Illuminate\Database\Seeder;

class FilterOptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //disable foreign key check for this connection before running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('filter_options')->truncate();

        DB::table('filter_options')->insert([
    		['name' => 'Prefer Female Riders only'],
            ['name' => 'Prefer Handicap Accessibility'],
            ['name' => 'Prefer Child Seat Accessibility'],
            ['name' => 'Prefer Female Drivers only'],
    	]);

        DB::table('filter_options_translations')->truncate();

        DB::table('filter_options_translations')->insert([
            ['filter_option_id'=>1, 'name'=>'تفضل الفرسان الإناث فقط', 'locale'=>'ar'],
            ['filter_option_id'=>1, 'name'=>'Preferir solo mujeres jinetes', 'locale'=>'es'],
            ['filter_option_id'=>1, 'name'=>'فقط زن سوارکار را ترجیح دهید', 'locale'=>'fa'],
            ['filter_option_id'=>1, 'name'=>'Prefira apenas mulheres', 'locale'=>'pt'],

            ['filter_option_id'=>2, 'name'=>'تفضل الوصول للمعاقين', 'locale'=>'ar'],
            ['filter_option_id'=>2, 'name'=>'Prefiero la accesibilidad para discapacitados', 'locale'=>'es'],
            ['filter_option_id'=>2, 'name'=>'دسترسی معلولیت را ترجیح دهید', 'locale'=>'fa'],
            ['filter_option_id'=>2, 'name'=>'Prefira acessibilidade para deficientes', 'locale'=>'pt'],

            ['filter_option_id'=>3, 'name'=>'تفضل الوصول إلى مقعد الطفل', 'locale'=>'ar'],
            ['filter_option_id'=>3, 'name'=>'Prefiero la accesibilidad del asiento para niños', 'locale'=>'es'],
            ['filter_option_id'=>3, 'name'=>'دسترسی صندلی کودک را ترجیح دهید', 'locale'=>'fa'],
            ['filter_option_id'=>3, 'name'=>'Acessibilidade preferencial para cadeirinha de criança', 'locale'=>'pt'],

            ['filter_option_id'=>4, 'name'=>'تفضل السائقات الإناث فقط', 'locale'=>'ar'],
            ['filter_option_id'=>4, 'name'=>'Prefiero solo mujeres conductoras', 'locale'=>'es'],
            ['filter_option_id'=>4, 'name'=>'فقط رانندگان زن را ترجیح دهید', 'locale'=>'fa'],
            ['filter_option_id'=>4, 'name'=>'Prefira apenas motoristas', 'locale'=>'pt'],
        ]);

        //enable foreign key check for this connection after running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
