# 03 — UI System

## Objetivo
Criar a nova base visual do sistema com Blade Components, Tailwind, Alpine e Livewire.

## Escopo
- layout base
- navbar
- sidebar
- componentes visuais
- padrões de tela
- modais
- formulários
- tabelas
- feedback visual

## Meta
Definir um design system mínimo para evitar caos visual e retrabalho durante a portabilidade das funcionalidades.

## Tarefas

### 1. Layout base
- [ ] criar `layouts/app.blade.php`
- [ ] definir estrutura principal da página
- [ ] integrar Vite
- [ ] integrar Livewire assets
- [ ] integrar Alpine
- [ ] preparar slots e seções reutilizáveis

### 2. Navegação
- [ ] criar navbar nova
- [ ] criar sidebar nova
- [ ] integrar sidebar dinâmica
- [ ] validar estados ativos
- [ ] validar menu por permissão

### 3. Componentes Blade
- [ ] botão
- [ ] input
- [ ] select
- [ ] textarea
- [ ] checkbox
- [ ] radio
- [ ] card
- [ ] table
- [ ] badge
- [ ] alert
- [ ] modal
- [ ] dropdown
- [ ] pagination wrapper

### 4. Padrões visuais
- [ ] definir espaçamentos
- [ ] definir padrão de tipografia
- [ ] definir padrão de cores
- [ ] definir estados de erro/sucesso
- [ ] definir padrão de ícones
- [ ] definir padrão de loading

### 5. Alpine.js
- [ ] usar Alpine para:
  - [ ] modal
  - [ ] dropdown
  - [ ] tabs
  - [ ] toggles
  - [ ] interações locais
- [ ] evitar lógica de negócio no Alpine

### 6. Livewire
- [ ] definir padrão para listagens
- [ ] definir padrão para formulários
- [ ] definir padrão para filtros
- [ ] definir padrão para paginação
- [ ] definir padrão para estados de loading/empty state

## Regras de uso
- Blade Component: visual reaproveitável
- Alpine: interação local da UI
- Livewire: estado, formulário, listagem, busca, paginação
- Service/Action: regra de negócio

## Critérios de aceite
- layout base pronto
- sidebar pronta
- navbar pronta
- componentes visuais reaproveitáveis
- uma tela exemplo construída só com a nova base
- zero dependência nova de Bootstrap/jQuery

## Riscos
- recriar bagunça visual em cada tela
- misturar regra de negócio no componente
- usar Livewire para tudo, inclusive o que é só UI

## Regra de ouro
Antes de migrar um CRUD, a UI base precisa estar minimamente pronta.
Senão cada módulo vira um Frankenstein diferente.