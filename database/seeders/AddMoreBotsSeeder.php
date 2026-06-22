<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddMoreBotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = [
            'João', 'Maria', 'José', 'Ana', 'Pedro', 'Mariana', 'Lucas', 'Juliana', 'Carlos', 'Fernanda',
            'Paulo', 'Camila', 'Rafael', 'Beatriz', 'Gabriel', 'Letícia', 'Felipe', 'Amanda', 'Bruno', 'Larissa',
            'Matheus', 'Gabriela', 'Rodrigo', 'Carla', 'Fernando', 'Patrícia', 'Diego', 'Aline', 'Thiago', 'Renata',
            'Gustavo', 'Tatiana', 'Ricardo', 'Viviane', 'Marcelo', 'Vanessa', 'André', 'Michele', 'Eduardo', 'Daniela',
            'Leandro', 'Adriana', 'Vinícius', 'Roberta', 'Fábio', 'Sandra', 'Henrique', 'Cristina', 'Leonardo', 'Simone',
            'Guilherme', 'Eliane', 'César', 'Silvia', 'Alex', 'Mônica', 'Marcos', 'Regina', 'Renan', 'Luciana',
            'Igor', 'Priscila', 'Daniel', 'Cláudia', 'Caio', 'Rosângela', 'Sérgio', 'Vera', 'Roberto', 'Sueli',
            'Júlio', 'Marta', 'Antônio', 'Helena', 'Márcio', 'Denise', 'Ivan', 'Andréia', 'Victor', 'Fátima',
            'Wagner', 'Joana', 'Edson', 'Rita', 'Cláudio', 'Cintia', 'Rubens', 'Valéria', 'Mauro', 'Sônia',
            'Nelson', 'Elisa', 'Raul', 'Márcia', 'Douglas', 'Alice', 'Samuel', 'Lívia', 'Otávio', 'Sofia',
            'Miguel', 'Isabela', 'Arthur', 'Laura', 'Davi', 'Manuela', 'Lorenzo', 'Valentina', 'Theo', 'Heloísa',
            'Benjamin', 'Cecília', 'Enzo', 'Melissa', 'Nicolas', 'Bruna', 'Murilo', 'Luana', 'Pietro', 'Débora',
            'Emanuel', 'Raquel', 'Isaac', 'Natália', 'Cauã', 'Sabrina', 'Vicente', 'Jéssica', 'Benício', 'Bianca',
            'Joaquim', 'Thaís', 'Bernardo', 'Rafaela', 'Gael', 'Carolina', 'Anthony', 'Alessandra', 'Dante', 'Karina',
            'Heitor', 'Ivone', 'Oliver', 'Rosana', 'Ravi', 'Solange', 'Caleb', 'Teresa', 'Thomas', 'Celeste',
        ];

        $lastNames = [
            'Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes',
            'Costa', 'Ribeiro', 'Martins', 'Carvalho', 'Rocha', 'Almeida', 'Nascimento', 'Araújo', 'Melo', 'Barbosa',
            'Cardoso', 'Correia', 'Dias', 'Fernandes', 'Moreira', 'Castro', 'Azevedo', 'Barros', 'Monteiro', 'Freitas',
            'Pinto', 'Teixeira', 'Mendes', 'Campos', 'Moraes', 'Vieira', 'Soares', 'Duarte', 'Ramos', 'Nunes',
            'Lopes', 'Reis', 'Miranda', 'Machado', 'Batista', 'Gonçalves', 'Cavalcanti', 'Nogueira', 'Farias', 'Pires',
            'Carneiro', 'Andrade', 'Xavier', 'Fonseca', 'Moura', 'Tavares', 'Santiago', 'Cunha', 'Marques', 'Medeiros',
            'Guerra', 'Viana', 'Siqueira', 'Amaral', 'Matos', 'Lourenço', 'Borges', 'Bezerra', 'Guedes', 'Santana',
            'Braga', 'Vasques', 'Coelho', 'Porto', 'Paiva', 'Sales', 'Sampaio', 'Figueiredo', 'Lacerda', 'Toledo',
        ];

        $suffixes = ['', '', '', '', '', '10', '22', '93', '777', 'Rei', 'Pro', 'TX', 'BR', 'Top', '8Ball'];

        $bots = [];
        $existingUsernames = DB::table('bot_users')->pluck('username')->toArray();
        $existingEmails = DB::table('bot_users')->pluck('email')->toArray();

        $this->command->info('🤖 Gerando 350 novos bots...');
        $progressBar = $this->command->getOutput()->createProgressBar(350);
        
        for ($i = 0; $i < 350; $i++) {
            $attempts = 0;
            $maxAttempts = 100;
            
            do {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $suffix = $suffixes[array_rand($suffixes)];
                
                // Username: primeira letra nome + sobrenome + sufixo
                $username = strtolower(substr($firstName, 0, 1) . $lastName . $suffix);
                $username = Str::slug($username, '');
                
                // Email único
                $emailPrefix = strtolower(substr($firstName, 0, 1) . $lastName);
                $emailPrefix = Str::slug($emailPrefix, '');
                $email = $emailPrefix . rand(1000, 9999) . '@botmail.dev';
                
                $attempts++;
            } while (
                (in_array($username, $existingUsernames) || in_array($username, array_column($bots, 'username'))) &&
                $attempts < $maxAttempts
            );

            if ($attempts >= $maxAttempts) {
                $this->command->warn("\n⚠️  Não foi possível gerar username único após $maxAttempts tentativas");
                continue;
            }

            // 70% de chance de ser premium
            $isPremium = rand(1, 100) <= 70;
            
            $bots[] = [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'username' => $username,
                'email' => $email,
                'mobile' => null,
                'cpf' => null,
                'is_premium' => $isPremium,
                'premium_until' => $isPremium ? now()->addMonths(rand(1, 12)) : null,
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ];

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();

        // Inserir em lotes de 100
        $chunks = array_chunk($bots, 100);
        $this->command->info('💾 Inserindo bots no banco em ' . count($chunks) . ' lotes...');
        
        foreach ($chunks as $index => $chunk) {
            DB::table('bot_users')->insert($chunk);
            $this->command->info('   Lote ' . ($index + 1) . ' de ' . count($chunks) . ' inserido ✓');
        }

        $totalBots = DB::table('bot_users')->count();
        $premiumBots = DB::table('bot_users')->where('is_premium', true)->count();

        $this->command->newLine();
        $this->command->info('✅ 350 novos bots adicionados com sucesso!');
        $this->command->info('📊 Total de bots no sistema: ' . $totalBots);
        $this->command->info('👑 Bots premium: ' . $premiumBots . ' (' . round(($premiumBots / $totalBots) * 100, 1) . '%)');
    }
}
