# 00 — Contexto Geral do Projeto

Este documento descreve o propósito, a arquitetura, o stack tecnológico e as regras de desenvolvimento do projeto. É o ponto de entrada obrigatório para qualquer engenheiro que vá contribuir com o sistema. Todas as decisões arquiteturais relevantes estão registradas aqui.

---
## Regra de Organização da Aplicação

A aplicação é dividida em duas camadas principais:

### Core
Responsável por tudo que é global, compartilhado e estrutural para o funcionamento do sistema, incluindo:
- layout base
- navegação global
- autenticação
- usuários
- permissões
- componentes reutilizáveis
- regras transversais

### Módulos
Responsáveis por funcionalidades de negócio específicas, carregadas conforme necessidade do domínio.  
Cada módulo deve conter apenas:
- regras de negócio próprias
- interface específica
- rotas próprias
- componentes Livewire próprios
- integrações específicas

Módulos não devem recriar elementos globais da aplicação.
Eles devem consumir a base fornecida pelo Core.
## Propósito do Projeto

Este sistema é uma **reconstrução greenfield** de um SaaS para gestão de negócios. O objetivo é construir um produto limpo, modular e sustentável — preservando as regras de negócio essenciais, mas sem carregar débito técnico de versões anteriores.

O sistema **não é uma migração** de código legado. Todo o código é novo. A única herança é o conhecimento de domínio (o que o negócio precisa fazer).

---

## Arquitetura

O sistema é um **monólito modular** baseado em `nwidart/laravel-modules`. A estrutura é:

```
modularAppLavralLivewire/
├── app/                        ← Shell mínimo (base classes, User model)
├── Modules/
│   ├── Core/                   ← Infraestrutura transversal
│   ├── AgendaAi/               ← [planejado] Domínio de agendamentos
│   ├── MercadoPago/            ← [planejado] Domínio de pagamentos
│   └── Report/                 ← [planejado] Domínio de relatórios
├── resources/                  ← Assets globais (app.css, app.js)
├── routes/                     ← Rotas raiz (web.php, api.php)
└── config/                     ← Configurações Laravel
```

Cada módulo é um **domínio isolado**: tem seus próprios models, migrations, rotas, Livewire components, services e testes. Módulos se comunicam **exclusivamente via Events** — nunca importando models de outro módulo diretamente.

---

## Stack Tecnológico

| Tecnologia | Versão | Papel |
|---|---|---|
| PHP | ^8.2 | Runtime |
| Laravel | 12.x | Framework base |
| Livewire | ^4.2 | UI reativa server-side |
| Tailwind CSS | v4 (via Vite plugin) | Estilização utility-first |
| Alpine.js | (bundled com Livewire) | Interatividade client-side |
| nwidart/laravel-modules | ^12.0 | Estrutura modular |
| spatie/laravel-permission | ^6.25 | RBAC |
| laravel/pint | latest | Qualidade de código (PSR-12) |
| Vite | latest | Asset bundler |
| MySQL | 8+ | Banco de dados |
| Node.js | 20+ | Build toolchain |

---

## Princípios de Design

### 1. Independência de módulos
Cada módulo deve ser capaz de existir, ser testado e ser removido sem quebrar os outros. Dependências entre módulos são proibidas a nível de código — use Events.

### 2. Camada de serviço
Lógica de negócio vive em **Service classes** (`Modules/Name/app/Services/`). Controllers e Livewire components são finos — delegam ao serviço e renderizam.

### 3. Action pattern
Operações atômicas e reutilizáveis vivem em **Action classes** (`Modules/Name/app/Actions/`). Uma action faz uma coisa só. Pode ser chamada de Services, Livewire, ou jobs.

### 4. Event-driven entre módulos
A única forma de um módulo reagir a algo que aconteceu em outro módulo é via Events do Laravel. Dispatche um evento no módulo origem; registre um Listener no módulo destino.

