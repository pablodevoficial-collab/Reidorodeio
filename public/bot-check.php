<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🤖 Teste Sistema de Bots - Rei do Rodeio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.1rem;
        }
        .status-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .status-item:last-child { margin-bottom: 0; }
        .status-label {
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-value {
            font-size: 1.3rem;
            font-weight: 700;
        }
        .status-value.success { color: #10b981; }
        .status-value.error { color: #ef4444; }
        .status-value.warning { color: #f59e0b; }
        .emoji { font-size: 1.5rem; }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.3s ease;
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 Sistema de Bots</h1>
            <p>Rei do Rodeio - Status da Instalação</p>
        </div>

        <?php
        // Conectar ao banco
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Schema;

        // Verificações
        $checks = [];

        // 1. Arquivo bots.json
        $botsFile = storage_path('app/bots.json');
        $botsCount = 0;
        if (file_exists($botsFile)) {
            $data = json_decode(file_get_contents($botsFile), true);
            $botsCount = is_array($data) ? count($data) : 0;
            $checks['bots_file'] = [
                'status' => $botsCount > 0 ? 'success' : 'warning',
                'label' => '📁 Arquivo bots.json',
                'value' => $botsCount > 0 ? "$botsCount pessoas" : 'Vazio'
            ];
        } else {
            $checks['bots_file'] = [
                'status' => 'error',
                'label' => '📁 Arquivo bots.json',
                'value' => 'Não encontrado'
            ];
        }

        // 2. Coluna users.is_bot
        $checks['users_column'] = [
            'status' => Schema::hasColumn('users', 'is_bot') ? 'success' : 'error',
            'label' => '👤 Coluna users.is_bot',
            'value' => Schema::hasColumn('users', 'is_bot') ? '✅ Existe' : '❌ Não existe'
        ];

        // 3. Coluna x1_rooms.is_bot_room
        $checks['x1_column'] = [
            'status' => Schema::hasColumn('x1_rooms', 'is_bot_room') ? 'success' : 'error',
            'label' => '⚔️ Coluna x1_rooms.is_bot_room',
            'value' => Schema::hasColumn('x1_rooms', 'is_bot_room') ? '✅ Existe' : '❌ Não existe'
        ];

        // 4. Coluna fantasy_leagues.is_bot_league
        $checks['fantasy_column'] = [
            'status' => Schema::hasColumn('fantasy_leagues', 'is_bot_league') ? 'success' : 'error',
            'label' => '🏆 Coluna fantasy_leagues.is_bot_league',
            'value' => Schema::hasColumn('fantasy_leagues', 'is_bot_league') ? '✅ Existe' : '❌ Não existe'
        ];

        // 5. Controller existe
        $controllerPath = app_path('Http/Controllers/Admin/BotManagementController.php');
        $checks['controller'] = [
            'status' => file_exists($controllerPath) ? 'success' : 'error',
            'label' => '🎮 Controller',
            'value' => file_exists($controllerPath) ? '✅ Instalado' : '❌ Não encontrado'
        ];

        // 6. View existe
        $viewPath = resource_path('views/admin/users/bots.blade.php');
        $checks['view'] = [
            'status' => file_exists($viewPath) ? 'success' : 'error',
            'label' => '🎨 View Admin',
            'value' => file_exists($viewPath) ? '✅ Instalada' : '❌ Não encontrada'
        ];

        // 7. Rodeios disponíveis
        $rodeiosCount = DB::table('rodeios')->count();
        $checks['rodeios'] = [
            'status' => $rodeiosCount > 0 ? 'success' : 'warning',
            'label' => '🎪 Rodeios cadastrados',
            'value' => $rodeiosCount > 0 ? "$rodeiosCount rodeio(s)" : 'Nenhum'
        ];

        // 8. Bots criados
        $botsUsersCount = DB::table('users')->where('is_bot', true)->count();
        $checks['bots_created'] = [
            'status' => $botsUsersCount > 0 ? 'success' : 'warning',
            'label' => '🤖 Bots criados',
            'value' => $botsUsersCount > 0 ? "$botsUsersCount usuários" : 'Nenhum ainda'
        ];

        // Verificar se tudo está OK
        $allGood = true;
        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                $allGood = false;
                break;
            }
        }
        ?>

        <div class="status-card">
            <h2 style="margin-bottom: 20px; color: #333;">
                <?php if ($allGood): ?>
                    ✅ Sistema Pronto para Usar!
                <?php else: ?>
                    ⚠️ Atenção: Alguns itens precisam de atenção
                <?php endif; ?>
            </h2>

            <?php foreach ($checks as $check): ?>
                <div class="status-item">
                    <span class="status-label">
                        <?= $check['label'] ?>
                    </span>
                    <span class="status-value <?= $check['status'] ?>">
                        <?= $check['value'] ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($allGood): ?>
            <div style="text-align: center;">
                <a href="/admin/users/bots" class="btn">
                    🚀 Acessar Painel de Bots
                </a>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>Rei do Rodeio © 2026 - Sistema de Bots v1.0</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">
                <?= $allGood ? '🎉 Tudo funcionando perfeitamente!' : '⚠️ Verifique os itens acima' ?>
            </p>
        </div>
    </div>
</body>
</html>
