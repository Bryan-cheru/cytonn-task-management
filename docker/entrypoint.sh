#!/bin/bash
set -e

# Railway deployment entrypoint
echo "Starting Cytonn Task Management System on Railway..."

# Create necessary directories
mkdir -p /var/log/apache2 /var/run/apache2 /var/lock/apache2 /var/www/html/logs

# Set proper permissions
chown -R www-data:www-data /var/www/html /var/log/apache2 /var/run/apache2 /var/lock/apache2
chmod -R 755 /var/www/html

# Start Apache in foreground
echo "Starting Apache server..."
exec apache2-foreground
        " || return 1
    fi
    return 0
}

# Wait for database with retries
echo "Waiting for database to be ready..."
for i in {1..30}; do
    if check_database; then
        echo "Database is ready!"
        break
    else
        echo "Database not ready, waiting... (attempt $i/30)"
        sleep 2
    fi
    
    if [ $i -eq 30 ]; then
        echo "Database connection timeout, proceeding anyway..."
    fi
done

# Run database setup if DATABASE_URL is set
if [ ! -z "$DATABASE_URL" ]; then
    echo "Setting up database..."
    echo "DATABASE_URL is: ${DATABASE_URL:0:30}..." # Show first 30 chars for debugging
    php -f setup-render.php || echo "Database setup completed or already exists"
else
    echo "No DATABASE_URL found, skipping database setup"
fi

# Export DATABASE_URL for Apache/PHP if it exists
if [ ! -z "$DATABASE_URL" ]; then
    export DATABASE_URL="$DATABASE_URL"
    echo "Exported DATABASE_URL to environment"
fi

# Ensure proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Test Apache configuration
echo "Testing Apache configuration..."
apache2ctl configtest

# Start Apache with environment variables
echo "Starting Apache server..."
exec apache2-foreground
