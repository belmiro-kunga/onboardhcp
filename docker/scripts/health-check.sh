#!/bin/bash

echo "🏥 Verificação de Saúde do Sistema"
echo "=================================="

# Verificar se os containers estão rodando
echo "📦 Status dos Containers:"
docker-compose ps

echo ""
echo "🔍 Verificações de Sistema:"

# Verificar PHP
echo -n "✅ PHP: "
docker-compose exec app php -v | head -1

# Verificar Laravel
echo -n "✅ Laravel: "
docker-compose exec app php artisan --version

# Verificar permissões
echo -n "✅ Permissões Storage: "
docker-compose exec app php -r "echo (is_writable('/var/www/storage') ? 'OK' : 'ERRO') . PHP_EOL;"

echo -n "✅ Permissões Views: "
docker-compose exec app php -r "echo (is_writable('/var/www/storage/framework/views') ? 'OK' : 'ERRO') . PHP_EOL;"

# Verificar banco de dados
echo -n "✅ Conexão MySQL: "
docker-compose exec app php artisan migrate:status > /dev/null 2>&1 && echo "OK" || echo "ERRO"

# Verificar rotas
echo -n "✅ Rotas carregadas: "
ROUTE_COUNT=$(docker-compose exec app php artisan route:list --json | jq length 2>/dev/null || echo "0")
echo "$ROUTE_COUNT rotas"

# Verificar cache
echo "✅ Status do Cache:"
docker-compose exec app php artisan cache:table > /dev/null 2>&1 && echo "   - Cache: Configurado" || echo "   - Cache: Usando arquivos"

echo ""
echo "🌐 URLs de Acesso:"
echo "   - Aplicação: http://localhost:8000"
echo "   - phpMyAdmin: http://localhost:8080"

echo ""
echo "🚀 Sistema pronto para uso!"