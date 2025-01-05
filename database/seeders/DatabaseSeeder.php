<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


            $user = DB::table('users')->first();

            if ($user) {
                return;
            }

            $tables = DB::select('SHOW TABLES');

            foreach ($tables as $key => $item) {
                $table = $item->Tables_in_product_esmk;
                if (!DB::connection('mysql_portalisasi')->getSchemaBuilder()->hasTable($table)) {
                    continue;
                }
                $data = DB::connection('mysql_portalisasi')->table($table)->get();
                foreach ($data as $keyData => $itemData) {
                    if($table == "migrations"){
                        continue;
                    }
                    $itemData = (array) $itemData;
                    DB::table($table)->insert($itemData);
                }
            }

    }
}
