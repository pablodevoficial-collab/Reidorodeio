<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Índices de performance para hospedagem compartilhada
     * Otimiza queries críticas do sistema
     */
    public function up(): void
    {
        // =====================================================
        // USERS - Tabela mais acessada
        // =====================================================
        Schema::table('users', function (Blueprint $table) {
            // Login e busca
            if (!$this->hasIndex('users', 'users_email_index')) {
                $table->index('email', 'users_email_index');
            }
            if (!$this->hasIndex('users', 'users_username_index')) {
                $table->index('username', 'users_username_index');
            }
            // Afiliados
            if (!$this->hasIndex('users', 'users_referred_by_index')) {
                $table->index('referred_by_id', 'users_referred_by_index');
            }
            // Status
            if (!$this->hasIndex('users', 'users_status_index')) {
                $table->index('status', 'users_status_index');
            }
        });

        // =====================================================
        // X1 ROOMS - Salas de X1
        // =====================================================
        if (Schema::hasTable('x1_rooms')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                if (!$this->hasIndex('x1_rooms', 'x1_rooms_status_index')) {
                    $table->index('status', 'x1_rooms_status_index');
                }
                if (!$this->hasIndex('x1_rooms', 'x1_rooms_host_index')) {
                    $table->index('host_user_id', 'x1_rooms_host_index');
                }
                if (!$this->hasIndex('x1_rooms', 'x1_rooms_modalidade_index')) {
                    $table->index('modalidade_id', 'x1_rooms_modalidade_index');
                }
                // Índice composto para listagem de salas
                if (!$this->hasIndex('x1_rooms', 'x1_rooms_listing_index')) {
                    $table->index(['status', 'created_at'], 'x1_rooms_listing_index');
                }
                // Índice para salas fechadas
                if (!$this->hasIndex('x1_rooms', 'x1_rooms_closed_index')) {
                    $table->index('closed_at', 'x1_rooms_closed_index');
                }
            });
        }

        // =====================================================
        // X1 PARTICIPANTS - Participantes das salas
        // =====================================================
        if (Schema::hasTable('x1_participants')) {
            Schema::table('x1_participants', function (Blueprint $table) {
                if (!$this->hasIndex('x1_participants', 'x1_part_room_index')) {
                    $table->index('x1_room_id', 'x1_part_room_index');
                }
                if (!$this->hasIndex('x1_participants', 'x1_part_user_index')) {
                    $table->index('user_id', 'x1_part_user_index');
                }
                // Índice composto para buscar participação do usuário
                if (!$this->hasIndex('x1_participants', 'x1_part_user_room_index')) {
                    $table->index(['user_id', 'x1_room_id'], 'x1_part_user_room_index');
                }
            });
        }

        // =====================================================
        // X1 PAYMENTS - Pagamentos X1
        // =====================================================
        if (Schema::hasTable('x1_payments')) {
            Schema::table('x1_payments', function (Blueprint $table) {
                if (!$this->hasIndex('x1_payments', 'x1_pay_status_index')) {
                    $table->index('status', 'x1_pay_status_index');
                }
                if (!$this->hasIndex('x1_payments', 'x1_pay_provider_pref_index')) {
                    $table->index('provider_preference_id', 'x1_pay_provider_pref_index');
                }
                if (!$this->hasIndex('x1_payments', 'x1_pay_user_index')) {
                    $table->index('user_id', 'x1_pay_user_index');
                }
                // Índice para processar pagamentos pendentes
                if (!$this->hasIndex('x1_payments', 'x1_pay_pending_index')) {
                    $table->index(['status', 'created_at'], 'x1_pay_pending_index');
                }
            });
        }

        // =====================================================
        // X1 RESULTS - Resultados
        // =====================================================
        if (Schema::hasTable('x1_results')) {
            Schema::table('x1_results', function (Blueprint $table) {
                if (!$this->hasIndex('x1_results', 'x1_res_room_index')) {
                    $table->index('x1_room_id', 'x1_res_room_index');
                }
                if (!$this->hasIndex('x1_results', 'x1_res_winner_index')) {
                    $table->index('winner_user_id', 'x1_res_winner_index');
                }
                // Índice para histórico de vitórias
                if (!$this->hasIndex('x1_results', 'x1_res_winner_date_index')) {
                    $table->index(['winner_user_id', 'processed_at'], 'x1_res_winner_date_index');
                }
            });
        }

        // =====================================================
        // USER X1 STATS - Estatísticas de usuário
        // =====================================================
        if (Schema::hasTable('user_x1_stats')) {
            Schema::table('user_x1_stats', function (Blueprint $table) {
                if (!$this->hasIndex('user_x1_stats', 'ux1_user_index')) {
                    $table->index('user_id', 'ux1_user_index');
                }
                // Índice para ranking (ordenação por ganhos)
                if (!$this->hasIndex('user_x1_stats', 'ux1_ranking_index')) {
                    $table->index(['total_prize_won', 'wins'], 'ux1_ranking_index');
                }
            });
        }

        // =====================================================
        // AFFILIATES - Afiliados
        // =====================================================
        if (Schema::hasTable('affiliates')) {
            Schema::table('affiliates', function (Blueprint $table) {
                if (!$this->hasIndex('affiliates', 'aff_user_index')) {
                    $table->index('user_id', 'aff_user_index');
                }
                if (!$this->hasIndex('affiliates', 'aff_code_index')) {
                    $table->index('referral_code', 'aff_code_index');
                }
            });
        }

        // =====================================================
        // AFFILIATE COMMISSIONS - Comissões
        // =====================================================
        if (Schema::hasTable('affiliate_commissions')) {
            Schema::table('affiliate_commissions', function (Blueprint $table) {
                if (!$this->hasIndex('affiliate_commissions', 'aff_com_affiliate_index')) {
                    $table->index('affiliate_id', 'aff_com_affiliate_index');
                }
                if (!$this->hasIndex('affiliate_commissions', 'aff_com_status_index')) {
                    $table->index('status', 'aff_com_status_index');
                }
            });
        }

        // =====================================================
        // FANTASY TEAMS - Times Fantasy
        // =====================================================
        if (Schema::hasTable('fantasy_teams')) {
            Schema::table('fantasy_teams', function (Blueprint $table) {
                if (!$this->hasIndex('fantasy_teams', 'ft_user_index')) {
                    $table->index('user_id', 'ft_user_index');
                }
                if (!$this->hasIndex('fantasy_teams', 'ft_league_index')) {
                    $table->index('fantasy_league_id', 'ft_league_index');
                }
                // Índice composto para verificar se usuário já tem time na liga
                if (!$this->hasIndex('fantasy_teams', 'ft_user_league_index')) {
                    $table->index(['user_id', 'fantasy_league_id'], 'ft_user_league_index');
                }
            });
        }

        // =====================================================
        // SUBSCRIPTIONS - Assinaturas
        // =====================================================
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                if (!$this->hasIndex('subscriptions', 'sub_user_index')) {
                    $table->index('user_id', 'sub_user_index');
                }
                if (!$this->hasIndex('subscriptions', 'sub_status_index')) {
                    $table->index('status', 'sub_status_index');
                }
                // Índice para verificar assinaturas ativas (usando data_fim ao invés de expires_at)
                if (!$this->hasIndex('subscriptions', 'sub_active_index')) {
                    $table->index(['user_id', 'status', 'data_fim'], 'sub_active_index');
                }
            });
        }

        // =====================================================
        // COMPETITORS - Competidores
        // =====================================================
        if (Schema::hasTable('competitors')) {
            Schema::table('competitors', function (Blueprint $table) {
                if (!$this->hasIndex('competitors', 'comp_status_index')) {
                    $table->index('status', 'comp_status_index');
                }
            });
        }

        // =====================================================
        // MODALIDADE COMPETITOR GROUPS - Grupos
        // =====================================================
        if (Schema::hasTable('modalidade_competitor_groups')) {
            Schema::table('modalidade_competitor_groups', function (Blueprint $table) {
                if (!$this->hasIndex('modalidade_competitor_groups', 'mcg_modalidade_index')) {
                    $table->index('modalidade_id', 'mcg_modalidade_index');
                }
                if (!$this->hasIndex('modalidade_competitor_groups', 'mcg_status_index')) {
                    $table->index('status', 'mcg_status_index');
                }
            });
        }

        // =====================================================
        // LIVE SCORES - Pontuações ao vivo
        // =====================================================
        if (Schema::hasTable('live_scores')) {
            Schema::table('live_scores', function (Blueprint $table) {
                if (!$this->hasIndex('live_scores', 'ls_event_index')) {
                    $table->index('live_event_id', 'ls_event_index');
                }
                if (!$this->hasIndex('live_scores', 'ls_competitor_index')) {
                    $table->index('competitor_id', 'ls_competitor_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_email_index');
            $table->dropIndexIfExists('users_username_index');
            $table->dropIndexIfExists('users_referred_by_index');
            $table->dropIndexIfExists('users_status_index');
        });

        // X1 Rooms
        if (Schema::hasTable('x1_rooms')) {
            Schema::table('x1_rooms', function (Blueprint $table) {
                $table->dropIndexIfExists('x1_rooms_status_index');
                $table->dropIndexIfExists('x1_rooms_host_index');
                $table->dropIndexIfExists('x1_rooms_modalidade_index');
                $table->dropIndexIfExists('x1_rooms_listing_index');
                $table->dropIndexIfExists('x1_rooms_closed_index');
            });
        }

        // X1 Participants
        if (Schema::hasTable('x1_participants')) {
            Schema::table('x1_participants', function (Blueprint $table) {
                $table->dropIndexIfExists('x1_part_room_index');
                $table->dropIndexIfExists('x1_part_user_index');
                $table->dropIndexIfExists('x1_part_user_room_index');
            });
        }

        // X1 Payments
        if (Schema::hasTable('x1_payments')) {
            Schema::table('x1_payments', function (Blueprint $table) {
                $table->dropIndexIfExists('x1_pay_status_index');
                $table->dropIndexIfExists('x1_pay_provider_pref_index');
                $table->dropIndexIfExists('x1_pay_user_index');
                $table->dropIndexIfExists('x1_pay_pending_index');
            });
        }

        // X1 Results
        if (Schema::hasTable('x1_results')) {
            Schema::table('x1_results', function (Blueprint $table) {
                $table->dropIndexIfExists('x1_res_room_index');
                $table->dropIndexIfExists('x1_res_winner_index');
                $table->dropIndexIfExists('x1_res_winner_date_index');
            });
        }

        // User X1 Stats
        if (Schema::hasTable('user_x1_stats')) {
            Schema::table('user_x1_stats', function (Blueprint $table) {
                $table->dropIndexIfExists('ux1_user_index');
                $table->dropIndexIfExists('ux1_ranking_index');
            });
        }

        // Affiliates
        if (Schema::hasTable('affiliates')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->dropIndexIfExists('aff_user_index');
                $table->dropIndexIfExists('aff_code_index');
            });
        }

        // Affiliate Commissions
        if (Schema::hasTable('affiliate_commissions')) {
            Schema::table('affiliate_commissions', function (Blueprint $table) {
                $table->dropIndexIfExists('aff_com_affiliate_index');
                $table->dropIndexIfExists('aff_com_status_index');
            });
        }

        // Fantasy Teams
        if (Schema::hasTable('fantasy_teams')) {
            Schema::table('fantasy_teams', function (Blueprint $table) {
                $table->dropIndexIfExists('ft_user_index');
                $table->dropIndexIfExists('ft_league_index');
                $table->dropIndexIfExists('ft_user_league_index');
            });
        }

        // Subscriptions
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndexIfExists('sub_user_index');
                $table->dropIndexIfExists('sub_status_index');
                $table->dropIndexIfExists('sub_active_index');
            });
        }

        // Competitors
        if (Schema::hasTable('competitors')) {
            Schema::table('competitors', function (Blueprint $table) {
                $table->dropIndexIfExists('comp_status_index');
            });
        }

        // Modalidade Competitor Groups
        if (Schema::hasTable('modalidade_competitor_groups')) {
            Schema::table('modalidade_competitor_groups', function (Blueprint $table) {
                $table->dropIndexIfExists('mcg_modalidade_index');
                $table->dropIndexIfExists('mcg_status_index');
            });
        }

        // Live Scores
        if (Schema::hasTable('live_scores')) {
            Schema::table('live_scores', function (Blueprint $table) {
                $table->dropIndexIfExists('ls_event_index');
                $table->dropIndexIfExists('ls_competitor_index');
            });
        }
    }

    /**
     * Verifica se um índice existe na tabela
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
