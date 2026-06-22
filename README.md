# 🤠 REI DO RODEIO

> Plataforma completa de Fantasy Rodeio, Salas X1 e Estatísticas para o mundo do rodeio brasileiro.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Mercado Pago](https://img.shields.io/badge/Mercado%20Pago-Integrado-00B1EA?style=flat-square)

---

## 📋 Índice

- [Visão Geral](#-visão-geral)
- [Funcionalidades](#-funcionalidades)
- [Stack Tecnológica](#-stack-tecnológica)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Módulos do Sistema](#-módulos-do-sistema)
- [API Reference](#-api-reference)
- [Painel Administrativo](#-painel-administrativo)
- [Arquitetura](#-arquitetura)

---

## 🎯 Visão Geral

O **Rei do Rodeio** é uma plataforma inovadora que conecta fãs de rodeio através de:

- **Salas X1**: Desafios 1v1 ou dupla vs dupla com escolha de competidores
- **Fantasy Rodeio**: Monte seu time de competidores e dispute rankings
- **Transmissões ao Vivo**: Acompanhe rodeios em tempo real com scoring
- **Sistema de Afiliados**: Ganhe comissões indicando novos usuários
- **Planos Premium**: Acesso a recursos exclusivos e estatísticas avançadas

---

## ✨ Funcionalidades

### 🎮 Salas X1
- Criação de salas com valores de R$ 0,50 a R$ 10.000
- Modalidades: Individual (1v1) ou Duplas (2v2)
- Seleção de competidores/grupos por modalidade
- Pagamento via Mercado Pago (PIX, Cartão)
- Sistema de ranking ELO
- Histórico completo de batalhas
- Prêmio automático calculado (entrada x2 - taxa)
- Controle de pagamentos PIX pelo admin

### 🏆 Fantasy Rodeio
- Ligas por rodeio/evento
- Montagem de times com budget
- Pontuação em tempo real
- Rankings e premiações
- Estatísticas detalhadas

### 📺 Transmissões ao Vivo
- Painel de scoring em tempo real
- Desqualificação de competidores
- Auto-resultado para X1 (quando competidor é desqualificado)
- Log de eventos

### 👥 Sistema de Afiliados
- Link único de indicação (`/r/{codigo}`)
- Cookie de 30 dias para rastreamento
- Níveis de comissão:
  - 🥉 Bronze: 20% (0-9 indicações)
  - 🥈 Prata: 25% (10-24 indicações)
  - 🥇 Ouro: 30% (25-49 indicações)
  - 💎 Diamante: 35% (50+ indicações)
- Comissão calculada sobre o lucro da plataforma em X1
- Dashboard de ganhos e indicações

### 💎 Planos Premium
- Trial gratuito de 7 dias
- Assinatura mensal via Mercado Pago
- Benefícios:
  - Taxa reduzida em X1
  - Rankings completos
  - Estatísticas avançadas
  - Badge exclusivo

### 🔔 Notificações
- Web Push Notifications
- Alertas de resultados X1
- Notificações de pagamento

---

## 🛠 Stack Tecnológica

### Backend
| Tecnologia | Versão | Uso |
|------------|--------|-----|
| PHP | 8.2+ | Runtime |
| Laravel | 11.x | Framework |
| MySQL | 8.0 | Database |
| Laravel Sanctum | 4.0 | API Auth |
| Laravel Socialite | 5.6 | OAuth (Google) |
| Pusher | 7.2 | WebSockets |

### Integrações
| Serviço | Uso |
|---------|-----|
| Mercado Pago | Pagamentos (PIX, Cartão, Assinaturas) |
| Twilio | SMS |
| SendGrid | Email |
| Mailjet | Email alternativo |
| MessageBird | SMS alternativo |
| Web Push | Notificações PWA |

### Frontend
- Blade Templates
- Vanilla JavaScript
- CSS Custom Properties
- Font Awesome Icons
- PWA Ready

---

## 📁 Estrutura do Projeto

```
rei/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Painel administrativo
│   │   │   ├── Api/             # APIs REST
│   │   │   ├── User/            # Área do usuário
│   │   │   └── Gateway/         # Gateways de pagamento
│   │   └── Middleware/
│   ├── Models/                   # 65+ models
│   ├── Services/                 # Serviços de negócio
│   │   ├── AffiliateCommissionService.php
│   │   ├── MercadoPagoService.php
│   │   ├── X1RoomService.php
│   │   ├── X1StatsService.php
│   │   ├── FantasyRankingService.php
│   │   └── PushNotificationService.php
│   ├── Events/                   # Eventos do sistema
│   └── Jobs/                     # Processamento assíncrono
├── database/
│   └── migrations/               # 100+ migrations
├── resources/
│   └── views/
│       ├── admin/                # Views do admin
│       └── frontend/             # Views públicas
│           └── partials/
│               ├── inicial_hub.blade.php
│               ├── inicial_x1_content.blade.php
│               ├── inicial_perfil_content.blade.php
│               └── inicial_fantasy_content.blade.php
├── routes/
│   ├── web.php                   # Rotas web
│   ├── api.php                   # Rotas API
│   ├── admin.php                 # Rotas admin
│   └── user.php                  # Rotas usuário
└── public/
    └── assets/                   # Assets estáticos
```

---

## 🚀 Instalação

### Requisitos
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ (para assets)

### Passos

```bash
# 1. Clonar repositório
git clone https://github.com/seu-usuario/rei-do-rodeio.git
cd rei-do-rodeio

# 2. Instalar dependências PHP
composer install

# 3. Copiar arquivo de ambiente
cp .env.example .env

# 4. Gerar chave da aplicação
php artisan key:generate

# 5. Configurar banco de dados no .env
# DB_DATABASE=rei_do_rodeio
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Executar migrations
php artisan migrate

# 7. Executar seeders (opcional)
php artisan db:seed

# 8. Instalar dependências Node (se necessário)
npm install && npm run build

# 9. Iniciar servidor
php artisan serve
```

---

## ⚙️ Configuração

### Variáveis de Ambiente Principais

```env
# Aplicação
APP_NAME="Rei do Rodeio"
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rei_do_rodeio
DB_USERNAME=root
DB_PASSWORD=

# Mercado Pago
MERCADO_PAGO_ACCESS_TOKEN=your_access_token
MERCADO_PAGO_PUBLIC_KEY=your_public_key

# Pusher (WebSockets)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=sa1

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key

# Web Push (VAPID)
VAPID_PUBLIC_KEY=your_public_key
VAPID_PRIVATE_KEY=your_private_key
```

---

## 📦 Módulos do Sistema

### 1. Salas X1 (`X1RoomInstance`)

**Fluxo completo:**
1. Usuário cria sala → Seleciona modalidade, competidor, valor
2. Pagamento do host → Sala fica "open" aguardando oponente
3. Oponente entra → Seleciona seu competidor/grupo
4. Pagamento do oponente → Sala fica "in_progress"
5. Durante transmissão ao vivo:
   - Se competidor é desqualificado → Auto-resultado
   - Admin declara vencedor manualmente
6. Resultado processado → Estatísticas atualizadas
7. Comissão de afiliado (se aplicável)
8. Admin marca PIX como feito → Usuário vê em "Recebido"

**Models envolvidos:**
- `X1RoomInstance` - Sala
- `X1Participant` - Participantes
- `X1Payment` - Pagamentos
- `X1Result` - Resultados
- `UserX1Stat` - Estatísticas do usuário

### 2. Fantasy (`FantasyLeague`)

**Fluxo:**
1. Admin cria liga vinculada a rodeio
2. Usuários montam times com budget
3. Pagamento da entrada
4. Transmissão ao vivo atualiza pontos
5. Ranking calculado e premiação

**Models:**
- `FantasyLeague` - Liga
- `FantasyTeam` - Times dos usuários
- `FantasyScore` - Pontuações
- `FantasyPayment` - Pagamentos

### 3. Transmissões ao Vivo (`LiveTransmission`)

**Recursos:**
- Scoring em tempo real
- Desqualificação de competidores
- Auto-resultado X1
- Histórico de ações

### 4. Sistema de Afiliados (`Affiliate`)

**Tabelas:**
- `affiliates` - Dados do afiliado
- `affiliate_tiers` - Níveis (Bronze/Prata/Ouro/Diamante)
- `affiliate_commissions` - Comissões geradas
- `affiliate_payments` - Pagamentos realizados

**Regras de comissão X1:**
- Comissão paga apenas quando o **indicado vence**
- Cálculo: `lucro_plataforma × percentual_tier`
- Lucro = `(valor_entrada × 2) - premio_total`

### 5. Assinaturas Premium (`Subscription`)

**Integração Mercado Pago:**
- Checkout Pro para assinaturas
- Webhooks para status
- Trial de 7 dias
- Cancelamento com reembolso proporcional

---

## 🔌 API Reference

### Endpoints Públicos

```
GET  /api/realtime/stats              # Estatísticas gerais
GET  /api/realtime/rankings           # Rankings por modalidade
GET  /api/realtime/rodeios            # Lista de rodeios
GET  /api/x1/rankings/top30           # Top 30 X1
GET  /api/fantasy/leagues             # Ligas ativas
GET  /api/subscriptions/plans         # Planos disponíveis
```

### Endpoints Autenticados

```
# X1
POST /api/x1                          # Criar sala
POST /api/x1/{id}/join                # Entrar na sala
GET  /api/x1/stats/me                 # Minhas estatísticas
GET  /api/x1/history/me               # Meu histórico
GET  /api/x1/active/me                # Minhas salas ativas

# Fantasy
POST /api/fantasy/leagues/{id}/teams  # Criar time
GET  /api/fantasy/leagues/{id}/teams/me # Meu time

# Assinaturas
GET  /api/subscriptions/status        # Status da assinatura
POST /api/subscriptions/subscribe     # Assinar
POST /api/subscriptions/cancel        # Cancelar
```

### Webhooks

```
POST /api/webhooks/subscription       # Mercado Pago subscription
POST /ipn/mercadopago                 # Pagamentos gerais
```

---

## 🎛 Painel Administrativo

Acesso: `/admin`

### Módulos Disponíveis

| Módulo | Rota | Descrição |
|--------|------|-----------|
| Dashboard | `/admin/dashboard` | Visão geral |
| Usuários | `/admin/users` | Gerenciar usuários |
| Salas X1 | `/admin/x1` | Ver/fechar salas, marcar PIX |
| Fantasy | `/admin/fantasy-leagues` | Gerenciar ligas |
| Rodeios | `/admin/rodeios` | Cadastrar eventos |
| Modalidades | `/admin/modalidades` | Tipos de prova |
| Competidores | `/admin/competitors` | Cadastro de atletas |
| Transmissão | `/admin/live-transmission` | Scoring ao vivo |
| Afiliados | `/admin/affiliates` | Gerenciar programa |
| Assinaturas | `/admin/subscriptions` | Planos e assinantes |
| Configurações | `/admin/settings` | Config. gerais |

### Funcionalidades X1 Admin

- Ver detalhes da sala
- Ver participantes e PIX
- Marcar prêmio como pago ("PGT. Feito")
- Encerrar sala manualmente
- Ver resultado e vencedor

---

## 🏗 Arquitetura

### Princípios

1. **Backend-First**: Toda lógica no Laravel
2. **API-Driven**: Frontend consome APIs REST
3. **Snapshots**: Rankings pré-calculados, não queries dinâmicas
4. **Acumuladores**: Estatísticas incrementais
5. **Event-Driven**: Jobs para processamento assíncrono

### Fluxo de Dados

```
[Usuário] → [Frontend] → [API Controller] → [Service] → [Model] → [Database]
                                    ↓
                              [Job/Event]
                                    ↓
                            [Cache/Snapshot]
```

### Services Principais

| Service | Responsabilidade |
|---------|------------------|
| `X1RoomService` | Criar/gerenciar salas X1 |
| `X1StatsService` | Estatísticas e rankings X1 |
| `AffiliateCommissionService` | Processar comissões |
| `MercadoPagoService` | Integração pagamentos |
| `FantasyRankingService` | Rankings fantasy |
| `PushNotificationService` | Web Push |
| `SubscriptionService` | Assinaturas premium |

---

## 📱 PWA

O sistema é PWA-ready:

- `manifest.json` configurado
- Service Worker para cache
- Ícones em múltiplas resoluções
- Instalável em dispositivos móveis

---

## 🔐 Segurança

- CSRF Protection em todas as rotas web
- Sanctum para API authentication
- Rate limiting em endpoints sensíveis
- Validação de pagamentos via webhook
- Sessão única por usuário (logout automático em outro device)

---

## 🚀 Deploy - Hostinger Shared Hosting

### Otimizações Implementadas

O sistema foi otimizado para rodar em hosting compartilhada:

1. **Database Indexes** - Índices em todas as tabelas críticas
2. **Cache Agressivo** - CacheService com file cache
3. **Rate Limiting** - Proteção contra abuso de requests
4. **Cron Jobs** - Processamento via cron (sem queue workers)

### Configuração de Cron

Configure no cPanel/Hostinger uma tarefa cron executando a cada minuto:

```bash
* * * * * cd /home/user/rei && php artisan schedule:run >> /dev/null 2>&1
```

No layout recomendado, o app Laravel fica fora do `public_html` e apenas o conteúdo de `public/` vai para `public_html`.
Para uploads públicos em shared hosting, use `FILESYSTEM_DISK=public` com `PUBLIC_DISK_ROOT=../public_html/storage`.

### Jobs Agendados (routes/console.php)

| Comando | Frequência | Descrição |
|---------|------------|-----------|
| `x1:process-payments` | A cada minuto | Processa pagamentos X1 pendentes |
| `x1:clean-expired` | A cada minuto | Limpa salas X1 expiradas |
| `cache:update-rankings` | A cada 5 min | Atualiza rankings e aquece cache |
| `WarmFantasyRankingCache` | A cada 5 min | Mantém cache Fantasy aquecido |
| `ExpireTrialSubscriptions` | A cada hora | Expira trials vencidos |
| `ProcessSubscriptionBilling` | 08:00 diário | Processa renovações de assinatura |
| `backup:database` | 03:00 diário | Backup do banco de dados |
| `cache:warmup` | 05:00 diário | Aquece cache completo |

### Rate Limits (routes/api.php)

| Limiter | Limite | Rotas |
|---------|--------|-------|
| `api-general` | 60/min | Todas as rotas de API |
| `api-heavy` | 20/min | Operações pesadas |
| `x1-create` | 5/min | Criação de salas X1 |
| `x1-join` | 10/min | Entrada em salas X1 |
| `payment` | 10/min | Operações de pagamento |

### Comandos Úteis

```bash
# Aquecer cache após deploy
php artisan cache:warmup --clear

# Verificar pagamentos manualmente
php artisan x1:process-payments --dry-run

# Atualizar rankings manualmente
php artisan cache:update-rankings --warm

# Ver schedule programado
php artisan schedule:list
```

---

## 📊 Banco de Dados

### Tabelas Principais (65+)

**Usuários & Auth:**
- `users`, `admins`, `password_resets`, `user_logins`

**X1:**
- `x1_room_instances`, `x1_participants`, `x1_payments`, `x1_results`, `user_x1_stats`, `x1_ranking_snapshots`

**Fantasy:**
- `fantasy_leagues`, `fantasy_teams`, `fantasy_scores`, `fantasy_payments`

**Competidores:**
- `competitors`, `modalidade_competitor_groups`, `modalidade_competitor_group_members`, `competitor_stats`

**Afiliados:**
- `affiliates`, `affiliate_tiers`, `affiliate_commissions`, `affiliate_payments`

**Assinaturas:**
- `subscriptions`, `subscription_plans`

**Rodeios:**
- `rodeios`, `modalidades`, `live_events`, `live_scores`

---

## 🤝 Contribuição

1. Fork o projeto
2. Crie sua branch (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Add nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request

---

## 📝 Licença

Este projeto é proprietário. Todos os direitos reservados.

---

## 📞 Suporte

- Email: suporte@reidorodeio.com
- WhatsApp: (XX) XXXXX-XXXX

---

**Desenvolvido com ❤️ para o mundo do rodeio brasileiro** 🤠🇧🇷
