<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EntitiesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('entities')->delete();
        
        \DB::table('entities')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => 'Сделка',
                'created_at' => '2021-06-30 13:22:36',
                'updated_at' => '2021-06-30 13:22:39',
            ),
            1 => 
            array (
                'id' => 2,
                'title' => 'Лид',
                'created_at' => '2021-06-30 13:22:36',
                'updated_at' => '2021-06-30 13:22:39',
            ),
            2 => 
            array (
                'id' => 3,
                'title' => 'Контакт',
                'created_at' => '2021-06-30 13:22:36',
                'updated_at' => '2021-06-30 13:22:39',
            ),
            3 => 
            array (
                'id' => 4,
                'title' => 'Компания',
                'created_at' => '2021-06-30 13:22:36',
                'updated_at' => '2021-06-30 13:22:39',
            ),
        ));
        
        
    }
}