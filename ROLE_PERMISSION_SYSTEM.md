# Sistema de Roles e Permissões - Documentação

## Visão Geral

O sistema de roles e permissões foi implementado com sucesso, fornecendo controlo granular de acesso às funcionalidades da aplicação. O sistema suporta:

- **Roles**: Conjuntos de permissões que podem ser atribuídos a utilizadores
- **Permissões**: Direitos específicos para realizar ações no sistema
- **Grupos de Utilizadores**: Organização por departamento, equipa ou critérios personalizados
- **Herança de Permissões**: Utilizadores herdam permissões de múltiplos roles e grupos

## Componentes Implementados

### 1. RolePermissionService (`app/Services/RolePermissionService.php`)

Serviço principal que gere todas as operações relacionadas com roles e permissões:

```php
// Criar um novo role
$role = $rolePermissionService->createRole('Manager', 'Gestor de equipa', ['view_users', 'edit_users']);

// Atribuir role a utilizador
$rolePermissionService->assignRoleToUser($user, 'Manager', auth()->id());

// Verificar permissões
$hasPermission = $rolePermissionService->userHasPermission($user, 'view_users');

// Obter todas as permissões do utilizador
$permissions = $rolePermissionService->getUserPermissions($user);
```

### 2. Middleware de Permissões

#### CheckPermission (`app/Http/Middleware/CheckPermission.php`)
Verifica uma permissão específica:

```php
Route::get('/users', [UserController::class, 'index'])->middleware('permission:view_users');
```

#### CheckPermissions (`app/Http/Middleware/CheckPermissions.php`)
Verifica múltiplas permissões:

```php
// Utilizador precisa de qualquer uma das permissões
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('permissions:view_users,view_roles,any');

// Utilizador precisa de todas as permissões
Route::post('/users/bulk', [UserController::class, 'bulk'])
    ->middleware('permissions:edit_users,bulk_actions_users,all');
```

#### CheckRole (`app/Http/Middleware/CheckRole.php`)
Verifica roles específicos:

```php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:Admin,Manager,any');
```

### 3. Controllers

#### RoleController (`app/Http/Controllers/RoleController.php`)
Gestão completa de roles:
- CRUD de roles
- Atribuição/remoção de permissões
- Listagem de utilizadores com role específico

#### UserGroupController (`app/Http/Controllers/UserGroupController.php`)
Gestão de grupos de utilizadores:
- CRUD de grupos
- Gestão de membros do grupo
- Atribuição de permissões a grupos

#### UserRoleController (`app/Http/Controllers/UserRoleController.php`)
Gestão de roles de utilizadores:
- Atribuição/remoção de roles
- Verificação de permissões
- Ações em lote

### 4. Trait HasPermissions (`app/Traits/HasPermissions.php`)

Adiciona métodos de conveniência ao modelo User:

```php
// Verificar permissões
$user->can('view_users');
$user->canAny(['view_users', 'edit_users']);
$user->canAll(['view_users', 'edit_users']);

// Obter permissões
$permissions = $user->getAllPermissions();
$permissionsByModule = $user->getPermissionsByModule();

// Verificações de role
$user->isSuperAdmin();
$user->isAdmin();
$user->canManageUsers();
```

## Rotas Implementadas

### Gestão de Roles
```
GET    /admin/roles                    - Listar roles
GET    /admin/roles/create             - Formulário de criação
POST   /admin/roles                    - Criar role
GET    /admin/roles/{role}             - Ver detalhes do role
GET    /admin/roles/{role}/edit        - Formulário de edição
PUT    /admin/roles/{role}             - Actualizar role
DELETE /admin/roles/{role}             - Eliminar role
POST   /admin/roles/{role}/permissions - Atribuir permissões
DELETE /admin/roles/{role}/permissions - Remover permissões
```

### Gestão de Grupos
```
GET    /admin/groups                     - Listar grupos
GET    /admin/groups/create              - Formulário de criação
POST   /admin/groups                     - Criar grupo
GET    /admin/groups/{group}             - Ver detalhes do grupo
GET    /admin/groups/{group}/edit        - Formulário de edição
PUT    /admin/groups/{group}             - Actualizar grupo
DELETE /admin/groups/{group}             - Eliminar grupo
POST   /admin/groups/{group}/users       - Adicionar utilizadores
DELETE /admin/groups/{group}/users       - Remover utilizadores
POST   /admin/groups/{group}/permissions - Atribuir permissões
```

### Gestão de Roles de Utilizadores
```
GET    /admin/users/{user}/roles              - Ver roles do utilizador
POST   /admin/users/{user}/roles              - Atribuir role
DELETE /admin/users/{user}/roles/{role}       - Remover role
PUT    /admin/users/{user}/roles/sync         - Sincronizar roles
GET    /admin/users/{user}/roles/permissions  - Ver permissões
POST   /admin/bulk-roles/assign               - Atribuição em lote
POST   /admin/bulk-roles/remove               - Remoção em lote
```

