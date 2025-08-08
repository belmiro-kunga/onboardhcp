#!/bin/bash

echo "🚀 Iniciando otimização completa do sistema Docker..."

# Parar containers
echo "⏹️  Parando containers..."
docker-compose down

# Limpar imagens e volumes não utilizados
echo "🧹 Limpando recursos Docker não utilizados..."
docker system prune -f
docker volume prune -f

# Rebuild containers com otimizações
echo "🔨 Reconstruindo containers com otimizações..."
docker-compose build --no-cache

# Iniciar containers
echo "▶️  Iniciando containers otimizados..."
docker-compose up -d

# Aguardar containers iniciarem
echo "⏳ Aguardando containers iniciarem..."
sleep 30

# Executar otimizações do Laravel
echo "⚡ Executando otimizações do Laravel..."
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan optimize

# Configurar permissões
echo "🔐 Configurando permissões..."
docker-compose exec app chown -R www:www /var/www/storage
docker-compose exec app chown -R www:www /var/www/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/storage
docker-compose exec app chmod -R 775 /var/www/bootstrap/cache

# Verificar status
echo "📊 Verificando status dos containers..."
docker-compose ps

echo "✅ Otimização completa! O sistema agora deve estar significativamente mais rápido."
echo "🌐 Acesse: http://localhost:8000"