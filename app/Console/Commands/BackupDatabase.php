<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Realiza backup automático do banco de dados';

    public function handle()
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $filename = 'backup_' . $db . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.sql';
        $path = storage_path('app/backup/' . $filename);

        $command = sprintf('mysqldump -h%s -u%s -p%s %s > "%s"', $host, $user, $pass, $db, $path);
        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Backup realizado com sucesso: ' . $filename);
        } else {
            $this->error('Erro ao realizar backup.');
        }
    }
}
