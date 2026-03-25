
---

## `02-core.md`

```md
# 02 — Core do Sistema

## Objetivo
Reconstruir o núcleo transversal do sistema antes de portar módulos de negócio.

## Escopo
- usuários
- autenticação
- autorização
- 2FA
- configurações globais
- sidebar dinâmica
- auditoria
- notificações

## Meta
Garantir que o sistema novo tenha o mesmo núcleo funcional do legado, mas com base limpa.

## Tarefas

### 1. Usuários
- [ ] portar model `User`
- [ ] manter UUID
- [ ] validar casts, traits e relacionamentos
- [ ] revisar mutators/accessors
- [ ] revisar factories e seeders

### 2. Autenticação
- [ ] configurar login
- [ ] configurar logout
- [ ] configurar recuperação de senha
- [ ] configurar proteção de rotas
- [ ] validar sessão e guard

### 3. 2FA
- [ ] reconstruir fluxo de 2FA
- [ ] validar middleware
- [ ] validar tela de verificação
- [ ] validar persistência de estado
- [ ] garantir ordem:
  - [ ] auth
  - [ ] Check2FA
  - [ ] CheckEmailVerification

### 4. Permissões e papéis
- [ ] configurar `spatie/laravel-permission`
- [ ] portar roles
- [ ] portar permissions
- [ ] validar policies/gates
- [ ] revisar permissões por módulo
- [ ] validar cache de permissões

### 5. Sidebar dinâmica
- [ ] portar tabela `menu_side_bars`
- [ ] portar model e relacionamentos
- [ ] reconstruir menu lateral dinâmico
- [ ] validar renderização por permissão
- [ ] validar hierarquia e links

### 6. Configurações globais
- [ ] portar configurações do sistema
- [ ] validar telas administrativas
- [ ] definir onde ficam configs globais
- [ ] revisar uso de config em banco vs arquivo

### 7. Auditoria
- [ ] instalar/configurar pacote de auditoria
- [ ] portar regras de auditoria
- [ ] validar eventos auditados
- [ ] validar listagem de logs

### 8. Notificações
- [ ] revisar notificações existentes
- [ ] portar notificações essenciais
- [ ] validar mail/database notifications
- [ ] garantir compatibilidade com sistema novo

## Critérios de aceite
- login funciona
- logout funciona
- usuário com UUID funciona
- permissões funcionam
- sidebar dinâmica aparece corretamente
- 2FA funciona
- auditoria grava corretamente
- rotas protegidas respeitam permissões

## Riscos
- quebrar fluxo de autenticação
- esquecer regra transversal que o módulo depende
- misturar regra de negócio de módulo dentro do core

## Regra de ouro
O core precisa ficar estável antes de entrar nos módulos.
Sem isso, tu só vai migrar bug com roupa nova.