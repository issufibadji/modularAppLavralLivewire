---
name: senior-architect
description: Skill de arquitetura sênior especializada em PHP, Laravel, Livewire, Tailwind CSS e MySQL. Inclui padrões de design, decisões de stack, análise de dependências, estrutura modular com nwidart/laravel-modules, e boas práticas para sistemas web enterprise. Use ao projetar arquitetura, tomar decisões técnicas, avaliar trade-offs, definir padrões de integração ou revisar estrutura de módulos.
---

# Senior Architect — PHP / Laravel / Livewire / Tailwind / MySQL

Toolkit completo para arquiteto sênior com foco no ecossistema Laravel moderno.

---

## Stack de Referência

| Camada       | Tecnologia                                      |
|--------------|-------------------------------------------------|
| Linguagem    | PHP 8.2+                                        |
| Framework    | Laravel 10/11                                   |
| Módulos      | nwidart/laravel-modules                         |
| Frontend     | Livewire 3 + Alpine.js + Tailwind CSS 3         |
| Banco        | MySQL 8+ (via Eloquent ORM)                     |
| Auth         | Laravel Sanctum + Spatie Permission + 2FA       |
| Testes       | PHPUnit + Pest PHP                              |
| Build        | Vite (Laravel Plugin)                           |
| DevOps       | Docker / Laravel Sail / GitHub Actions          |
| Qualidade    | Laravel Pint (PSR-12) + Larastan (PHPStan)      |

---

## Capacidades Principais

### 1. Design de Arquitetura Modular

**Princípios:**
- Cada domínio de negócio é um módulo independente em `/Modules/`
- Módulos não se acoplam diretamente — comunicam via Events, Jobs ou Service classes
- Core (`app/`) contém apenas infraestrutura transversal: Auth, RBAC, Auditoria, Notificações

**Criando um módulo:**
```bash
php artisan module:make NomeModulo
php artisan module:enable NomeModulo
```

**Estrutura padrão de módulo:**
```
Modules/NomeModulo/
├── Config/
├── Console/
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Entities/          # Models do módulo
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Livewire/      # Componentes Livewire do módulo
│   └── Requests/
├── Providers/
│   └── NomeModuloServiceProvider.php
├── Resources/
│   ├── assets/        # JS/CSS específicos
│   └── views/         # Blade + componentes Livewire
├── Routes/
│   ├── web.php
│   └── api.php
└── Tests/
```

---

### 2. Padrões de Design Recomendados

#### Repository Pattern
```php
// Modules/NomeModulo/Repositories/NomeRepository.php
interface NomeRepositoryInterface {
    public function all(): Collection;
    public function find(string $id): Model;
    public function create(array $data): Model;
    public function update(string $id, array $data): Model;
    public function delete(string $id): bool;
}
```

#### Service Layer
```php
// Modules/NomeModulo/Services/NomeService.php
class NomeService {
    public function __construct(
        private NomeRepositoryInterface $repo
    ) {}

    public function processar(array $dados): Model {
        // lógica de negócio aqui
        return $this->repo->create($dados);
    }
}
```

#### Action Classes (Single Responsibility)
```php
// Modules/NomeModulo/Actions/CriarNomeAction.php
class CriarNomeAction {
    public function execute(NomeData $data): Model {
        // uma única responsabilidade
    }
}
```

#### Form Requests para validação
```php
// Modules/NomeModulo/Http/Requests/StoreNomeRequest.php
class StoreNomeRequest extends FormRequest {
    public function rules(): array {
        return [
            'campo' => ['required', 'string', 'max:255'],
        ];
    }
}
```

---

### 3. Componentes Livewire

**Convenções:**
- Componentes em `Modules/[Nome]/Http/Livewire/`
- Views em `Modules/[Nome]/Resources/views/livewire/`
- Usar `#[Validate]` attributes do Livewire 3 para validação inline
- Preferir `wire:model.live` para feedback imediato; `wire:model.blur` para campos pesados

**Exemplo de componente:**
```php
// Modules/AgendaAi/Http/Livewire/AgendamentoForm.php
namespace Modules\AgendaAi\Http\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

class AgendamentoForm extends Component
{
    #[Validate('required|string|max:255')]
    public string $titulo = '';

    #[Validate('required|date')]
    public string $data = '';

    public function salvar(): void
    {
        $this->validate();
        // persistir...
        $this->dispatch('agendamento-criado');
        $this->reset();
    }

    public function render()
    {
        return view('agendaai::livewire.agendamento-form');
    }
}
```

**Registrando no ServiceProvider do módulo:**
```php
use Livewire\Livewire;

public function boot(): void
{
    Livewire::component('agendaai::agendamento-form',
        \Modules\AgendaAi\Http\Livewire\AgendamentoForm::class);
}
```

---

### 4. Tailwind CSS com Blade

**Estrutura de layout base:**
```html
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900 antialiased">
    @include('partials.sidebar')
    <main class="ml-64 p-6">
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
```

