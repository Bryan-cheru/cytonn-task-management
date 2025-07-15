#!/bin/bash

# Render.com startup script
echo "🚀 Render Deployment Starting..."
echo "Current working directory: $(pwd)"
echo "Environment: Render"

# Create necessary directories
mkdir -p /var/log/apache2 /var/run/apache2 /var/lock/apache2 /var/www/html/logs

# Set proper permissions
chown -R www-data:www-data /var/www/html /var/log/apache2 /var/run/apache2 /var/lock/apache2
chmod -R 755 /var/www/html

# Check if we're in the right directory
if [ ! -f "public/health-simple.php" ]; then
    echo "❌ ERROR: health-simple.php not found in public directory"
    ls -la public/
    exit 1
fi

echo "✅ Health check file found"

# Start Apache
echo "🌐 Starting Apache server..."
exec apache2-foreground
