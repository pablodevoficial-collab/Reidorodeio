<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanMissingImages extends Command
{
    protected $signature = 'clean:missing-images';
    protected $description = 'Limpa referências de imagens que não existem mais no storage';

    public function handle()
    {
        $this->info('🔍 Verificando estrutura da tabela competitors...');
        
        // Mostrar colunas disponíveis
        $columns = \Schema::getColumnListing('competitors');
        $this->line('Colunas disponíveis: ' . implode(', ', $columns));
        
        // Verificar se existe coluna de imagem
        $imageColumn = null;
        foreach(['image', 'avatar', 'photo', 'picture', 'foto'] as $col) {
            if (in_array($col, $columns)) {
                $imageColumn = $col;
                break;
            }
        }
        
        if (!$imageColumn) {
            $this->info('❌ Nenhuma coluna de imagem encontrada!');
            return;
        }
        
        $this->info("✅ Usando coluna de imagem: {$imageColumn}");
        
        $competitorsWithMissingImages = DB::table('competitors')
            ->whereNotNull($imageColumn)
            ->get(['id', $imageColumn])
            ->filter(function($competitor) use ($imageColumn) {
                $imagePath = 'competitors/' . $competitor->$imageColumn;
                return !Storage::disk('public')->exists($imagePath);
            });

        $this->info("📊 Competidores com imagens faltando: " . $competitorsWithMissingImages->count());

        if ($competitorsWithMissingImages->count() === 0) {
            $this->info('✅ Nenhuma imagem faltando encontrada!');
            return;
        }

        foreach($competitorsWithMissingImages as $competitor) {
            $this->line("🗑️  ID: {$competitor->id} - Imagem: {$competitor->$imageColumn}");
            
            // Limpar a referência da imagem
            DB::table('competitors')
                ->where('id', $competitor->id)
                ->update([$imageColumn => null]);
            
            $this->info("✅ Imagem limpa para ID: {$competitor->id}");
        }

        $this->info('✅ Limpeza concluída! Os erros 404 devem parar.');
        return 0;
    }
}