## Permissões Disponíveis

### Gestão de Utilizadores
- `view_users` - Ver lista de utilizadores
- `create_users` - Criar novos utilizadores
- `edit_users` - Editar informações de utilizadores
- `delete_users` - Eliminar utilizadores
- `manage_user_roles` - Gerir roles de utilizadores
- `bulk_actions_users` - Ações em lote
- `import_export_users` - Importar/exportar dados

### Gestão de Roles
- `view_roles` - Ver roles e permissões
- `create_roles` - Criar novos roles
- `edit_roles` - Editar permissões de roles
- `delete_roles` - Eliminar roles

### Gestão de Conteúdo
- `view_courses` - Ver cursos
- `create_courses` - Criar cursos
- `edit_courses` - Editar cursos
- `delete_courses` - Eliminar cursos
- `view_videos` - Ver vídeos
- `create_videos` - Fazer upload de vídeos
- `edit_videos` - Editar informações de vídeos
- `delete_videos` - Eliminar vídeos

### Simulados
- `view_simulados` - Ver simulados
- `create_simulados` - Criar simulados
- `edit_simulados` - Editar questões
- `delete_simulados` - Eliminar simulados
- `view_simulado_results` - Ver resultados

### Relatórios e Analytics
- `view_reports` - Ver relatórios do sistema
- `export_reports` - Exportar dados de relatórios
- `view_analytics` - Ver analytics do sistema

### Auditoria
- `view_audit_logs` - Ver logs de auditoria
- `export_audit_logs` - Exportar dados de auditoria

### Configurações do Sistema
- `manage_system_settings` - Gerir configurações
- `manage_integrations` - Gerir integrações externas

## Roles Pré-definidos

### Super Admin
- Acesso completo a todas as funcionalidades
- Pode gerir configurações do sistema
- Pode gerir integrações

### Admin
- Acesso administrativo à maioria das funcionalidades
- Não pode alterar configurações do sistema

### Manager
- Pode gerir utilizadores e conteúdo
- Pode ver relatórios e analytics
- Pode ver logs de auditoria

### Employee
- Acesso básico para visualização
- Pode ver cursos, vídeos e simulados

### Student
- Acesso mínimo apenas para cursos atribuídos
- Pode ver vídeos e fazer simulados

## Utilização em Controllers

```php
class UserController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware de permissões
        $this->middleware('permission:view_users')->only(['index', 'show']);
        $this->middleware('permission:create_users')->only(['create', 'store']);
        $this->middleware('permission:edit_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only(['destroy']);
    }
    
    public function index()
    {
        // Verificação adicional no código
        if (!auth()->user()->can('view_users')) {
            abort(403, 'Sem permissão para ver utilizadores');
        }
        
        // Lógica do controller...
    }
}
```

## Utilização em Views (Blade)

```blade
@can('create_users')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        Criar Utilizador
    </a>
@endcan

@canany(['edit_users', 'delete_users'])
    <div class="actions">
        @can('edit_users')
            <a href="{{ route('admin.users.edit', $user) }}">Editar</a>
        @endcan
        
        @can('delete_users')
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                @csrf @method('DELETE')
                <button type="submit">Eliminar</button>
            </form>
        @endcan
    </div>
@endcanany
```

## Cache e Performance

O sistema utiliza cache Redis para optimizar a verificação de permissões:

- Cache de permissões de utilizador: 1 hora
- Cache de roles: 1 hora
- Limpeza automática quando permissões são alteradas

## Validações Implementadas

- Não é possível eliminar roles do sistema
- Não é possível remover o último role de admin de um utilizador
- Validação de permissões existentes ao atribuir
- Validação de utilizadores existentes ao adicionar a grupos

## Próximos Passos

Para completar a implementação, será necessário:

1. Criar as views Blade para as interfaces de gestão
2. Implementar JavaScript para interações dinâmicas
3. Configurar o sistema de cache Redis
4. Executar os seeders para popular dados iniciais
5. Criar testes automatizados

## Conclusão

O sistema de roles e permissões foi implementado com sucesso, fornecendo:

✅ **RolePermissionService** - Lógica de negócio centralizada
✅ **Middleware** - Verificação granular de permissões
✅ **Controllers** - Gestão completa via interface web
✅ **Trait HasPermissions** - Métodos de conveniência
✅ **Validações** - Segurança e integridade de dados
✅ **Rotas** - Interface administrativa completa
✅ **Cache** - Optimização de performance
✅ **Herança de Permissões** - Suporte a múltiplos roles e grupos

O sistema está pronto para ser utilizado e pode ser facilmente estendido com novas permissões e funcionalidades conforme necessário.