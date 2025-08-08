# 🔧 Guia de Solução de Problemas

## ❌ Erro HTTP 500 - filemtime(): stat failed

### **Problema:**
```
ErrorException
HTTP 500 Internal Server Error
filemtime(): stat failed for /var/www/storage/framework/views/[hash].php
```

### **Causa:**
- Permissões incorretas nos diretórios de storage
- Cache de views corrompido
- Arquivos de view compilada inexistentes

### **Solução:**
```bash
# 1. Limpar cache de views
docker-compose exec app php artisan view:clear

# 2. Limpar todos os caches
docker-compose exec app php artisan optimize:clear

# 3. Corrigir permissões (como root)
docker-compose exec --user root app chown -R www:www /var/www/storage
docker-compose exec --user root app chmod -R 775 /var/www/storage
docker-compose exec --user root app chown -R www:www /var/www/bootstrap/cache
docker-compose exec --user root app chmod -R 775 /var/www/bootstrap/cache

# 4. Reiniciar container
docker-compose restart app
```

## 🐌 Sistema Lento

### **Problema:**
Páginas demoram muito para carregar (3-5 segundos)

### **Soluções Aplicadas:**
1. **Docker otimizado** com volumes cached
2. **PHP OPcache** habilitado
3. **MySQL** com configurações otimizadas
4. **Nginx** com compressão e cache
5. **Laravel** com cache de config/routes

### **Para aplicar otimizações:**
```bash
# Aplicar otimizações completas
chmod +x optimize-docker.sh
./optimize-docker.sh

# OU aplicar otimizações rápidas
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose restart app
```

## 🔐 Problemas de Permissão

### **Problema:**
```
Permission denied
Operation not permitted
```

### **Solução:**
```bash
# Executar comandos como root
docker-compose exec --user root app [comando]

# Corrigir permissões permanentemente
docker-compose exec --user root app chown -R www:www /var/www
docker-compose exec --user root app chmod -R 775 /var/www/storage
docker-compose exec --user root app chmod -R 775 /var/www/bootstrap/cache
```

## 🗄️ Problemas de Banco de Dados

### **Problema:**
```
SQLSTATE[HY000] [2002] Connection refused
```

### **Solução:**
```bash
# Verificar se MySQL está rodando
docker-compose ps

# Reiniciar MySQL
docker-compose restart db

# Verificar logs
docker-compose logs db

# Testar conexão
docker-compose exec app php artisan migrate:status
```

## 🚫 Erro 404 - Rota não encontrada

### **Problema:**
```
Route [admin.courses.index] not defined
```

### **Solução:**
```bash
# Limpar cache de rotas
docker-compose exec app php artisan route:clear

# Verificar rotas disponíveis
docker-compose exec app php artisan route:list

# Recriar cache de rotas
docker-compose exec app php artisan route:cache
```

## 🔄 Problemas de Autoloader

### **Problema:**
```
Class not found
```

### **Solução:**
```bash
# Recriar autoloader
docker-compose exec app composer dump-autoload

# Com otimizações
docker-compose exec app composer dump-autoload --optimize --classmap-authoritative
```

## 📦 Problemas de Container

### **Problema:**
Container não inicia ou para inesperadamente

### **Solução:**
```bash
# Verificar logs
docker-compose logs app
docker-compose logs webserver
docker-compose logs db

# Reiniciar containers
docker-compose restart

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

## 🧹 Limpeza Completa

### **Quando usar:**
- Sistema muito lento
- Erros persistentes
- Após mudanças significativas

### **Comandos:**
```bash
# Parar tudo
docker-compose down

# Limpar sistema Docker
docker system prune -f
docker volume prune -f

# Rebuild completo
docker-compose build --no-cache
docker-compose up -d

# Aguardar inicialização
sleep 30

# Aplicar otimizações
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan config:cache
docker-compose exec --user root app chown -R www:www /var/www/storage
docker-compose exec --user root app chmod -R 775 /var/www/storage
```

## 🏥 Verificação de Saúde

### **Script de diagnóstico:**
```bash
chmod +x docker/scripts/health-check.sh
./docker/scripts/health-check.sh
```

### **Verificações manuais:**
```bash
# Status dos containers
docker-compose ps

# Uso de recursos
docker stats

# Verificar PHP
docker-compose exec app php -v

# Verificar Laravel
docker-compose exec app php artisan --version

# Verificar permissões
docker-compose exec app php -r "echo (is_writable('/var/www/storage') ? 'OK' : 'ERRO');"

# Verificar banco
docker-compose exec app php artisan migrate:status

# Verificar rotas
docker-compose exec app php artisan route:list --name=login
```

## 📞 Suporte Adicional

### **Logs importantes:**
```bash
# Logs da aplicação
docker-compose logs app --tail=50

# Logs do Nginx
docker-compose logs webserver --tail=50

# Logs do MySQL
docker-compose logs db --tail=50

# Logs do Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

### **Informações do sistema:**
```bash
# Versões
docker-compose exec app php -v
docker-compose exec app php artisan --version
docker-compose exec db mysql --version

# Configurações PHP
docker-compose exec app php -i | grep opcache

# Configurações MySQL
docker-compose exec db mysql -u root -proot -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size';"
```

---

**💡 Dica:** Mantenha este guia à mão para resolver problemas rapidamente!