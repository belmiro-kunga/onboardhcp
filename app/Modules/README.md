# Arquitetura Monolítica Modular - Hemera Capital Partners

## 📁 Estrutura dos Módulos

### 🔐 **Auth Module** (`app/Modules/Auth/`)
**Responsabilidade:** Gerenciamento de autenticação e autorização
- `Controllers/AuthController.php` - Login/logout de funcionários
- `Services/AuthService.php` - Lógica de autenticação

### 👥 **User Module** (`app/Modules/User/`)
**Responsabilidade:** Gerenciamento de usuários
- `Models/User.php` - Modelo de usuário com scopes e accessors
- `Services/UserService.php` - CRUD e operações de usuário

### 🎂 **Birthday Module** (`app/Modules/Birthday/`)
**Responsabilidade:** Sistema de aniversários
- `Services/BirthdayService.php` - Lógica de aniversários e cálculos

### 🏢 **Admin Module** (`app/Modules/Admin/`)
**Responsabilidade:** Área administrativa
- `Controllers/AdminController.php` - Dashboard e gerenciamento admin

### 📚 **Onboarding Module** (`app/Modules/Onboarding/`)
**Responsabilidade:** Sistema de integração de funcionários
- `Controllers/OnboardingController.php` - Dashboard de onboarding
- `Services/OnboardingService.php` - Lógica de progresso e etapas

## 🔧 **Princípios da Arquitetura**

### ✅ **Separação de Responsabilidades**
- Cada módulo tem uma responsabilidade específica
- Controllers focam apenas em HTTP requests/responses
- Services contêm a lógica de negócio
- Models representam dados e relacionamentos

### ✅ **Injeção de Dependência**
- Services são injetados nos Controllers
- Facilita testes unitários
- Reduz acoplamento entre módulos

### ✅ **Reutilização de Código**
- Services podem ser usados por múltiplos Controllers
- Lógica centralizada e consistente
- Fácil manutenção e extensão

### ✅ **Testabilidade**
- Services isolados são fáceis de testar
- Mocks podem ser facilmente criados
- Testes unitários e de integração simplificados

## 🚀 **Benefícios da Estrutura**

1. **Organização Clara:** Cada funcionalidade tem seu lugar definido
2. **Escalabilidade:** Novos módulos podem ser adicionados facilmente
3. **Manutenibilidade:** Mudanças são isoladas em módulos específicos
4. **Reutilização:** Services podem ser compartilhados entre módulos
5. **Testabilidade:** Cada componente pode ser testado independentemente

## 📋 **Como Adicionar um Novo Módulo**

1. Criar pasta em `app/Modules/NomeModulo/`
2. Adicionar Controllers, Services, Models conforme necessário
3. Registrar Services no `ModuleServiceProvider.php`
4. Adicionar rotas em `routes/web.php`
5. Criar views em `resources/views/nomemodulo/`

## 🔄 **Fluxo de Dados**

```
Request → Route → Controller → Service → Model → Database
                     ↓
Response ← View ← Controller ← Service ← Model ← Database
```

## 🛡️ **Segurança**

- Middleware de autenticação aplicado nos Controllers
- Validação de dados nos Controllers
- Autorização verificada nos Services
- Sanitização de dados nos Models