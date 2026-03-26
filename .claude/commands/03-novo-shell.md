# 01 — Shell da Aplicação

O shell é a camada raiz do projeto: provê o bootstrap do framework, configuração compartilhada, roteamento raiz e o pipeline de assets. Ele é intencionalmente mínimo — toda lógica de negócio e infraestrutura de domínio vive nos módulos.

---

## Propósito do Shell

O shell (`app/` + raiz do projeto) é responsável por:

- Inicializar o Laravel e registrar os Service Providers
- Fornecer o modelo `User` como âncora de autenticação
- Configurar o Vite (único `vite.config.js` na raiz)
- Definir rotas raiz (`routes/web.php`, `routes/api.php`) que redirecionam para módulos
- Controlar quais módulos estão ativos via `modules_statuses.json`

O shell **não contém lógica de negócio**. Se algo pertence a um domínio específico, vai para um módulo.

---

## Estrutura de Diretórios

```
modularAppLavralLivewire/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Controller.php          ← base class apenas
│   ├── Models/
│   │   └── User.php                    ← único model no shell
│   └── Providers/
│       └── AppServiceProvider.php      ← bootstrap mínimo
├── Modules/                            ← todos os módulos aqui
│   └── Core/
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/                             ← configurações Laravel padrão
├── database/
│   └── migrations/                     ← apenas migrations de framework (users, sessions, etc.)
├── resources/
│   ├── css/
│   │   └── app.css                     ← entry point Tailwind CSS v4
│   └── js/
│       └── app.js                      ← entry point JS (Alpine + Livewire)
├── routes/
│   ├── web.php                         ← rota raiz → redireciona para Core
│   └── api.php
├── modules_statuses.json               ← controla módulos ativos/inativos
├── composer.json
├── vite.config.js
└── package.json
```

---

## Divisão de Responsabilidades: app/ vs Modules/

| O que vai em `app/` | O que vai em `Modules/` |
|---|---|
| `Controller.php` (base class) | Todos os controllers de domínio |
| `User.php` (model de auth) | Todos os outros models |
| `AppServiceProvider.php` | Todos os Service Providers de módulo |
| — | Livewire components |
| — | Services, Actions, Repositories |
| — | Views, layouts, Blade components |
| — | Migrations de domínio |
| — | Rotas de módulo |

**Regra:** Se você está prestes a criar algo em `app/` que não seja uma dessas três classes, pare e avalie se deveria ir para `Modules/Core/` ou outro módulo.

---

## Configuração do Vite

O arquivo `vite.config.js` na raiz é o único ponto de configuração de assets. Ele carrega os assets raiz e pode incluir entrypoints de módulos:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
})
```

Não existe `tailwind.config.js` — o Tailwind CSS v4 é configurado via CSS (ver `doc/03-ui-system.md`).

---

## Autoload por Módulo

Cada módulo precisa estar mapeado no `composer.json` raiz para que o PHP encontre suas classes:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Modules\\Core\\": "Modules/Core/app/",
            "Modules\\Core\\Database\\Factories\\": "Modules/Core/database/factories/",
            "Modules\\Core\\Database\\Seeders\\": "Modules/Core/database/seeders/"
        }
    }
}
```

Ao adicionar um novo módulo, sempre adicione o mapeamento aqui e execute `composer dump-autoload`.

---

## Controle de Módulos Ativos

O arquivo `modules_statuses.json` na raiz define quais módulos são carregados:

```json
{
    "Core": true,
    "AgendaAi": false,
    "MercadoPago": false,
    "Report": false
}
```

Comandos para gerenciar:

```bash
# Ativar um módulo
php artisan module:enable AgendaAi

# Desativar um módulo
php artisan module:disable AgendaAi

# Listar status de todos os módulos
php artisan module:list
```

Um módulo desativado não tem seus `ServiceProvider`s carregados — suas rotas, views e classes não ficam disponíveis.

---

## Scripts do Composer

| Script | Comando | O que faz |
|---|---|---|
| `composer dev` | `composer run dev` | Inicia server + queue + pail + vite concorrentemente |
| `composer test` | `composer run test` | Executa `php artisan test` |

Definição no `composer.json`:

```json
{
    "scripts": {
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "@php artisan serve --no-interaction &",
            "@php artisan queue:listen --tries=1 &",
            "@php artisan pail --timeout=0 &",
            "npm run dev"
        ],
        "test": "@php artisan test"
    }
}
```

---

## Fluxo de Bootstrap

A ordem de carregamento ao receber uma request:

```
HTTP Request
    └── bootstrap/app.php
        └── AppServiceProvider::boot()
            └── nwidart ModuleServiceProvider (auto-discovery)
                ├── CoreServiceProvider::register() + boot()
                │   ├── CoreRouteServiceProvider (carrega Modules/Core/routes/)
                │   ├── CoreEventServiceProvider (registra listeners)
                │   └── Livewire::component() registrations
                ├── AgendaAiServiceProvider (se ativo)
                ├── MercadoPagoServiceProvider (se ativo)
                └── ReportServiceProvider (se ativo)
```

---

## Regras do Shell

1. **Nada de negócio em `app/`** — o shell é infraestrutura pura.
2. **`User` fica em `app/Models/`** — é a âncora de autenticação do Laravel; os módulos referenciam `App\Models\User` via relacionamentos, mas não estendem nem movem esse model.
3. **Um único `vite.config.js`** — não crie configurações Vite por módulo.
4. **Routes raiz são redirecionamentos** — `routes/web.php` na raiz não deve conter lógica de negócio, apenas redirect para rotas de módulos ou para o Core.
5. **Migrations de framework ficam em `database/migrations/`** — migrations de domínio ficam em `Modules/Name/database/migrations/`.
