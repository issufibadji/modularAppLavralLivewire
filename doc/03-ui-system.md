# 03 — Sistema de UI

Este documento descreve o stack de UI do projeto, as convenções de uso de cada tecnologia e a matriz de decisão para saber quando usar Livewire, Blade, Alpine.js ou CSS puro.

---

## Stack de UI

| Tecnologia | Versão | Papel |
|---|---|---|
| Livewire | ^4.2 | UI reativa com estado no servidor |
| Alpine.js | (bundled) | Interatividade puramente client-side |
| Tailwind CSS | v4 (via Vite) | Estilização utility-first |
| Blade | (Laravel 12) | Templates, layouts, componentes estáticos |

---

## Matriz de Decisão

Use esta tabela antes de criar qualquer componente de UI:

| Situação | Solução correta |
|---|---|
| Formulário com validação server-side | Livewire Component |
| Lista com filtros, busca ou paginação | Livewire Component |
| Modal que carrega dados do servidor | Livewire Component |
| Upload de arquivo com progresso | Livewire Component |
| Contador de unidades lido do banco | Livewire Component |
| Botão, badge, card reutilizável sem lógica | Blade Component (`x-core::button`) |
| Partial de layout (header, footer, sidebar estático) | Blade Component ou `@include` |
| Dropdown de navegação (só CSS/JS) | Alpine.js |
| Accordion / tabs locais (sem dados do servidor) | Alpine.js |
| Toggle, show/hide local | Alpine.js |
| Animação de entrada/saída | Alpine.js com `x-transition` |
| Tipografia, espaçamento, cores | Classes Tailwind direto no HTML |

---

## Livewire 4 — Convenções

### Localização dos arquivos

```
Modules/Name/app/Livewire/          ← classes PHP
Modules/Name/resources/views/livewire/  ← views Blade
```

### Namespace

```
Modules\Name\Livewire\ComponentName
```

Exemplo:
- Classe: `Modules\Core\Livewire\Dashboard` → `Modules/Core/app/Livewire/Dashboard.php`
- View: `Modules/Core/resources/views/livewire/dashboard.blade.php`

### Registro no ServiceProvider

Registre cada componente no `boot()` do ServiceProvider do módulo:

```php
use Livewire\Livewire;
use Modules\Core\Livewire\Dashboard;

public function boot(): void
{
    Livewire::component('core::dashboard', Dashboard::class);
}
```

### Exemplo de componente Livewire 4

```php
<?php

namespace Modules\AgendaAi\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Modules\AgendaAi\Actions\CreateAppointmentAction;

#[Layout('core::components.layouts.master')]
class CreateAppointment extends Component
{
    #[Validate('required|string|max:255')]
    public string $clientName = '';

    #[Validate('required|date|after:today')]
    public string $scheduledAt = '';

    #[Validate('required|string')]
    public string $notes = '';

    public function save(CreateAppointmentAction $action): void
    {
        $this->validate();

        $action->execute([
            'client_name'  => $this->clientName,
            'scheduled_at' => $this->scheduledAt,
            'notes'        => $this->notes,
        ]);

        $this->reset();
        $this->dispatch('appointment-created');
        session()->flash('success', 'Agendamento criado com sucesso.');
    }

    public function render()
    {
        return view('agendaai::livewire.create-appointment');
    }
}
```

### Regras Livewire 4

| Regra | Detalhe |
|---|---|
| Validação inline | Use `#[Validate]` diretamente na propriedade |
| `wire:model.live` | Para campos responsivos (busca, filtros) |
| `wire:model.blur` | Para campos com operação pesada (evitar requests a cada tecla) |
| Comunicação entre componentes | `$this->dispatch('event-name', payload: $data)` |
| Ouvir eventos | `#[On('event-name')]` no método receptor |
| Lógica de negócio | Delegue para Actions ou Services — não coloque em `render()` |
| Layouts | Prefira `#[Layout('core::components.layouts.master')]` no component |

### Incluir Livewire na view mestre

No `master.blade.php`:

```blade
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
```

---

## Tailwind CSS v4 — Convenções

### Sem `tailwind.config.js`

O Tailwind v4 usa configuração CSS-first. Não existe arquivo `tailwind.config.js` no projeto.

### Entry point: `resources/css/app.css`

