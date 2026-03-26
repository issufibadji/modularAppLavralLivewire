# 02 — Módulo Core

O módulo Core é a infraestrutura transversal do sistema. Ele provê autenticação, RBAC, layouts compartilhados, sidebar de navegação e primitivas de UI para os demais módulos. Não contém lógica de negócio de nenhum domínio.

---

## Propósito do Core

O Core existe para resolver problemas que **todos os outros módulos têm em comum**:

- Quem é o usuário autenticado?
- Quais permissões ele tem?
- Como o layout da aplicação é estruturado?
- Onde ficam os componentes Blade compartilhados (botões, cards, alertas)?
- Como registrar eventos de auditoria?

Tudo isso é responsabilidade do Core. O que **não é** responsabilidade do Core: qualquer lógica relacionada a agendamentos, pagamentos, relatórios ou qualquer outro domínio de negócio.

---

## Estado Atual (Scaffold)

O Core atualmente contém apenas a estrutura base. As funcionalidades planejadas ainda não foram implementadas.

**Implementado:**

| Arquivo | Namespace | Responsabilidade |
|---|---|---|
| `Modules/Core/app/Livewire/Dashboard.php` | `Modules\Core\Livewire\Dashboard` | Componente Livewire da dashboard |
| `Modules/Core/resources/views/components/layouts/master.blade.php` | — | Layout principal da aplicação |
| `Modules/Core/app/Http/Controllers/CoreController.php` | `Modules\Core\Http\Controllers\CoreController` | Controller base do Core |
| `Modules/Core/app/Providers/CoreServiceProvider.php` | `Modules\Core\Providers\CoreServiceProvider` | Provider principal do módulo |
| `Modules/Core/app/Providers/EventServiceProvider.php` | `Modules\Core\Providers\EventServiceProvider` | Registro de listeners do Core |
| `Modules/Core/app/Providers/RouteServiceProvider.php` | `Modules\Core\Providers\RouteServiceProvider` | Carrega rotas do módulo |

---

## O que Pertence ao Core

### Autenticação
- Login, logout, registro, recuperação de senha
- Email verification
- 2FA (two-factor authentication)
- Middleware de verificação de 2FA

### RBAC — Roles & Permissions (via Spatie Permission)
- Definição de roles (`admin`, `manager`, `operator`, etc.)
- Definição de permissions por módulo
- Gates e Policies para verificação
- Seeder de roles e permissions iniciais

### Sidebar de Navegação
- Componente Livewire `Sidebar` com menu dinâmico
- Itens filtrados por permissão do usuário autenticado
- Suporte a grupos e sub-itens

### Layouts e Componentes UI Compartilhados
- `master.blade.php` — layout principal
- Componentes Blade: `<x-core::button>`, `<x-core::card>`, `<x-core::alert>`, `<x-core::modal>`
- Componente de notificações flash

### Audit Logging
- Model `AuditLog`
- Middleware que registra ações por usuário
- Interface de visualização de logs

---

## O que NÃO Pertence ao Core

| O que não vai | Onde vai |
|---|---|
| Models de domínio (Appointment, Payment) | Módulo respectivo |
| Lógica de agendamento | `Modules/AgendaAi/` |
| Lógica de pagamento | `Modules/MercadoPago/` |
| Geração de relatórios | `Modules/Report/` |
| Qualquer `if ($user->hasRole('admin'))` relacionado a negócio | Módulo respectivo |

---

## Namespace e Estrutura de Arquivos

```
Modules/Core/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── CoreController.php          ← Modules\Core\Http\Controllers\CoreController
│   ├── Livewire/
│   │   └── Dashboard.php                   ← Modules\Core\Livewire\Dashboard
│   ├── Models/                             ← (a implementar: AuditLog)
│   ├── Services/                           ← (a implementar: AuthService, PermissionService)
│   └── Providers/
│       ├── CoreServiceProvider.php
│       ├── EventServiceProvider.php
│       └── RouteServiceProvider.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── layouts/
│       │       └── master.blade.php        ← layout principal
│       └── livewire/
│           └── dashboard.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── database/
    └── migrations/
        └── (a implementar: create_audit_logs_table)
```

**Regra de namespace:** `Modules\Core\` mapeia para `Modules/Core/app/`.

Exemplo completo:
- Namespace: `Modules\Core\Livewire\Dashboard`
- Arquivo: `Modules/Core/app/Livewire/Dashboard.php`

---

## Sistema de Layout

O layout principal fica em `Modules/Core/resources/views/components/layouts/master.blade.php`.

Para usá-lo em qualquer módulo:

```blade
{{-- Em uma view de outro módulo --}}
<x-core::layouts.master>
    <x-slot name="title">Agendamentos</x-slot>

    {{-- conteúdo da página --}}
</x-core::layouts.master>
```

Em um Livewire component, use o atributo `#[Layout]`:

```php
<?php

namespace Modules\AgendaAi\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('core::components.layouts.master')]
class AppointmentList extends Component
{
    public function render()
    {
        return view('agendaai::livewire.appointment-list');
    }
}
```

---

## Funcionalidades Planejadas

### Autenticação e Segurança

- [ ] Login com email + senha
- [ ] Logout
- [ ] Registro de usuário (se aplicável ao produto)
- [ ] Recuperação de senha (forgot/reset)
- [ ] Email verification
- [ ] Middleware `check2fa`
- [ ] 2FA via TOTP (Google Authenticator)

### RBAC

- [ ] Seeder de roles iniciais (`admin`, `manager`, `operator`)
- [ ] Seeder de permissions por módulo
- [ ] Gate definitions em `CoreServiceProvider`
- [ ] Blade directive `@can` funcionando com roles do Spatie

### Interface

- [ ] Sidebar component (Livewire, itens dinâmicos por permissão)
- [ ] Dashboard com métricas reais (widgets por módulo)
- [ ] Componentes Blade compartilhados: button, card, alert, badge, modal, table
- [ ] Flash notifications (success, error, warning, info)

### Infraestrutura

- [ ] Model `AuditLog` com migration
- [ ] Middleware de auditoria (registra route, user_id, payload, IP)
- [ ] Interface admin para visualizar logs de auditoria

---

## Stack de Middleware Planejado

As rotas protegidas do sistema seguirão esta pilha:

```
web → auth → verified → check2fa → checkPermission('permission.name')
```

| Middleware | Responsabilidade |
|---|---|
| `auth` | Usuário autenticado (padrão Laravel) |
| `verified` | Email verificado |
| `check2fa` | 2FA completado na sessão |
| `checkPermission` | Permissão Spatie verificada |

---

## Registrando Componentes Livewire

No `CoreServiceProvider::boot()`:

```php
use Livewire\Livewire;
use Modules\Core\Livewire\Dashboard;

public function boot(): void
{
    Livewire::component('core::dashboard', Dashboard::class);
    // Livewire::component('core::sidebar', Sidebar::class);
}
```

O prefixo `core::` é convenção — outros módulos usam o próprio prefixo (`agendaai::`, `report::`, etc.).
