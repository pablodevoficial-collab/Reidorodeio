<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $email    = env('ADMIN_EMAIL', 'admin@local.test');
        $password = env('ADMIN_PASSWORD', 'Admin123');

        $admin = Admin::where('username', $username)->first();

        if ($admin) {
            $admin->email = $admin->email ?: $email;
            $admin->password = Hash::make($password);
            $admin->save();
            $this->command?->info("Admin updated: {$username}");
        } else {
            $admin = new Admin();
            $admin->name = 'Super Admin';
            $admin->username = $username;
            $admin->email = $email;
            $admin->password = Hash::make($password);
            $admin->save();
            $this->command?->info("Admin created: {$username}");
        }
    }
}