```css
@import "tailwindcss";

/* Plugins */
@plugin "@tailwindcss/forms";

/* Tema customizado */
@theme {
    --color-primary: oklch(55% 0.2 260);
    --color-primary-dark: oklch(45% 0.2 260);
    --color-brand: #1a56db;
    --font-sans: 'Inter', sans-serif;
}
```

### Vite plugin

No `vite.config.js`:

```js
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
        tailwindcss(),
    ],
})
```

### Content scanning automático

O Tailwind v4 escaneia automaticamente todos os arquivos do projeto para detectar classes utilizadas. Não é necessário configurar um array `content`.

### Regras Tailwind v4

| Regra | Detalhe |
|---|---|
| Utility-first | Use classes Tailwind para spacing, cores, tipografia — sem CSS custom |
| Cores customizadas | Defina em `@theme { --color-nome: valor; }` no `app.css` |
| Plugins | Declare via `@plugin "nome-do-plugin";` no `app.css` |
| Dark mode | Configurado em `@theme` com variantes `dark:` |
| Sem `@apply` para classes utilitárias | Prefira aplicar direto no HTML |
| `@apply` permitido | Apenas para abstrações de componente (ex: `.btn-primary`) no `app.css` |

---

## Alpine.js — Convenções

### Quando usar Alpine

- Dropdowns de navegação
- Accordion / tabs sem dados do servidor
- Modais puramente client-side (sem dados carregados via servidor)
- Toggle show/hide
- Transições e animações de entrada/saída

### Quando NÃO usar Alpine

Se o estado depende de dados do servidor, use Livewire. Alpine não faz requests — ele gerencia estado local no browser.

### Exemplo: dropdown de usuário

```blade
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" type="button">
        {{ auth()->user()->name }}
    </button>

    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg"
    >
        <a href="{{ route('core.profile') }}" class="block px-4 py-2 text-sm">Perfil</a>
        <form method="POST" action="{{ route('core.logout') }}">
            @csrf
            <button type="submit" class="block w-full text-left px-4 py-2 text-sm">Sair</button>
        </form>
    </div>
</div>
```

### Padrões Alpine comuns

| Padrão | Uso |
|---|---|
| `x-data="{ open: false }"` | Define estado local |
| `x-show="open"` | Controla visibilidade |
| `x-bind:class="{ active: isActive }"` | Classes condicionais |
| `@click="open = !open"` | Handler de evento |
| `@click.outside="open = false"` | Fechar ao clicar fora |
| `x-transition` | Transição padrão (fade) |
| `x-ref="input"` | Referência DOM |
| `$refs.input.focus()` | Acesso ao DOM via ref |

---

## Sistema de Layout

O layout principal vive em `Modules/Core/resources/views/components/layouts/master.blade.php`.

### Usar em views Blade diretas

```blade
<x-core::layouts.master>
    <x-slot name="title">Dashboard</x-slot>

    <div class="p-6">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
    </div>
</x-core::layouts.master>
```

### Usar em Livewire components (recomendado)

```php
#[Layout('core::components.layouts.master')]
class Dashboard extends Component { ... }
```

---

## Blade Components — Nomenclatura

Componentes Blade seguem a convenção `<x-modulo::nome-do-componente>`:

| Componente | Arquivo |
|---|---|
| `<x-core::button>` | `Modules/Core/resources/views/components/button.blade.php` |
| `<x-core::card>` | `Modules/Core/resources/views/components/card.blade.php` |
| `<x-core::alert>` | `Modules/Core/resources/views/components/alert.blade.php` |
| `<x-core::layouts.master>` | `Modules/Core/resources/views/components/layouts/master.blade.php` |
| `<x-agendaai::appointment-card>` | `Modules/AgendaAi/resources/views/components/appointment-card.blade.php` |

### Exemplo de Blade component

```blade
{{-- Modules/Core/resources/views/components/button.blade.php --}}
@props(['variant' => 'primary', 'type' => 'button'])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex items-center px-4 py-2 rounded-md font-medium text-sm
        " . match($variant) {
            'primary' => 'bg-primary text-white hover:bg-primary-dark',
            'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200',
            'danger' => 'bg-red-600 text-white hover:bg-red-700',
            default => 'bg-primary text-white',
        }
    ]) }}
>
    {{ $slot }}
</button>
```

Uso:

```blade
<x-core::button variant="primary" wire:click="save">
    Salvar
</x-core::button>

<x-core::button variant="danger" type="submit">
    Excluir
</x-core::button>
```
