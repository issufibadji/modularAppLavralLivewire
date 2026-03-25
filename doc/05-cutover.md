
---

## `05-cutover.md`

```md
# 05 — Cutover e Desligamento do Legado

## Objetivo
Fazer a transição do sistema antigo para o novo com segurança, validando fluxos críticos antes do desligamento do legado.

## Meta
Colocar o sistema novo em condição real de uso, sem trocar tudo no impulso.

## Escopo
- validação final
- testes críticos
- limpeza do legado
- plano de entrada em produção
- rollback

## Tarefas

### 1. Validação funcional
- [ ] login
- [ ] logout
- [ ] recuperação de senha
- [ ] 2FA
- [ ] permissões
- [ ] sidebar dinâmica
- [ ] dashboard
- [ ] CRUD de usuários
- [ ] roles/permissões
- [ ] auditoria
- [ ] notificações
- [ ] Report
- [ ] Mercadopago
- [ ] AgendaAi

### 2. Validação técnica
- [ ] sem warnings críticos no Composer
- [ ] sem erros PSR-4
- [ ] sem classes ambíguas
- [ ] sem dependência residual de Bootstrap/jQuery
- [ ] Vite funcionando
- [ ] Livewire funcionando
- [ ] Alpine funcionando
- [ ] filas/jobs funcionando
- [ ] logs limpos

### 3. Limpeza
- [ ] remover assets legados não usados
- [ ] remover Bootstrap
- [ ] remover jQuery
- [ ] remover Mix/webpack antigo
- [ ] remover views mortas
- [ ] remover controllers de tela que foram substituídos
- [ ] remover providers/aliases obsoletos

### 4. Plano de produção
- [ ] definir estratégia de deploy
- [ ] validar variáveis de ambiente
- [ ] validar banco
- [ ] validar filas e cron
- [ ] validar permissões de storage/cache
- [ ] validar logs e monitoramento

### 5. Rollback
- [ ] definir gatilhos de rollback
- [ ] definir backup antes do deploy
- [ ] definir procedimento de reversão
- [ ] documentar quem faz o quê no dia do corte

### 6. Entrada controlada
- [ ] ambiente de homologação aprovado
- [ ] smoke test em produção
- [ ] validar usuários críticos
- [ ] validar integrações sensíveis
- [ ] acompanhar erros pós-deploy

## Critérios de aceite
- sistema novo cobre fluxos críticos do legado
- produção estável
- rollback documentado
- legado pronto para ser desligado
- equipe sabe operar a nova base

## Riscos
- desligar legado cedo demais
- esquecer fluxo crítico escondido
- entrar em produção sem rollback

## Regra de ouro
Cutover não é “subir e rezar”.
É validar, observar e só então desligar o sistema antigo.