**Configuração Tailwind para módulos** (`tailwind.config.js`):
```js
export default {
    content: [
        './resources/**/*.blade.php',
        './Modules/**/Resources/views/**/*.blade.php',
        './Modules/**/Http/Livewire/**/*.php',
    ],
    theme: { extend: {} },
    plugins: [require('@tailwindcss/forms')],
}
```

---

### 5. MySQL — Boas Práticas com Eloquent

**Migrations:**
```php
Schema::create('nome_tabela', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('titulo');
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->softDeletes();
    // índices explícitos para queries frequentes
    $table->index(['user_id', 'created_at']);
});
```

**Evitar N+1:**
```php
// Ruim
$items = Item::all();
foreach ($items as $item) { $item->user->name; }

// Bom
$items = Item::with('user')->get();

// Melhor (quando escopo é grande)
$items = Item::with('user:id,name')->paginate(20);
```

**Scopes para queries reutilizáveis:**
```php
// No model
public function scopeAtivo(Builder $query): void
{
    $query->where('ativo', true);
}

// Uso
Item::ativo()->with('user')->paginate();
```

---

### 6. RBAC e Permissões (Spatie)

**Padrão de nomenclatura:** `[acao]-[recurso]`
```
create-agendamentos
edit-agendamentos
delete-agendamentos
view-relatorios
```

**Em controllers:**
```php
$this->authorize('create-agendamentos');
```

**Em views Blade:**
```html
@can('edit-agendamentos')
    <button wire:click="editar">Editar</button>
@endcan
```

**Após alterar permissões:**
```bash
php artisan permission:cache-reset
```

---

### 7. Análise de Dependências

**Verificar saúde do projeto:**
```bash
# Dependências desatualizadas
composer outdated

# Vulnerabilidades de segurança
composer audit

# Análise estática
./vendor/bin/phpstan analyse --level=5

# Code style
./vendor/bin/pint --test
```

**Árvore de dependências de um módulo:**
```bash
composer depends nwidart/laravel-modules
```

---

### 8. Decisões de Arquitetura — Framework

| Cenário | Decisão |
|---------|---------|
| UI com estado (formulários, listas reativas) | Livewire Component |
| UI sem estado (display only) | Blade Component (`<x-...>`) |
| Operação pesada (relatório, email) | Job + Queue |
| Notificação ao usuário | Laravel Notification (DB + Push) |
| Comunicação entre módulos | Laravel Events + Listeners |
| Lógica reutilizável entre módulos | Trait ou Service no Core |
| Consulta complexa | Eloquent Query Builder + Scope |
| Consulta muito pesada | Raw Query ou View materializada no MySQL |
| Validação de entrada | Form Request |
| Transformação de saída | API Resource (`JsonResource`) |

---

## Fluxo de Desenvolvimento

### 1. Novo recurso em módulo existente
```bash
# 1. Criar migration
php artisan module:make-migration create_X_table NomeModulo

# 2. Criar model (Entity)
php artisan module:make-model X NomeModulo

# 3. Criar controller
php artisan module:make-controller XController NomeModulo

# 4. Criar componente Livewire
php artisan make:livewire NomeModulo\\XForm

# 5. Registrar rota em Modules/NomeModulo/Routes/web.php

# 6. Rodar migration
php artisan module:migrate NomeModulo

# 7. Testes
php artisan test --filter XTest
```

### 2. Cache e otimização
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Reset completo (dev)
```bash
php artisan migrate:fresh --seed
php artisan config:clear && php artisan cache:clear
php artisan view:clear && php artisan route:clear
```

---

## Pontos Críticos de Arquitetura

1. **Middleware order** obrigatório: `auth → Check2FA → CheckEmailVerification`
2. **UUID em vez de auto-increment** no model `User` — `$user->id` é string UUID
3. **ServiceProviders dos módulos** são auto-registrados pelo nwidart — não duplicar em `config/app.php`
4. **Livewire + Módulos** — registrar componentes no `boot()` do ServiceProvider do módulo
5. **Assets de módulos** — cada módulo com `vite.config.js` próprio; não misturar com o build raiz
6. **Permissões são cacheadas** — sempre rodar `permission:cache-reset` após seeders de permissão
7. **Eventos entre módulos** — usar `Event::dispatch()` e Listeners registrados no `EventServiceProvider` de cada módulo
8. **Soft Deletes** — preferir `softDeletes()` em tabelas de domínio para manter histórico

---

## Qualidade e Testes

```bash
# Rodar todos os testes
php artisan test

# Testes de um módulo específico
php artisan test --filter AgendaAiTest

# Cobertura
php artisan test --coverage

# PHPStan nível 5
./vendor/bin/phpstan analyse Modules/ app/ --level=5

# Formatação automática
./vendor/bin/pint
```

**Estrutura de teste por módulo:**
```
Modules/NomeModulo/Tests/
├── Unit/
│   └── NomeServiceTest.php
└── Feature/
    └── NomeControllerTest.php
```

---

## Referências Internas

- Contexto completo do projeto: `.claude/commands/projeto.md`
- Estrutura de módulos: `/Modules/`
- Config de módulos: `config/modules.php`
- Estado dos módulos: `modules_statuses.json`
