# Deploy Hostinger

## Fluxo Git recomendado
- O fluxo mais seguro e simples para este projeto e:
  - `VS Code/local -> GitHub -> Hostinger`
- No hPanel, a Hostinger faz clone/pull do repositório. Ela nao funciona como um remote de deploy igual a um servidor VPS com bare repo.
- Para repositório privado:
  - gere a chave SSH no hPanel
  - adicione a chave publica no GitHub como `Deploy key` do repositório
  - use a URL SSH do repositório, por exemplo:
    - `git@github.com:reidorodeiooficial/reidorodeio.com.br.git`

## Fluxo de primeiro setup
- No computador local:

```bash
git init -b main
git remote add origin git@github.com:reidorodeiooficial/reidorodeio.com.br.git
git add .
git commit -m "chore: initial hostinger project import"
git push -u origin main
```

- No hPanel > Git:
  - clique em `Gerar chave SSH`
  - cadastre a chave no GitHub como deploy key
  - crie o repositório apontando para a branch `main`

## Deploy automatico por push

Este repositorio ja possui o workflow:

```text
.github/workflows/deploy-hostinger.yml
```

Ele dispara o webhook da Hostinger automaticamente quando houver `git push` na branch `main`.

### 1) Pegar a URL do webhook na Hostinger

- Acesse hPanel > Website > Gerenciar > Git.
- Confirme que o repositorio esta conectado na branch `main`.
- Clique em `Auto Deployment`.
- Copie a `Webhook URL` gerada pela Hostinger.

### 2) Salvar a URL como segredo no GitHub

- No GitHub, abra o repositorio.
- Va em Settings > Secrets and variables > Actions > New repository secret.
- Nome do segredo:

```text
HOSTINGER_WEBHOOK_URL
```

- Valor: cole a Webhook URL copiada da Hostinger.

### 3) Usar no dia a dia

No computador local:

```bash
git add .
git commit -m "mensagem do ajuste"
git push origin main
```

Depois do push:

- o GitHub Actions executa `Deploy Hostinger`;
- o workflow chama a URL `HOSTINGER_WEBHOOK_URL`;
- a Hostinger faz o pull/deploy da branch configurada.

Tambem e possivel disparar manualmente pelo GitHub em Actions > Deploy Hostinger > Run workflow.

### Alternativa sem GitHub Actions

Se preferir nao usar workflow, voce pode cadastrar a Webhook URL da Hostinger diretamente em:

- GitHub > Settings > Webhooks > Add webhook
- Payload URL: Webhook URL da Hostinger
- Content type: `application/json`
- Events: `Just the push event`

Nesse modo, o GitHub chama a Hostinger direto em todo push. O workflow deste repositorio fica como opcao mais controlada e com historico no menu Actions.

## Importante sobre o diretorio
- Se o site ja esta rodando com arquivos dentro de `public_html`, nao clone o Git por cima dessa pasta sem backup.
- O ideal neste projeto e:
  - clonar o Laravel completo fora do `public_html`, por exemplo `/home/SEU_USUARIO/rei`
  - manter apenas o front publico em `public_html`
- Se usar o Git da Hostinger em uma pasta ja existente, ela precisa estar vazia.

## 1) Estrutura recomendada
- Suba todo o projeto para uma pasta fora do `public_html`, por exemplo:
  - `/home/SEU_USUARIO/rei`
- Copie somente o conteúdo de `public/` para:
  - `/home/SEU_USUARIO/public_html`

## 2) `public_html/index.php`
- O `public/index.php` agora tenta localizar automaticamente o app Laravel em uma pasta irmã do `public_html`.
- Se o app estiver em `/home/SEU_USUARIO/rei`, normalmente não precisa editar nada.
- Se a detecção automática falhar, adicione em `public_html/.htaccess`:

```apacheconf
SetEnv LARAVEL_BASE_PATH /home/SEU_USUARIO/rei
```

## 3) Ambiente de produção
- Use `/.env.hostinger` como base para o `.env` do servidor.
- Se preferir montar manualmente, use `/.env.hostinger.example`.
- O deploy foi preparado para gravar uploads diretamente em `public_html/storage` com:
  - `FILESYSTEM_DISK=public`
  - `PUBLIC_DISK_ROOT=../public_html/storage`

## 4) Comandos no SSH
- Dentro de `/home/SEU_USUARIO/rei`:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

- Não rode `php artisan storage:link` nesse layout.
- O sistema foi ajustado para usar `public_html/storage` diretamente.

## 5) Permissões
- Garanta escrita em:
  - `storage/`
  - `bootstrap/cache/`
  - `public_html/storage/`

## 6) Cron obrigatório
- No hPanel > Cron Jobs:

```bash
php /home/SEU_USUARIO/rei/artisan schedule:run >> /dev/null 2>&1
```

- Frequência: 1 vez por minuto.

## 7) Webhooks e OAuth
- URLs de produção esperadas:
  - `https://www.reidorodeio.com.br/api/webhooks/subscription`
  - `https://www.reidorodeio.com.br/user/social/google/callback`
  - `https://www.reidorodeio.com.br/user/social/facebook/callback`
  - `https://www.reidorodeio.com.br/user/social/apple/callback`

## 8) Checklist final
- Login e registro
- Login social
- Upload e exibição de banners/rodeios/competidores
- PIX e callbacks do Mercado Pago
- Fantasy / X1
- Painel admin
