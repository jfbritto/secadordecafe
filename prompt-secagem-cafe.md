
# Prompt Completo — Plataforma de Gestão de Fazendas e Secagem de Café

## Contexto Geral
Você é um arquiteto de software sênior especializado em Laravel e sistemas SaaS multi-tenant.

Seu objetivo é construir uma plataforma web moderna chamada provisoriamente de “secadordecafe”, focada em:
- Gestão de fazendas
- Controle de secagem de café
- Gestão de clientes
- Controle financeiro básico
- Controle de usuários e permissões
- Cobrança recorrente via Asaas

A plataforma deve ser construída utilizando:
- Laravel (última versão estável)
- Blade + AdminLTE
- MySQL
- Eloquent ORM
- Laravel Queues
- Laravel Policies/Gates
- Spatie Permission
- Spatie Activitylog
- Laravel Mail
- Vite
- PHPUnit/Pest para testes

O projeto deve seguir boas práticas de:
- SOLID
- Clean Code
- Service Layer
- Repository Pattern quando fizer sentido
- Form Requests
- DTOs quando necessário
- Eventos e Listeners
- Logs de auditoria

---

# Modelo de Negócio

## Multi Fazenda
O sistema é multi-tenant simplificado.

Cada usuário pertence obrigatoriamente a uma fazenda.

Ao criar conta:
- O usuário deve informar:
  - Nome da fazenda
  - Nome
  - E-mail
  - Senha
- A fazenda deve ser criada automaticamente
- O usuário criador será o administrador daquela fazenda

Existe também:
- Usuário ROOT da plataforma
- Usuário ROOT consegue:
  - visualizar todas as fazendas
  - bloquear/desbloquear fazendas
  - visualizar assinaturas
  - acessar métricas gerais
  - impersonate/login como fazenda

---

# Fluxo de Cadastro

## Cadastro Inicial
Fluxo:
1. Usuário acessa página de cadastro
2. Informa:
   - nome da fazenda
   - nome
   - email
   - senha
3. Sistema cria:
   - fazenda
   - usuário administrador
   - assinatura trial
4. Sistema envia e-mail de boas-vindas

---

# Entidades Principais

## Fazenda
Campos:
- id
- nome
- slug
- status
- telefone
- cidade
- estado
- created_at

Relacionamentos:
- possui muitos usuários
- possui muitos clientes
- possui muitas secagens
- possui muitas despesas

---

## Usuários

Tipos:
- root
- admin
- operador
- financeiro
- visualizador

Cada perfil possui permissões específicas.

Implementar:
- CRUD de usuários
- Convite por e-mail
- Reset de senha
- Controle de permissões

---

## Clientes

Representa produtores/clientes da fazenda.

Campos:
- id
- farm_id
- nome
- telefone
- cpf_cnpj
- observacoes
- saldo_cafe_kg
- saldo_cafe_sacas

Funcionalidades:
- CRUD
- Histórico de movimentações
- Extrato do cliente
- Controle de saldo

---

# Controle de Café

O cliente possui saldo disponível de café no sistema.

Esse saldo representa:
- quantidade de café disponível para secagem

O saldo:
- aumenta manualmente através de entradas
- diminui conforme secagens acontecem

O sistema nunca pode permitir:
- saldo negativo

---

# Movimentações de Café

Criar entidade de movimentações.

Tipos:
- entrada
- secagem
- ajuste
- saída

Campos:
- cliente_id
- tipo
- quantidade_kg
- observacao
- usuario_id

Toda alteração de saldo deve gerar movimentação.

---

# Cadastro de Secagens

Esta é a principal feature do sistema.

Uma secagem:
- pode possuir 1 ou vários clientes
- cada cliente participa com uma quantidade diferente

Exemplo:
Secagem #10:
- João → 500kg
- Carlos → 300kg
- Pedro → 200kg

Total:
1000kg

---

## Regras da Secagem

Antes de salvar:
- validar saldo disponível do cliente
- impedir quantidade maior que saldo disponível

Ao confirmar secagem:
- reduzir saldo dos clientes
- gerar movimentações automaticamente

---

## Dados da Secagem

