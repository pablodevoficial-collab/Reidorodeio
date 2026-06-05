<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PushNotificationService;

class SendPushNotification extends Command
{
    protected $signature = 'push:send 
                            {--title= : Título da notificação} 
                            {--body= : Corpo da mensagem} 
                            {--url= : URL de destino} 
                            {--user= : ID do usuário específico (deixe vazio para todos)}
                            {--image= : URL da imagem}';

    protected $description = 'Enviar push notification para usuários';

    public function handle(PushNotificationService $pushService)
    {
        $title = $this->option('title');
        $body = $this->option('body');
        $url = $this->option('url') ?? url('/');
        $userId = $this->option('user');
        $image = $this->option('image');

        // Validar campos obrigatórios
        if (!$title) {
            $title = $this->ask('Título da notificação');
        }

        if (!$body) {
            $body = $this->ask('Mensagem');
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => asset('assets/images/logo_icon/logo.png'),
            'badge' => asset('assets/images/logo_icon/favicon.png'),
        ];

        if ($image) {
            $payload['image'] = $image;
        }

        $this->info('📤 Enviando push notification...');
        $this->newLine();

        if ($userId) {
            $this->info("🎯 Destinatário: Usuário ID {$userId}");
            $results = $pushService->sendToUser((int)$userId, $payload);
        } else {
            $this->info("🎯 Destinatário: TODOS OS USUÁRIOS");
            if (!$this->confirm('Tem certeza que deseja enviar para todos?', false)) {
                $this->warn('❌ Operação cancelada');
                return 0;
            }
            $results = $pushService->sendToAll($payload);
        }

        $this->newLine();
        $this->info('✅ Resultado:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Enviadas com sucesso', $results['success']],
                ['Falharam', $results['failed']],
                ['Expiradas/Removidas', $results['expired']],
            ]
        );

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->warn('⚠️  Erros encontrados:');
            foreach ($results['errors'] as $error) {
                $this->line("  - Subscription {$error['subscription_id']}: {$error['error']}");
            }
        }

        return 0;
    }
}
