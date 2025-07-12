#!/bin/bash
set -e

# Docker entrypoint script for Render.com deployment
echo "Starting Cytonn Task Management System..."

# Wait for database to be ready
echo "Waiting for database connection..."
sleep 15

# Run database setup if DATABASE_URL is set
if [ ! -z "$DATABASE_URL" ]; then
    echo "Setting up database..."
    php -f setup-render.php || echo "Database setup completed or already exists"
fi

# Ensure proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Start Apache
echo "Starting Apache server..."
exec apache2-foreground
