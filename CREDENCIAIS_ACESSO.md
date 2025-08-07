# 🔐 Credenciais de Acesso - Sistema Hemera Capital Partners

## 👨‍💼 Administradores

### Administrador Principal
- **Email:** admin@hemeracapital.com
- **Senha:** admin123
- **Tipo:** Administrador
- **Acesso:** Painel administrativo completo

### Super Administrador
- **Email:** superadmin@hemeracapital.com
- **Senha:** superadmin123
- **Tipo:** Administrador
- **Acesso:** Painel administrativo completo

## 👥 Funcionários

### João Silva
- **Email:** joao.silva@hemeracapital.com
- **Senha:** password123
- **Tipo:** Funcionário
- **Data de Nascimento:** 22/03/1990

### Maria Santos
- **Email:** maria.santos@hemeracapital.com
- **Senha:** password123
- **Tipo:** Funcionário
- **Data de Nascimento:** 10/07/1988

### Pedro Costa
- **Email:** pedro.costa@hemeracapital.com
- **Senha:** password123
- **Tipo:** Funcionário
- **Data de Nascimento:** 05/11/1992

### Ana Ferreira
- **Email:** ana.ferreira@hemeracapital.com
- **Senha:** password123
- **Tipo:** Funcionário
- **Data de Nascimento:** 18/09/1987

### Carlos Oliveira
- **Email:** carlos.oliveira@hemeracapital.com
- **Senha:** password123
- **Tipo:** Funcionário
- **Data de Nascimento:** 03/12/1991

### Sofia Rodrigues
- **Email:** sofia.rodrigues@hemeracapital.com
- **Senha:** password123
- **Tipo:** Funcionário
- **Data de Nascimento:** 25/04/1989

## 🌐 URLs de Acesso

### Painel Administrativo
- **URL:** `/admin/login`
- **Acesso:** Apenas administradores

### Portal do Funcionário
- **URL:** `/` (página principal)
- **Acesso:** Todos os funcionários

## 📋 Informações Importantes

- **Total de Usuários:** 8 (2 administradores + 6 funcionários)
- **Todos os emails estão verificados**
- **Senhas são hasheadas com bcrypt**
- **Sistema de roles implementado (is_admin)**

## 🔄 Como Resetar Dados

Para recriar os usuários, execute:
```bash
docker-compose exec app php artisan db:seed
```

## ⚠️ Segurança

**IMPORTANTE:** Altere as senhas padrão em ambiente de produção!