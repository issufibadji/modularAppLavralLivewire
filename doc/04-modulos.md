# 04 — Arquitetura de Módulos

Este documento descreve a filosofia, estrutura, convenções e padrões de código para módulos do sistema. Cada módulo é um domínio de negócio completamente auto-contido.

---

## Filosofia

Cada módulo representa **um único domínio de negócio**. Ele contém tudo que aquele domínio precisa: models, migrations, rotas, services, actions, Livewire components, views e testes.

**Módulos não se importam diretamente**. A comunicação entre módulos acontece exclusivamente via Events do Laravel. Isso garante que um módulo pode ser ativado, desativado ou substituído sem quebrar os outros.

---

## Estrutura de Diretórios

Estrutura completa de um módulo seguindo nwidart/laravel-modules v12:

```
Modules/Name/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── NameController.php
│   │   └── Requests/
│   │       └── CreateNameRequest.php
│   ├── Livewire/
│   │   └── NameList.php
│   ├── Models/
│   │   └── Name.php
│   ├── Services/
│   │   └── NameService.php
│   ├── Actions/
│   │   └── CreateNameAction.php
│   ├── Repositories/
│   │   ├── NameRepository.php               ← interface
│   │   └── EloquentNameRepository.php       ← implementação
│   └── Providers/
│       ├── NameServiceProvider.php
│       ├── EventServiceProvider.php
│       └── RouteServiceProvider.php
├── config/
│   └── name.php
├── database/
│   ├── migrations/
│   │   └── 2024_01_01_000000_create_names_table.php
│   ├── factories/
│   │   └── NameFactory.php
│   └── seeders/
│       └── NameDatabaseSeeder.php
├── resources/
│   ├── assets/
│   └── views/
│       ├── livewire/
│       │   └── name-list.blade.php
│       └── components/
│           └── name-card.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   │   └── NameTest.php
│   └── Unit/
│       └── NameServiceTest.php
├── composer.json
└── module.json
```

---

## Namespace

| Namespace | Mapeia para |
|---|---|
| `Modules\Name\` | `Modules/Name/app/` |
| `Modules\Name\Http\Controllers\` | `Modules/Name/app/Http/Controllers/` |
| `Modules\Name\Livewire\` | `Modules/Name/app/Livewire/` |
| `Modules\Name\Models\` | `Modules/Name/app/Models/` |
| `Modules\Name\Services\` | `Modules/Name/app/Services/` |
| `Modules\Name\Actions\` | `Modules/Name/app/Actions/` |
| `Modules\Name\Repositories\` | `Modules/Name/app/Repositories/` |
| `Modules\Name\Database\Factories\` | `Modules/Name/database/factories/` |
| `Modules\Name\Database\Seeders\` | `Modules/Name/database/seeders/` |

---

## Criando um Módulo

### 1. Gerar o scaffold

```bash
php artisan module:make AgendaAi
```

### 2. Adicionar autoload no `composer.json` raiz

```json
{
    "autoload": {
        "psr-4": {
            "Modules\\AgendaAi\\": "Modules/AgendaAi/app/",
            "Modules\\AgendaAi\\Database\\Factories\\": "Modules/AgendaAi/database/factories/",
            "Modules\\AgendaAi\\Database\\Seeders\\": "Modules/AgendaAi/database/seeders/"
        }
    }
}
```

### 3. Regenerar autoload

```bash
composer dump-autoload
```

### 4. Ativar o módulo

```bash
php artisan module:enable AgendaAi
```

---

## Padrões de Código

### Service Class

A Service concentra a lógica de negócio do domínio. Injeta Repositories e dispara Events.

```php
<?php

namespace Modules\AgendaAi\Services;

use Modules\AgendaAi\Models\Appointment;
use Modules\AgendaAi\Repositories\AppointmentRepository;
use Modules\AgendaAi\Events\AppointmentCreated;
use Illuminate\Support\Facades\Event;

class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepository $appointments,
    ) {}

    public function create(array $data): Appointment
    {
        $appointment = $this->appointments->create($data);

        Event::dispatch(new AppointmentCreated($appointment));

        return $appointment;
    }

    public function cancel(Appointment $appointment, string $reason): void
    {
        $appointment->update(['status' => 'cancelled', 'cancel_reason' => $reason]);

        Event::dispatch(new AppointmentCancelled($appointment));
    }
}
```

### Action Class

Uma Action executa uma única operação atômica. Pode ser chamada de Services, Livewire components ou jobs.

```php
<?php

namespace Modules\AgendaAi\Actions;

use Modules\AgendaAi\Models\Appointment;
use Modules\AgendaAi\Services\AppointmentService;

class CreateAppointmentAction
{
    public function __construct(
        private readonly AppointmentService $service,
    ) {}

