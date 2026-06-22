<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('affiliate_tiers', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['bronze', 'silver', 'gold', 'diamond'])->unique();
            
            // Requisitos
            $table->integer('min_referrals')->comment('Mínimo de indicações ativas para atingir tier');
            
            // Comissões
            $table->decimal('x1_commission_percent', 5, 2)->comment('% sobre taxa X1 (20-35%)');
            $table->decimal('fantasy_commission_percent', 5, 2)->comment('% sobre prêmio fantasy (5-10%)');
            
            // Benefícios extras (JSON)
            $table->json('benefits')->nullable()->comment('Badge, nome, bônus, etc');
            
            $table->timestamps();
        });
        
        // Inserir tiers padrão (20%, 25%, 30%, 35% X1 | 5%, 7%, 8%, 10% Fantasy)
        DB::table('affiliate_tiers')->insert([
            [
                'tier' => 'bronze',
                'name' => 'Iniciante',
                'min_referrals' => 0,
                'x1_commission_percent' => 20.0,
                'fantasy_commission_percent' => 5.0,
                'benefits' => json_encode([
                    'emoji' => '🤠',
                    'name' => 'Iniciante',
                    'color' => '#cd7f32',
                    'description' => 'Comece sua jornada no rodeio'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tier' => 'silver',
                'name' => 'Promotor',
                'min_referrals' => 10,
                'x1_commission_percent' => 25.0,
                'fantasy_commission_percent' => 7.0,
                'benefits' => json_encode([
                    'emoji' => '🏆',
                    'name' => 'Promotor',
                    'color' => '#c0c0c0',
                    'description' => 'Conquiste seu primeiro troféu',
                    'custom_link' => true
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tier' => 'gold',
                'name' => 'Embaixador',
                'min_referrals' => 50,
                'x1_commission_percent' => 30.0,
                'fantasy_commission_percent' => 8.0,
                'benefits' => json_encode([
                    'emoji' => '👑',
                    'name' => 'Embaixador',
                    'color' => '#ffd700',
                    'description' => 'Elite do programa de afiliados',
                    'priority_support' => true,
                    'advanced_dashboard' => true
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tier' => 'diamond',
                'name' => 'Lenda',
                'min_referrals' => 200,
                'x1_commission_percent' => 35.0,
                'fantasy_commission_percent' => 10.0,
                'benefits' => json_encode([
                    'emoji' => '💎',
                    'name' => 'Lenda',
                    'color' => '#b9f2ff',
                    'description' => 'O topo absoluto do programa',
                    'featured' => true,
                    'vip_status' => true
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_tiers');
    }
};
