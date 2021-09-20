<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'admin',
                'email' => 'admin@gk-assistant.ru',
                'email_verified_at' => NULL,
                'password' => '$2y$10$apSB2VJcT.h3zFj9RMuoWOpBWaLXFjsTm3fsiToRXvEdmqe39l/a.',
                'remember_token' => 'l3oDjtCD2zqCUfZnPmouUe5thphk2qshQxKr607igGCUqdLFsHP1rk2bPCwa',
                'created_at' => '2021-06-30 12:35:58',
                'updated_at' => '2021-06-30 12:35:58',
            ),
        ));
        
        
    }
}