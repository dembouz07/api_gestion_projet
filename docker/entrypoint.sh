#!/bin/bash

set -e

echo "🚀 Starting Laravel Application..."

# Installer psql-client pour tester PostgreSQL
apt-get update > /dev/null 2>&1 && apt-get install -y postgresql-client > /dev/null 2>&1 || true

# Attendre que PostgreSQL soit prêt
echo "⏳ Waiting for PostgreSQL..."
max_attempts=30
attempt=0
until PGPASSWORD=$DB_PASSWORD psql -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -c '\q' 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -eq $max_attempts ]; then
        echo "❌ PostgreSQL did not become ready in time"
        exit 1
    fi
    echo "⏳ PostgreSQL is unavailable - sleeping (attempt $attempt/$max_attempts)"
    sleep 2
done
echo "✅ PostgreSQL is up!"

# Attendre qu'Elasticsearch soit prêt
echo "⏳ Waiting for Elasticsearch..."
max_attempts=30
attempt=0
until curl -s http://elasticsearch:9200/_cluster/health > /dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ $attempt -eq $max_attempts ]; then
        echo "❌ Elasticsearch did not become ready in time"
        exit 1
    fi
    echo "⏳ Elasticsearch is unavailable - sleeping (attempt $attempt/$max_attempts)"
    sleep 2
done
echo "✅ Elasticsearch is up!"

# Supprimer les caches problématiques
echo "🧹 Removing problematic cache files..."
rm -f /var/www/html/bootstrap/cache/services.php
rm -f /var/www/html/bootstrap/cache/packages.php
rm -f /var/www/html/bootstrap/cache/config.php

# Exécuter les migrations
echo "🗄️  Running migrations..."
php artisan migrate --force || echo "Migration failed, continuing..."

# Créer le lien storage
echo "🔗 Creating storage link..."
php artisan storage:link || echo "Storage link already exists"

# Définir les permissions finales
echo "🔒 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "✅ Laravel application is ready!"
echo "🌐 Application URL: http://localhost:8000"

# Exécuter la commande passée au container
exec "$@"
