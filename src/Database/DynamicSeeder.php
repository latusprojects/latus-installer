<?php


namespace Latus\Installer\Database;


use Illuminate\Database\Seeder;
use Latus\Installer\Database\Seeders\DatabaseSeeder;

class DynamicSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach ((new SeederRepository())->all() as $seeder) {
            $this->call($seeder);
        }

        $this->call(DatabaseSeeder::class);
    }
}