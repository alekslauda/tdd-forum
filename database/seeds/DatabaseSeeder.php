<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        // seed users
        factory(\App\User::class, 1)->create(['name' => 'test', 'email' => 'test@abv.bg', 'password' => bcrypt('test')]);
        factory(\App\User::class, 50)->create();

        // seed channels
        factory(\App\Channel::class, 20)->create();

        // seed threads
        factory(\App\Thread::class, 10)->create();

        // seed replies
        factory(\App\Reply::class, 10)->create();


    }
}
