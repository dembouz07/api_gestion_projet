#!/bin/bash

set -e

echo "🚀 Starting Laravel on Render..."

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
    echo "⏳ Attempt $attempt/$max_attempts..."
    sleep 2
done
echo "✅ PostgreSQL is ready!"

# Supprimer les caches
rm -rf bootstrap/cache/*.php

# Exécuter les migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Créer le lien storage
php artisan storage:link || echo "Storage link already exists"

# Définir les permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "✅ Laravel is ready!"

# Démarrer Supervisor (qui gère PHP-FPM et Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
