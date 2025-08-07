# Hemera Capital Partners - Sistema de Onboarding
## 🏗️ Arquitetura Monolítica Modular

### 📁 **Estrutura Modular Implementada:**
- **Auth Module:** Autenticação e autorização
- **User Module:** Gerenciamento de usuários
- **Birthday Module:** Sistema de aniversários
- **Admin Module:** Área administrativa
- **Onboarding Module:** Sistema de integração

## 🎉 Funcionalidades Implementadas

### ✅ **Sistema de Aniversariantes**
- **Página de Login:** Exibe aniversariantes do dia com animação de confetes e bolo
- **Próximos Aniversários:** Lista os 3 próximos aniversariantes
- **Animações:** Confetes animados, bolo pulsante, efeitos visuais
- **Dados Dinâmicos:** Calcula idade e dias até próximo aniversário

### ✅ **Área Administrativa**
- **Gestão de Utilizadores:** CRUD completo de utilizadores
- **Interface Intuitiva:** Tabela com informações de aniversários
- **Modal de Edição:** Edição inline de utilizadores
- **Validações:** Formulários com validação completa

### ✅ **Controllers:**
- `AuthController` - Login/logout + busca aniversariantes
- `FuncionarioController` - Dashboard de onboarding
- `AdminController` - Gerenciamento de usuários

### ✅ **Views:**
- `layouts/app.blade.php` - Layout base com branding Hemera Capital Partners
- `auth/login.blade.php` - Login com seção de aniversariantes animada
- `funcionario/index.blade.php` - Dashboard de onboarding
- `admin/users.blade.php` - Gerenciamento de usuários

### ✅ **Rotas:**
**Funcionários:**
- `/` - Página de login com aniversariantes
- `POST /login` - Processa o login de funcionários
- `/funcionario` - Dashboard de onboarding

**Administradores:**
- `/admin/login` - Página de login administrativa (segura)
- `POST /admin/login` - Processa o login de administradores
- `/admin/dashboard` - Dashboard administrativo
- `/admin/users` - Gerenciamento de usuários (CRUD)

**Geral:**
- `POST /logout` - Logout (funcionários e admins)

### ✅ **Database:**
- **Migration:** Campo `birth_date` na tabela users
- **UserSeeder:** Usuários com aniversariante do dia incluído

## Como Configurar (Docker)

### 1. Iniciar Containers Docker
```bash
docker-compose up -d
```

### 2. Instalar Dependências (se necessário)
```bash
docker-compose exec app composer install
```

### 3. Configurar Banco de Dados
```bash
# Executar migrações
docker-compose exec app php artisan migrate

# Executar seeders para criar usuários de teste (incluindo aniversariante do dia)
docker-compose exec app php artisan db:seed
```

### 4. Acessar o Sistema
- **Sistema:** http://localhost:8000/
- **PhpMyAdmin:** http://localhost:8080/ (usuário: laravel, senha: laravel)

### 5. Comandos Úteis Docker
```bash
# Ver logs dos containers
docker-compose logs -f

# Parar containers
docker-compose down

# Rebuild containers (se necessário)
docker-compose up -d --build
```

**Nota:** O sistema usa Tailwind CSS via CDN e MySQL via Docker. Tudo funciona imediatamente após executar os comandos acima!

## 🔐 Sistema de Login Separado por Segurança

### **Funcionários** - `http://localhost:8000/`
- Interface com aniversariantes animados
- Acesso ao painel de integração
- **Credenciais:** `funcionario@teste.com` / `123456`

### **Administradores** - `http://localhost:8000/admin/login`
- Interface administrativa segura
- Acesso ao painel administrativo e gestão de utilizadores
- **Credenciais:** `admin@teste.com` / `123456`

## Usuários de Teste

Após executar `docker-compose exec app php artisan db:seed`:

**👤 Funcionários:**
- `funcionario@teste.com` / `123456`
- `maria@hemeracapital.com` / `123456` (aniversariante do dia 🎂)
- `joao@hemeracapital.com` / `123456`
- `ana@hemeracapital.com` / `123456`
- `carlos@hemeracapital.com` / `123456`

**🔐 Administrador:**
- `admin@teste.com` / `123456` (acesso total ao sistema)

## Design System Aplicado

O sistema segue o design system fornecido:

- **Fonte:** Poppins (300, 400, 500, 600, 700)
- **Cores:** Gradiente primário (#7D9CFA → #BA6EFF)
- **Componentes:** Inputs com border-radius 8px, botões com 12px
- **Layout:** Centralizado, cards com sombra sutil
- **Responsivo:** Funciona em desktop e mobile

## Funcionalidades do Sistema de Onboarding

- ✅ **Autenticação Segura:** Login com validação e logout seguro
- ✅ **Branding Corporativo:** Interface personalizada para Hemera Capital Partners
- ✅ **Dashboard de Onboarding:** Progresso visual do processo de integração
- ✅ **Etapas Estruturadas:** 
  - Documentação (ativo)
  - Treinamento (bloqueado até completar documentação)
  - Configuração (bloqueado até completar treinamento)
- ✅ **Proteção de Rotas:** Middleware de autenticação
- ✅ **Design Responsivo:** Funciona em desktop e mobile
- ✅ **Experiência do Usuário:** Interface intuitiva seguindo design system

## Características do Sistema

**Para Novos Funcionários:**
- Interface de boas-vindas personalizada
- Progresso visual do onboarding (0% inicial)
- Etapas sequenciais com bloqueio inteligente
- Mensagens motivacionais e orientações claras

**Para Administradores:**
- Usuários de teste pré-configurados
- Sistema extensível para adicionar novas etapas
- Controle de acesso baseado em autenticação

## Próximos Desenvolvimentos

O sistema está preparado para expansão com:
- ✨ **Módulo de Documentação:** Upload e validação de documentos
- ✨ **Sistema de Treinamento:** Vídeos, quizzes e certificações
- ✨ **Configuração de Perfil:** Preferências e acesso a sistemas
- ✨ **Dashboard Administrativo:** Acompanhamento de progresso
- ✨ **Notificações:** E-mails automáticos e lembretes
- ✨ **Relatórios:** Analytics do processo de onboarding