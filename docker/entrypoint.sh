#!/bin/bash

# Docker entrypoint script for Render.com deployment
echo "Starting Cytonn Task Management System..."

# Wait for database to be ready
echo "Waiting for database connection..."
sleep 10

# Run database setup if DATABASE_URL is set
if [ ! -z "$DATABASE_URL" ]; then
    echo "Setting up database..."
    php setup-render.php
fi

# Start Apache
echo "Starting Apache server..."
apache2-foreground
