# 00 — Guia de Instalação

Este documento cobre todos os passos para colocar o ambiente de desenvolvimento em funcionamento, desde os pré-requisitos até a verificação dos módulos ativos. Siga na ordem indicada.

---

## Pré-requisitos

| Dependência | Versão mínima | Verificar com |
|---|---|---|
| PHP | 8.2+ | `php --version` |
| Composer | 2.x | `composer --version` |
| Node.js | 20+ | `node --version` |
| npm | 10+ | `npm --version` |
| MySQL | 8.0+ | `mysql --version` |
| Git | qualquer | `git --version` |

Extensões PHP obrigatórias: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`.

---

## Instalação

### 1. Clonar o repositório

```bash
git clone <repo-url> modularAppLavralLivewire
cd modularAppLavralLivewire
```

### 2. Instalar dependências PHP

```bash
composer install
```

### 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite `.env` e configure o banco de dados:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=modular_app
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 4. Executar migrations

```bash
php artisan migrate
```

### 5. Instalar dependências Node e buildar assets

```bash
npm install
npm run dev
```

---

## Servidor de Desenvolvimento

O script `composer dev` executa todos os processos necessários de forma concorrente:

```bash
composer dev
```

Este comando inicia em paralelo:
- `php artisan serve` — servidor HTTP
- `php artisan queue:listen` — worker de filas
- `php artisan pail` — log viewer em tempo real
- `npm run dev` — Vite com HMR

> **Nota:** Se o ambiente não suportar execução concorrente, execute cada processo em um terminal separado.

---

## Verificação dos Módulos

Após a instalação, verifique os módulos ativos:

```bash
php artisan module:list
```

Saída esperada:

```
+------+---------+----------+
| Name | Status  | Priority |
+------+---------+----------+
| Core | Enabled |    0     |
+------+---------+----------+
```

O arquivo `modules_statuses.json` na raiz do projeto controla quais módulos estão ativos. Um módulo desativado não tem seus providers carregados.

---

## Criando Novos Módulos

```bash
php artisan module:make NomeDoModulo
```

Após criar o módulo, adicione o autoload no `composer.json` raiz (seção `autoload.psr-4`):

```json
{
    "autoload": {
        "psr-4": {
            "Modules\\NomeDoModulo\\": "Modules/NomeDoModulo/app/",
            "Modules\\NomeDoModulo\\Database\\Factories\\": "Modules/NomeDoModulo/database/factories/",
            "Modules\\NomeDoModulo\\Database\\Seeders\\": "Modules/NomeDoModulo/database/seeders/"
        }
    }
}
```

Depois:

```bash
composer dump-autoload
php artisan module:enable NomeDoModulo
```

---

## Solução de Problemas

### Classes não encontradas após criar módulo

```bash
composer dump-autoload
```

### Módulo não carrega mesmo estando habilitado

Verifique se `modules_statuses.json` contém `"NomeDoModulo": true`. Verifique também se o namespace está correto no `composer.json`.

### Erro de views do módulo não encontradas

As views de um módulo são referenciadas com o prefixo `nome-do-modulo::`:

```php
// correto
return view('core::dashboard');

// errado
return view('dashboard');
```

### Assets não atualizam

```bash
npm run build
# ou em dev:
npm run dev
```

### Migrações de um módulo específico

```bash
php artisan module:migrate NomeDoModulo
```

### Reverter migrações de um módulo

```bash
php artisan module:migrate-rollback NomeDoModulo
```

---

## Comandos Úteis de Desenvolvimento

```bash
# Rodar todos os testes
php artisan test

# Rodar testes de um módulo específico
php artisan test --filter CoreTest

# Verificar code style (apenas reportar)
./vendor/bin/pint --test

# Corrigir code style automaticamente
./vendor/bin/pint

# Limpar todos os caches
php artisan optimize:clear
```

---

## Preparação para Produção

```bash
# Instalar dependências sem pacotes de dev
composer install --no-dev --optimize-autoloader

# Buildar assets para produção
npm run build

# Cachear tudo
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Symlink de storage
php artisan storage:link

# Rodar migrations
php artisan migrate --force
```
