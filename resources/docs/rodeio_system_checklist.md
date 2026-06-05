# Sistema de Rodeio — Checklist resumido

Objetivo: mapear e resumir o sistema "Rodeio / Transmissão ao vivo" (menus, controllers, models, views, rotas e tabelas relevantes).

- [ ] Menu atualizado
  - [x] Nome do menu principal alterado para `Sistema de rodeio` em `resources/views/admin/partials/sidenav.json`

- [ ] Visão geral das responsabilidades
  - **Função principal:** gerenciar rodeios, modalidades e competidores; oferecer interface de transmissão ao vivo com pontuação e logs.
  - **Áreas cobertas:** CRUD rodeios, CRUD modalidades, gerenciamento de competidores, pontuação em tempo real, logs de pontuação, API AJAX para seleção dinâmica e transmissão.

- [ ] Controllers (localização: `app/Http/Controllers/Admin`) — principais arquivos e responsabilidades
  - `RodeioController.php` — CRUD de rodeios (index, create, store, edit, update, destroy, show). Views: `resources/views/admin/rodeios/*`.
  - `ModalidadeController.php` — CRUD de modalidades; endpoints AJAX: `competitors()`, `attachCompetitors()` (vincular competidores à modalidade).
  - `CompetitorController.php` — CRUD de competidores; upload de foto de perfil; atualização de estatísticas via `updateStats()`.
  - `LiveTransmissionController.php` — lógica da interface de transmissão ao vivo: endpoints JSON para carregar modalidades por rodeio, salvar stream URL, salvar modalidade atual, adicionar pontuação (`addScore`), finalizar modalidade (`finishModalidade`), logs, sumarização e visualizadores.
  - `CompetitorStatsController.php` — páginas de estatísticas, criação de estatísticas se ausentes, endpoints para visualizar logs e resumo.
  - `QueueController.php` — (sistema de filas) gerencia workers/estado das filas (relacionado à transmissão no menu).
  - `DynamicSelectionController.php`, `QuickScoringController.php`, `FantasyLeagueController.php` — interfaces auxiliares ao redor do rodeio (seleção dinâmica, pontuação rápida, fantasy).

- [ ] Models (local: `app/Models`) — principais
  - `Rodeio.php`
    - Tabela: `rodeios` (campo `info` cast para array; campos `logo`, `status_transmissao`, `stream_url`, `modalidade_atual` usados pela transmissão).
    - Relação: `modalidades()` → hasMany `Modalidade`.
  - `Modalidade.php`
    - Tabela: `modalidades` (campos: `rodeio_id`, `nome`, `inicio`, `end`, `tipo_premio`, `valor_premio`, `descricao_premio`, `regras_eliminacao`, `regras_classificacao`, `ordem`, `status`).
    - Relações: `rodeio()` belongsTo `Rodeio`; `competitors()` belongsToMany `Competitor` (pivot `competitor_modalidade`).
  - `Competitor.php`
    - Tabela: `competitors` (campos: `nome`, `biografia`, `foto`, `status`, `nivel`, etc.).
    - Relações: `stats()` hasOne `CompetitorStat`; `modalidades()` belongsToMany `Modalidade`.
  - `CompetitorStat.php` — armazena estatísticas agregadas (vítórias, derrotas, empates, pontuação_total, contadores específicos por ação).
  - `CompetitorScoringLog.php` — logs de pontuação; campos importantes: `competitor_id`, `rodeio_id`, `modalidade_id`, `action_type`, `action_category`, `points`, `total_score_before`, `total_score_after`, `scored_at`, `scored_by`, `metadata`.

- [ ] Views (local: `resources/views/admin`) — páginas relevantes
  - `resources/views/admin/rodeios/*` — `index`, `create`, `edit`, `show` (CRUD Rodeios).
  - `resources/views/admin/modalidades/*` — CRUD Modalidades (index/create/edit/show).
  - `resources/views/admin/competitors/*` — CRUD Competidores (index/create/edit/show).
  - `resources/views/admin/live_transmission/index.blade.php` — interface principal de transmissão ao vivo (player, controles, lista de competidores, log de atividades, modal de pontuação; scripts JS para AJAX e ações de pontuação).
  - `resources/views/admin/competitor_stats/*` — páginas de estatísticas individuais e listagem.
  - `resources/views/admin/quick_scoring/*`, `dynamic-selection`, `fantasy-leagues` — interfaces auxiliares (se implementadas) mencionadas nas rotas.

- [ ] Routes (local: `routes/admin.php`) — endpoints principais (prefixo name `admin.`)
  - `Route::resource('rodeios', 'RodeioController')` — CRUD rodeios
  - `Route::resource('modalidades', 'ModalidadeController')` — CRUD modalidades
  - `admin.competitors.*` — group for competitors (index/create/store/edit/update/destroy/stats)
  - `admin.live_transmission.*` — group: `index`, `modalidades_by_rodeio`, `event_status`, `data`, `stream_url`, `save_modalidade`, `add_score`, `finish_modalidade`, `viewers`, `log`, `competitors_stats`, `update_competitor_stats`, `competitor_scoring_history`, `competitor_stats_summary`.
  - Endpoints AJAX adicionais: `/admin/rodeios/{rodeio}/modalidades` (JSON list)

- [ ] Database (observações)
  - Tabelas relacionadas esperadas (model names):
    - `rodeios`
    - `modalidades`
    - `competitors`
    - `competitor_modalidade` (pivot)
    - `competitor_stats`
    - `competitor_scoring_logs`
  - Nota: o repositório contém *algumas* migrations recentes (ex.: ajustes em `competitors` enum `nivel`), mas não há migrações explícitas com nomes padrões `create_rodeios_table` listadas em `database/migrations` no workspace atual. Verificar histórico de Migrations na branch/backup se necessário.

- [ ] Eventos / Real-time
  - `App\\Events\\LiveTransmissionUpdated` disparado em `LiveTransmissionController::updateEventStatus` para atualizar front-ends via WebSocket/pusher.
  - Possível integração com WebSocket/Queue (controller `WebSocketController.php`, `QueueController.php`) para substituição ou fallback de tempo-real.

- [ ] Tarefas recomendadas / próximos passos
  - [ ] Rodar `php artisan route:list | grep live` para revisar rotas ativas relacionadas ao sistema e confirmar nomes exatos.
  - [ ] Verificar migrations ausentes (buscar `create_*` de `rodeios`, `modalidades`, `competitors` em histórico de commits ou no branch principal). Se faltarem, criar migrações compatíveis.
  - [ ] Garantir que as views referenciadas por controllers existem e estão traduzidas conforme necessário (`@lang` já é usado em muitos textos).
  - [ ] Testar endpoints JSON (ex.: `GET /admin/live-transmission/modalidades-by-rodeio`) com `rodeio_id` válido para confirmar payloads.
  - [ ] Rever permissões/validations e testes: simular fluxo de criar rodeio → criar modalidade → anexar competidores → iniciar transmissão → pontuar competidor.

---

Arquivo gerado automaticamente em `resources/docs/rodeio_system_checklist.md`.
