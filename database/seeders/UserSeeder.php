<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // 1. Pastikan role 'Admin' sudah dibuat terlebih dahulu
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // 2. Buat data user dan tampung ke dalam variabel objek
        $user = User::create([
            'name' => 'bryan',
            'username' => 'bryan',
            'email' => 'bryanambraham@gmail.com',
            'password' => Hash::make('bryan123'),
        ]);
        
        // 3. Berikan role Admin ke user yang baru dibuat
        $user->assignRole($adminRole);
    }
}