### 5. Sem acesso direto a models de outro módulo
`Modules\AgendaAi\Models\Appointment` nunca deve ser importado dentro de `Modules\Report\`. Se Report precisa de dados de AgendaAi, AgendaAi expõe um Service ou dispara um Event com os dados necessários.

---

## Regras de Desenvolvimento

### PSR-4 e Namespaces
- Namespace raiz de cada módulo: `Modules\Name\`
- Mapeado para: `Modules/Name/app/`
- Exemplo: `Modules\Core\Livewire\Dashboard` → `Modules/Core/app/Livewire/Dashboard.php`
- Nunca use `Modules\Name\Http\Livewire\` — Livewire fica diretamente em `Modules\Name\Livewire\`

### Convenções de nomenclatura

| Tipo | Convenção | Exemplo |
|---|---|---|
| Model | PascalCase singular | `Appointment.php` |
| Service | `NomeService` | `AppointmentService.php` |
| Action | `VerbNomeAction` | `CreateAppointmentAction.php` |
| Repository | `NomeRepository` (interface) + `EloquentNomeRepository` (impl) | |
| Livewire Component | PascalCase | `AppointmentList.php` |
| Form Request | `VerbNomeRequest` | `CreateAppointmentRequest.php` |
| Event | Passado do evento | `AppointmentCreated.php` |
| Listener | `OnNomeDoEvento` | `OnAppointmentCreated.php` |
| Migration | snake_case descritiva | `create_appointments_table.php` |

### Onde cada tipo de código vai

| Tipo | Localização |
|---|---|
| Eloquent Models | `Modules/Name/app/Models/` |
| Livewire Components | `Modules/Name/app/Livewire/` |
| Views Livewire | `Modules/Name/resources/views/livewire/` |
| Blade Components | `Modules/Name/resources/views/components/` |
| Services | `Modules/Name/app/Services/` |
| Actions | `Modules/Name/app/Actions/` |
| Repositories | `Modules/Name/app/Repositories/` |
| HTTP Controllers | `Modules/Name/app/Http/Controllers/` |
| Form Requests | `Modules/Name/app/Http/Requests/` |
| Migrations | `Modules/Name/database/migrations/` |
| Seeders | `Modules/Name/database/seeders/` |
| Factories | `Modules/Name/database/factories/` |
| Rotas web | `Modules/Name/routes/web.php` |
| Rotas api | `Modules/Name/routes/api.php` |
| Testes | `Modules/Name/tests/Feature/` e `Unit/` |
| Configurações do módulo | `Modules/Name/config/` |

### Matriz de decisão: Livewire vs Blade vs Alpine

| Situação | Solução |
|---|---|
| Formulário com validação server-side | Livewire Component |
| Lista com filtros e paginação | Livewire Component |
| Modal com estado no servidor | Livewire Component |
| Botão reutilizável sem lógica | Blade Component (`x-core::button`) |
| Card/Badge sem lógica | Blade Component |
| Dropdown puro CSS/JS | Alpine.js |
| Tabs sem dados do servidor | Alpine.js |
| Toggle/Accordion local | Alpine.js |
| Partial de layout (header, footer) | `@include` ou Blade Component |

---

## Limites de Módulos

### Core (`Modules/Core/`)
Infraestrutura transversal do sistema. Fornece autenticação, RBAC, layouts, sidebar, logging, e componentes UI compartilhados. **Não contém lógica de negócio.**

### Domínios (`Modules/AgendaAi/`, `Modules/MercadoPago/`, `Modules/Report/`)
Cada módulo encapsula completamente um domínio de negócio: models, migrations, services, rotas, views e testes. **Dependem do Core, mas nunca uns dos outros diretamente.**

---

## O que este sistema NÃO é

- **Não é uma migração** de sistema legado — é uma reconstrução greenfield
- **Não é um MVC monolítico tradicional** — lógica de negócio não vive em Controllers
- **Não tem God Classes** — Services e Actions têm responsabilidade única
- **Não tem acoplamento entre domínios** — sem `use Modules\AgendaAi\...` dentro de `Modules\Report\`
- **Não tem CSS customizado desnecessário** — Tailwind v4 cobre spacing, cores e tipografia
- **Não tem lógica de negócio em Views ou Livewire Components** — eles delegam para Services/Actions
