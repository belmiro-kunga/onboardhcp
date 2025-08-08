#!/bin/bash

# Script de Otimização do Laravel para Performance
echo "🚀 Iniciando otimizações do Laravel..."

# Limpar todos os caches
echo "🧹 Limpando caches existentes..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Otimizar autoloader do Composer
echo "📦 Otimizando autoloader do Composer..."
composer dump-autoload --optimize --classmap-authoritative

# Cachear configurações para produção
echo "⚡ Criando caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Otimizar aplicação
echo "🔧 Otimizando aplicação..."
php artisan optimize

# Definir permissões corretas
echo "🔐 Configurando permissões..."
chown -R www:www /var/www/storage
chown -R www:www /var/www/bootstrap/cache
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache

echo "✅ Otimizações concluídas com sucesso!"