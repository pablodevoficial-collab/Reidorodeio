<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestQueueRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-queue-routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando rotas de queues...');

        // Testar se as rotas existem
        $routes = [
            'admin.queues.index',
            'admin.queues.status',
            'admin.queues.start_worker',
            'admin.queues.pause_worker',
            'admin.queues.clear',
            'admin.queues.test_job'
        ];

        foreach ($routes as $routeName) {
            $route = app('router')->getRoutes()->getByName($routeName);
            if ($route) {
                $this->info("✅ Rota {$routeName} encontrada: " . $route->uri());
            } else {
                $this->error("❌ Rota {$routeName} não encontrada");
            }
        }

        // Testar controller
        try {
            $controller = app(\App\Http\Controllers\Admin\QueueController::class);
            $this->info('✅ QueueController instanciado com sucesso');

            // Testar método getStatus
            if (method_exists($controller, 'getStatus')) {
                $this->info('✅ Método getStatus existe');
            } else {
                $this->error('❌ Método getStatus não existe');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro ao instanciar QueueController: ' . $e->getMessage());
        }
    }
}
