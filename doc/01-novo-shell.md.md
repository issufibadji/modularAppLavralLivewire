# 01 — Novo Shell

## Objetivo
Criar uma nova base limpa em Laravel 12, mantendo arquitetura modular e preparando a stack nova:
- Laravel 12
- Livewire 3
- Alpine.js
- Tailwind CSS
- Vite
- MySQL
- nwidart/laravel-modules

## Meta
Parar de brigar com upgrade in-place do legado e criar uma fundação limpa para portar funcionalidades.

## Escopo
- criar projeto novo
- configurar ambiente
- instalar pacotes-base
- configurar estrutura modular
- definir convenções de namespace, módulos e providers

## Tarefas

### 1. Criar novo projeto
- [ ] criar novo projeto Laravel 12
- [ ] configurar `.env`
- [ ] conectar MySQL
- [ ] validar conexão com banco
- [ ] configurar timezone, locale e app name

### 2. Instalar stack principal
- [ ] instalar Livewire 3
- [ ] instalar Tailwind
- [ ] configurar Alpine.js
- [ ] configurar Vite
- [ ] instalar `nwidart/laravel-modules`
- [ ] instalar `spatie/laravel-permission`
- [ ] instalar pacote de auditoria
- [ ] instalar pacote 2FA compatível com Laravel 12

### 3. Definir convenções
- [ ] padronizar nomes de módulos
- [ ] padronizar PSR-4
- [ ] padronizar namespace
- [ ] padronizar estrutura de pastas dos módulos
- [ ] padronizar nomenclatura entre `Mercadopago` vs `MercadoPago`
- [ ] decidir se módulos serão locais em `/Modules` ou pacotes externos, nunca os dois ao mesmo tempo

### 4. Configurar estrutura base
- [ ] criar layout principal provisório
- [ ] configurar providers
- [ ] configurar aliases e middlewares
- [ ] configurar autenticação base
- [ ] preparar base para UUID em usuários

## Estrutura sugerida
```text
app/
bootstrap/
config/
database/
Modules/
resources/
routes/