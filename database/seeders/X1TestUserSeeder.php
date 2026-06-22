<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class X1TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'teste@x1.com'],
            [
                'firstname' => 'Usuário',
                'lastname' => 'Teste X1',
                'name' => 'Usuário Teste X1',
                'username' => 'testex1',
                'password' => Hash::make('123456'),
                'kyc_status' => 'approved',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'status' => 1,
                'kv' => 1,
                'ev' => 1,
                'sv' => 1,
                'ts' => 0,
            ]
        );

        $this->command->info("✅ Usuário de teste criado:");
        $this->command->info("   Email: teste@x1.com");
        $this->command->info("   Senha: 123456");
        $this->command->info("   KYC: approved");
        $this->command->info("   ID: {$user->id}");
    }
}
