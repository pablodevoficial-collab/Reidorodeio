<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$rodeios = \App\Models\Rodeio::orderBy('id', 'desc')->limit(5)->get();
echo "Recent Rodeios:\n";
foreach($rodeios as $r) {
    echo sprintf("  ID:%d | %s | Status: %s\n", $r->id, $r->nome, $r->status_transmissao ?? 'NULL');
}

// Check CompetitorContextStat samples
echo "\nCompetitorContextStat samples:\n";
$stats = \App\Models\CompetitorContextStat::limit(5)->get();
foreach($stats as $s) {
    echo sprintf("  Competitor:%d | Rodeio:%d | Modalidade:%d | Divisao:'%s' | Pontuacao:%d\n", 
        $s->competitor_id, $s->rodeio_id, $s->modalidade_id, $s->divisao, $s->pontuacao_total);
}