    public function execute(array $data): Appointment
    {
        return $this->service->create([
            'client_name'  => $data['client_name'],
            'scheduled_at' => $data['scheduled_at'],
            'notes'        => $data['notes'] ?? null,
            'status'       => 'pending',
        ]);
    }
}
```

### Repository Interface

```php
<?php

namespace Modules\AgendaAi\Repositories;

use Modules\AgendaAi\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;

interface AppointmentRepository
{
    public function create(array $data): Appointment;

    public function findById(string $id): ?Appointment;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function update(Appointment $appointment, array $data): bool;

    public function delete(Appointment $appointment): bool;
}
```

### Repository Eloquent (implementação)

```php
<?php

namespace Modules\AgendaAi\Repositories;

use Modules\AgendaAi\Models\Appointment;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentAppointmentRepository implements AppointmentRepository
{
    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function findById(string $id): ?Appointment
    {
        return Appointment::find($id);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Appointment::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('client_name', 'like', "%{$search}%"))
            ->orderBy('scheduled_at')
            ->paginate($perPage);
    }

    public function update(Appointment $appointment, array $data): bool
    {
        return $appointment->update($data);
    }

    public function delete(Appointment $appointment): bool
    {
        return $appointment->delete();
    }
}
```

### Binding no ServiceProvider do módulo

```php
public function register(): void
{
    $this->app->bind(
        \Modules\AgendaAi\Repositories\AppointmentRepository::class,
        \Modules\AgendaAi\Repositories\EloquentAppointmentRepository::class,
    );
}
```

### Form Request

```php
<?php

namespace Modules\AgendaAi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('appointments.create');
    }

    public function rules(): array
    {
        return [
            'client_name'  => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}
```

### Model com boas práticas

```php
<?php

namespace Modules\AgendaAi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Appointment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'client_name',
        'scheduled_at',
        'notes',
        'status',
        'cancel_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];
}
```

---

## Comunicação Entre Módulos

**Proibido:**

```php
// Nunca faça isso em Modules/Report/
use Modules\AgendaAi\Models\Appointment; // PROIBIDO
```

**Correto — via Events:**

### Módulo origem: dispatch do evento

```php
// Modules/AgendaAi/app/Events/AppointmentCreated.php
namespace Modules\AgendaAi\Events;

use Modules\AgendaAi\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;

class AppointmentCreated
{
    use Dispatchable;

    public function __construct(
        public readonly string $appointmentId,
        public readonly string $clientName,
        public readonly string $scheduledAt,
    ) {}

    // O evento expõe dados primitivos — não o Model completo
    public static function fromModel(Appointment $appointment): self
    {
        return new self(
            appointmentId: $appointment->id,
            clientName: $appointment->client_name,
            scheduledAt: $appointment->scheduled_at->toISOString(),
        );
    }
}
```

### Módulo destino: listener registrado no EventServiceProvider

```php
// Modules/Report/app/Providers/EventServiceProvider.php
namespace Modules\Report\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\AgendaAi\Events\AppointmentCreated;
use Modules\Report\Listeners\OnAppointmentCreated;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AppointmentCreated::class => [
            OnAppointmentCreated::class,
        ],
    ];
}
```

---

## Banco de Dados

### Convenções obrigatórias

| Convenção | Detalhe |
|---|---|
| Primary key | UUID (`HasUuids`) em todos os models de domínio |
| Soft deletes | `SoftDeletes` em todos os models de domínio |
| Timestamps | `created_at`, `updated_at` sempre presentes |
| Indexes | Declare explicitamente em migrations: `$table->index(['status', 'scheduled_at'])` |
| Foreign keys | Sempre com `constrained()` e `cascadeOnDelete()` ou `nullOnDelete()` explícito |

### Executar migrations de um módulo

```bash
php artisan module:migrate AgendaAi
```

### Rollback de migrations de um módulo

```bash
php artisan module:migrate-rollback AgendaAi
```

---

## Testes

### Estrutura

```
Modules/AgendaAi/tests/
├── Feature/
│   └── CreateAppointmentTest.php   ← testa o fluxo completo (HTTP/Livewire)
└── Unit/
    └── AppointmentServiceTest.php  ← testa a Service isolada
```

### Executar testes de um módulo

```bash
php artisan test --filter AgendaAi
```

### Executar todos os testes

```bash
php artisan test
```

---

## Módulos Planejados

| Módulo | Domínio | Status |
|---|---|---|
| Core | Infraestrutura (auth, RBAC, UI) | Scaffold criado |
| AgendaAi | Gestão de agendamentos | Planejado |
| MercadoPago | Pagamentos e cobranças | Planejado |
| Report | Relatórios e analytics | Planejado |
