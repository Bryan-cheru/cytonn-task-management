#!/bin/bash

# Railway-specific startup script
echo "🚀 Railway Deployment Starting..."
echo "Current working directory: $(pwd)"
echo "Environment: Railway"

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
