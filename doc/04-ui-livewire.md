# 04 — Portabilidade dos Módulos

## Objetivo
Portar os módulos do sistema antigo para a nova base, mantendo arquitetura modular e funcionalidades.

## Meta
Levar as regras de negócio e fluxos dos módulos para o novo shell, corrigindo autoload, providers, namespaces e camada de interface.

## Escopo inicial
- Report
- Mercadopago
- AgendaAi

## Estratégia
Portar módulo por módulo.
Nunca todos ao mesmo tempo.

---

## 1. Regras gerais

### Convenções
- cada módulo vive em `Modules/[Nome]`
- namespace deve bater com a pasta
- providers devem ser únicos
- não duplicar módulo em `vendor` e `Modules`
- escolher uma única origem por módulo

### Estrutura sugerida
```text
Modules/
  Report/
    Config/
    Database/
    Http/
      Controllers/
      Livewire/
    Models/
    Providers/
    Resources/
      views/
        livewire/
    Routes/
 ```