# Railway Deployment Guide

## Quick Setup:

1. **Create Railway Account**: Go to [railway.app](https://railway.app) and sign up
2. **Connect GitHub**: Link your GitHub account
3. **Deploy Project**: Click "New Project" → "Deploy from GitHub repo" → Select this repository

## Database Setup:
- Railway will automatically detect you need PostgreSQL
- Click "Add Database" → "PostgreSQL" 
- Railway will automatically set the `DATABASE_URL` environment variable

## Environment Variables to Set:
```
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USERNAME=your-gmail@gmail.com
EMAIL_PASSWORD=your-app-password
```

## Health Check Endpoints:
- `/health-simple` - Simple OK response (used by Railway)
- `/health-debug` - Detailed health information for debugging

## Custom Domain (Optional):
- Go to your service settings
- Click "Settings" → "Domains"
- Add your custom domain

## Your app will be available at:
`https://your-app-name-production.up.railway.app`

## Migration Complete!
Your existing Docker setup and database configuration will work perfectly with Railway.
