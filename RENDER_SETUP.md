# Render.com Deployment Guide for Cytonn Task Management

## 🚀 Quick Setup Steps:

### 1. Deploy to Render
1. Go to [render.com](https://render.com) and sign up
2. Connect your GitHub account
3. Click "New +" → "Web Service"
4. Select your GitHub repository
5. Configure:
   - **Build Command**: `docker build -t app .`
   - **Start Command**: `docker run -p 10000:80 app`
   - **Environment**: Docker

### 2. Add PostgreSQL Database
1. In Render dashboard, click "New +" → "PostgreSQL"
2. Choose a name (e.g., `cytonn-db`)
3. Copy the **External Database URL** from the database info page

### 3. Set Environment Variables
In your web service settings, add:
```
DATABASE_URL=<paste-your-database-url-here>
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_USERNAME=your-gmail@gmail.com
EMAIL_PASSWORD=your-app-password
```

### 4. Initialize Database (IMPORTANT!)
After deployment, run this command in Render's console or visit the URL:
```
https://your-app.onrender.com/setup-render-database.php
```

This will:
- Create all database tables
- Create admin user: `admin@cytonn.com` (password: `admin123`)
- Create sample user: `john@example.com` (password: `user123`)

### 5. Login
Visit your app URL and login with:
- **Email**: `admin@cytonn.com`
- **Password**: `admin123`

## 🚨 Troubleshooting

### If login still doesn't work:
1. Visit: `https://your-app.onrender.com/create-emergency-admin.php`
2. This creates/resets the admin user

### If database connection fails:
1. Check the DATABASE_URL environment variable
2. Ensure PostgreSQL service is running
3. Visit: `https://your-app.onrender.com/health-debug.php` for diagnostics

## 🔧 Important Notes
- Change the default admin password after first login
- The database initialization only needs to be run once
- Render may take 2-3 minutes to start your service initially
