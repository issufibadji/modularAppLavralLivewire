# 05 — Checklist de Go-Live

Este documento cobre a validação final antes de colocar o sistema em produção. Esta é uma reconstrução greenfield — não há sistema legado em execução paralela. O foco é garantir que o produto novo está completo, estável e seguro antes do lançamento.

---

## Infraestrutura Core

Todas as funcionalidades de infraestrutura devem estar implementadas e testadas antes do go-live:

- [ ] Fluxo de autenticação completo: login, logout e recuperação de senha
- [ ] 2FA implementado e middleware `check2fa` ativo nas rotas protegidas
- [ ] Email verification configurado e funcionando
- [ ] RBAC: roles e permissions definidos e seeder executado
- [ ] Sidebar exibe itens corretos por role/permission
- [ ] Audit logging ativo (middleware registrando ações dos usuários)
- [ ] Flash notifications funcionando para success, error, warning e info

---

## Módulos de Negócio

### AgendaAi

- [ ] CRUD de agendamentos completo
- [ ] Fluxo de criação de agendamento testado end-to-end
- [ ] Filtros e paginação na listagem funcionando
- [ ] Cancelamento de agendamento com motivo funcionando
- [ ] Notificação (email ou outro canal) disparada no agendamento

### MercadoPago

- [ ] Integração com API do MercadoPago configurada
- [ ] Fluxo de pagamento testado em sandbox
- [ ] Webhook de confirmação de pagamento recebido e processado
- [ ] Tratamento de falha de pagamento implementado
- [ ] Chaves de API em `.env`, nunca no código

### Report

- [ ] Relatórios geram com dados reais do banco
- [ ] Filtros de data e período funcionando
- [ ] Export (PDF ou CSV) testado com volume real de dados
- [ ] Performance de queries validada (sem timeout em períodos longos)

---

## Qualidade de Código

- [ ] Todos os testes feature passando: `php artisan test`
- [ ] Zero erros de Pint: `./vendor/bin/pint --test`
- [ ] Nenhum `dd()`, `dump()` ou `var_dump()` no código
- [ ] Nenhuma credencial ou secret hardcoded no código
- [ ] Revisão de N+1 queries com Laravel Debugbar desativado em produção

---

## Performance

- [ ] `php artisan optimize` executado
- [ ] Queue worker configurado e testado sob carga
- [ ] Indexes de banco revisados para queries frequentes
- [ ] Assets buildados para produção: `npm run build`
- [ ] Imagens otimizadas (sem assets desnecessariamente pesados)
- [ ] Eager loading aplicado nas listagens (`with()`)

---

## Segurança

- [ ] `.env` não está no repositório (verificar `.gitignore`)
- [ ] `APP_DEBUG=false` configurado em produção
- [ ] `APP_ENV=production` configurado
- [ ] HTTPS enforced (certificado SSL válido)
- [ ] CSRF protection ativa (padrão Laravel — não remover `@csrf`)
- [ ] Rate limiting configurado nas rotas de autenticação
- [ ] Tokens e chaves de API rotacionados para produção (não usar keys de sandbox/dev)
- [ ] Headers de segurança configurados (HSTS, X-Content-Type-Options, etc.)

---

## Deployment

### Passos de deploy

```bash
# 1. Instalar dependências sem pacotes de dev
composer install --no-dev --optimize-autoloader

# 2. Buildar assets para produção
npm ci
npm run build

# 3. Configurar variáveis de ambiente de produção
cp .env.production .env
php artisan key:generate --force  # apenas se necessário

# 4. Executar migrations
php artisan migrate --force

# 5. Cachear configurações, rotas, views e eventos
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Symlink de storage
php artisan storage:link

# 7. Verificar módulos ativos
php artisan module:list
```

### Checklist de deployment

- [ ] `.env.production` configurado com todas as variáveis (DB, MAIL, QUEUE, APP_KEY, etc.)
- [ ] `composer install --no-dev --optimize-autoloader` executado
- [ ] `npm run build` executado e assets no `public/build/`
- [ ] Migrations executadas: `php artisan migrate --force`
- [ ] Todos os caches gerados (config, route, view, event)
- [ ] Storage symlink criado: `php artisan storage:link`
- [ ] Backup do banco de dados realizado antes do deploy
- [ ] Queue worker (Supervisor ou similar) configurado e rodando
- [ ] Cron do Laravel Scheduler configurado: `* * * * * php /path/to/artisan schedule:run`

---

## Configuração de Queue Worker (Supervisor)

Exemplo de configuração Supervisor para o worker de filas:

```ini
[program:modular-app-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/modularApp/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/modularApp/storage/logs/worker.log
stopwaitsecs=3600
```

---

## Validação Final (Smoke Test)

Execute manualmente antes de liberar acesso aos usuários:

- [ ] Login com usuário admin funciona
- [ ] Sidebar exibe todos os menus esperados para o role admin
- [ ] Dashboard carrega sem erros
- [ ] Criar um agendamento de teste end-to-end
- [ ] Simular um pagamento em produção (valor mínimo)
- [ ] Gerar um relatório com dados reais
- [ ] Logout funciona
- [ ] Tentativa de acessar rota protegida sem login redireciona para login
- [ ] Tentativa de acessar rota sem permissão retorna 403

---

## Rollback de Emergência

Se um problema crítico for detectado em produção:

```bash
# Reverter último deploy (se usando zero-downtime)
# 1. Reativar versão anterior via servidor web

# Reverter última migration (se necessário)
php artisan migrate:rollback

# Limpar caches corrompidos
php artisan optimize:clear

# Reiniciar workers
php artisan queue:restart
```

Mantenha sempre o backup do banco realizado **antes** do deploy disponível para restauração.
