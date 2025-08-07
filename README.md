# Laravel com Docker

Este projeto configura um ambiente completo de desenvolvimento Laravel com MySQL usando Docker.

## Serviços Incluídos

- **Laravel** (última versão estável) - Framework PHP
- **MySQL 8.0** - Banco de dados
- **Nginx** - Servidor web
- **phpMyAdmin** - Interface web para MySQL

## Como Usar

### 1. Construir e Iniciar os Containers

```bash
docker-compose up -d --build
```

### 2. Instalar o Laravel (primeira vez)

```bash
docker-compose exec app bash
composer create-project laravel/laravel .
cp .env.example .env
php artisan key:generate
php artisan migrate
exit
```

### 3. Acessar a Aplicação

- **Laravel**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080

### 4. Credenciais do Banco de Dados

- **Host**: db
- **Database**: laravel
- **Username**: laravel
- **Password**: laravel
- **Root Password**: root

## Comandos Úteis

### Parar os containers
```bash
docker-compose down
```

### Ver logs
```bash
docker-compose logs -f
```

### Executar comandos Artisan
```bash
docker-compose exec app php artisan [comando]
```

### Executar Composer
```bash
docker-compose exec app composer [comando]
```

### Acessar o container da aplicação
```bash
docker-compose exec app bash
```

## Estrutura do Projeto

```
.
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── local.ini
│   └── mysql/
│       └── my.cnf
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Observações

- O MySQL está configurado para persistir dados no volume `dbdata`
- As configurações PHP estão otimizadas para desenvolvimento
- O Nginx está configurado para servir aplicações Laravel
- O phpMyAdmin facilita a administração do banco de dados