Campos:
- número da secagem
- data
- secador utilizado
- observações
- status

Itens:
- cliente
- quantidade recebida
- quantidade seca
- rendimento
- comissão
- saldo líquido

---

# Referência das Planilhas

O sistema deve considerar os controles observados nas planilhas enviadas:

## Planilha de Secagem
Colunas identificadas:
- Data
- Cliente
- Número do secador
- Café côco quantidade
- Secador
- Pilado KG
- Saldo em sacas
- Rendimento
- Porcentagem
- Comissão KG
- Saldo líquido KG
- Saída de café
- Saldo do armazém

O sistema deve transformar isso em:
- entidades normalizadas
- cálculos automáticos
- histórico auditável

---

## Controle de Despesas

Módulo financeiro simples.

Campos:
- data
- descrição
- categoria
- unidade
- quantidade
- valor unitário
- valor total
- observações

Categorias:
- combustível
- manutenção
- mão de obra
- impostos
- equipamentos
- outros

Funcionalidades:
- CRUD
- filtros
- relatórios
- totalizadores

---

# Dashboard

Dashboard da fazenda:
- total de clientes
- saldo total de café
- total de secagens
- despesas do mês
- gráficos
- últimas movimentações

Dashboard ROOT:
- total de fazendas
- fazendas ativas
- receita mensal
- inadimplência
- novos cadastros

---

# Integração Asaas

Implementar:
- criação de assinatura
- cobrança recorrente
- webhook
- atualização automática do status

Status:
- trial
- ativo
- atrasado
- cancelado
- bloqueado

Bloquear acesso da fazenda em caso de inadimplência.

---

# Segurança

Implementar:
- autenticação Laravel
- rate limit
- logs
- confirmação de email
- recuperação de senha
- auditoria

---

# Arquitetura Técnica

Estrutura esperada:
- Actions
- Services
- Repositories
- Policies
- Jobs
- Mailables
- DTOs

Utilizar:
- migrations
- seeders
- factories

---

# Banco de Dados

Gerar:
- DER completo
- migrations
- relacionamentos
- índices

---

# Funcionalidades Extras Futuras

Estruturar pensando em:
- aplicativo mobile
- emissão de relatórios PDF
- integração WhatsApp
- estoque
- financeiro avançado
- multi unidade
- integração balança
- integração fiscal

---

# Entregáveis Esperados

Gerar:
1. Arquitetura completa
2. Estrutura de pastas
3. DER
4. Migrations
5. Models
6. Policies
7. Fluxos
8. Casos de uso
9. APIs
10. Estrutura frontend
11. Estratégia multi-tenant
12. Estratégia de permissões
13. Estratégia de cobrança
14. Plano de desenvolvimento por etapas
15. Checklist MVP
16. Sugestões de melhorias
17. Estratégia de testes



---

# Ambiente de Desenvolvimento

O ambiente local deve utilizar Docker obrigatoriamente.

Stack local:
- Docker
- Docker Compose
- PHP
- Nginx
- MySQL
- Redis
- Mailhog

Objetivos:
- padronizar ambiente da equipe
- facilitar onboarding
- garantir consistência entre ambientes

Implementar containers separados para:
- aplicação Laravel
- banco MySQL
- redis
- nginx
- mailhog

O sistema deve possuir:
- docker-compose.yml
- Dockerfile otimizado
- configuração de variáveis via .env
- comandos simplificados para setup

Mailhog deve ser utilizado para:
- simular envio de emails
- validar:
  - boas-vindas
  - recuperação de senha
  - convites
  - notificações

Banco de dados oficial:
- MySQL

---

# Deploy e Infraestrutura

O deploy será realizado em VPS da HostGator.

Estratégia esperada:
- Ubuntu Server
- Nginx
- PHP-FPM
- MySQL
- Supervisor para filas
- SSL via Let's Encrypt
- Deploy automatizado

O sistema deve estar preparado para:
- ambiente staging
- ambiente produção
- execução de queues
- logs centralizados
- backups automáticos

Gerar também:
- documentação de deploy
- checklist de produção
- variáveis de ambiente necessárias
- estratégia de backups
- estratégia de monitoramento

