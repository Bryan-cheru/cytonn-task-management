#!/bin/bash

# Commit all changes for Render deployment

echo "🚀 Preparing Render deployment..."

# Add all files
git add .

# Commit with message
git commit -m "Configure project for Render deployment

- Add render.yaml configuration
- Create database initialization scripts
- Add emergency admin creation script
- Update health check endpoints
- Fix Dockerfile for Render compatibility
- Add comprehensive deployment documentation"

# Push to main branch
git push origin main

echo "✅ All changes committed and pushed!"
echo "📋 Next steps:"
echo "1. Go to render.com and create a new web service"
echo "2. Connect your GitHub repository"
echo "3. Add PostgreSQL database"
echo "4. Set environment variables (DATABASE_URL, EMAIL_*)"
echo "5. After deployment, visit /setup-render-database.php to initialize"
echo "6. Login with admin@cytonn.com / admin123"
