# Cytonn Task Management System

A modern, feature-rich task management system built with PHP, MySQL/PostgreSQL, and Vue.js. Designed for the Cytonn Internship Challenge.

## 🚀 Live Demo
- **Production URL**: [Will be available after Render.com deployment]
- **Local Development**: http://localhost/cytonn/task-management-system/public/

## ✨ Features

### Core Functionality
- **User Management**: Create, edit, and manage user accounts with role-based access
- **Task Management**: Full CRUD operations for tasks with status tracking
- **Dashboard**: Real-time statistics and task overview
- **Email Notifications**: Automated SMTP notifications for task assignments
- **Responsive Design**: Modern Vue.js interface with Bootstrap 5

### Technical Features
- **Modern Architecture**: MVC pattern with OOP PHP 8.0+
- **Database Flexibility**: Supports both MySQL (local) and PostgreSQL (cloud)
- **Cloud-Ready**: Configured for Render.com deployment
- **Email System**: Gmail SMTP integration with TLS encryption
- **Security**: Session-based authentication with proper validation

## 🛠 Technology Stack

### Backend
- **PHP 8.0+** - Core application logic
- **MySQL/PostgreSQL** - Database layer
- **Composer** - Dependency management

### Frontend
- **Vue.js 3** - Interactive user interface
- **Bootstrap 5.3.2** - Responsive design framework
- **Font Awesome 6.4.0** - Icons and UI elements

### Deployment
- **Git** - Version control
- **Render.com** - Cloud hosting platform
- **XAMPP** - Local development environment

## 📁 Project Structure

```
task-management-system/
├── app/
│   ├── Auth.php              # Authentication system
│   ├── Models/
│   │   ├── Task.php          # Task model and operations
│   │   └── User.php          # User model and operations
│   └── Services/
│       └── EmailService.php  # SMTP email notifications
├── config/
│   ├── database.php          # MySQL configuration
│   ├── database-render.php   # PostgreSQL configuration
│   └── database-docker.php   # Docker database configuration
├── database/
│   ├── cytonn_task_management.sql  # MySQL schema
│   └── postgres-schema.sql         # PostgreSQL schema
├── docker/
│   ├── apache.conf           # Apache configuration for Docker
│   └── entrypoint.sh         # Docker startup script
├── public/
│   ├── index.php            # Application entry point
│   ├── dashboard.php        # Main dashboard interface
│   ├── manage-tasks.php     # Task management interface
│   ├── manage-users.php     # User management interface
│   ├── my-tasks.php         # User task view
│   ├── auth/
│   │   ├── login.php        # Login interface
│   │   └── logout.php       # Logout handler
│   └── api/
│       ├── delete-task.php       # Task deletion endpoint
│       └── update-task-status.php # Status update endpoint
├── Dockerfile              # Docker container configuration
├── .dockerignore           # Docker build exclusions
├── composer.json           # Dependencies and autoloading
├── render.yaml            # Render.com deployment config
└── setup-render.php      # Database setup for deployment
```

## 🔧 Installation & Setup

### Local Development

1. **Prerequisites**
   ```bash
   - XAMPP (Apache + PHP 8.0+ + MySQL)
   - Composer
   - Git
   ```

2. **Clone Repository**
   ```bash
   git clone [your-repository-url]
   cd task-management-system
   ```

3. **Install Dependencies**
   ```bash
   composer install
   ```

4. **Database Setup**
   - Start XAMPP (Apache + MySQL)
   - Import `database/cytonn_task_management.sql` into phpMyAdmin
   - Update `config/database.php` with your MySQL credentials

5. **Email Configuration**
   - Update Gmail SMTP settings in `app/Services/EmailService.php`
   - Use App Password for Gmail authentication

6. **Access Application**
### Cloud Deployment (Render.com with Docker)

1. **Push to GitHub**
   ```bash
   git add .
   git commit -m "Deploy to Render.com with Docker"
   git push origin main
   ```

2. **Render.com Setup**
   - Choose **"Docker"** as the environment
   - Connect GitHub repository
   - Service will auto-deploy using `Dockerfile` and `render.yaml`
   - PostgreSQL database will be automatically configured

3. **Environment Variables**
   ```
   EMAIL_USERNAME=your-email@gmail.com
   EMAIL_PASSWORD=your-app-password
   ```
   
   (EMAIL_HOST and EMAIL_PORT are pre-configured in render.yaml)

## 💾 Database Schema

### Users Table
```sql
- id (Primary Key)
- first_name, last_name
- email (Unique)
- password (Hashed)
- role (admin/user)
- created_at, updated_at
```

### Tasks Table
```sql
- id (Primary Key)
- title, description
- assigned_to (Foreign Key → users.id)
- status (pending/in_progress/completed)
- priority (low/medium/high)
- due_date
- created_at, updated_at
```

## 🔐 Authentication & Security

### Login Credentials
**Admin Account:**
- Email: admin@cytonn.com
- Password: admin123

**User Account:**
- Email: user@cytonn.com
- Password: user123

### Security Features
- Password hashing with PHP password_hash()
- Session-based authentication
- Role-based access control
- SQL injection prevention with prepared statements
- XSS protection with input sanitization

## 📧 Email System

### SMTP Configuration
- **Provider**: Gmail SMTP
- **Security**: STARTTLS encryption
- **Authentication**: OAuth2 compatible
- **Features**: Automated task assignment notifications

### Email Templates
- Task assignment notifications
- Status update alerts
- Due date reminders

## 🎨 User Interface

### Dashboard Features
- **Statistics Cards**: Total tasks, users, completion rates
- **Recent Activity**: Latest task updates and assignments
- **Quick Actions**: Add tasks, manage users
- **Responsive Design**: Mobile-friendly interface

### Task Management
- **CRUD Operations**: Create, read, update, delete tasks
- **Status Tracking**: Visual status indicators
- **Priority Levels**: Color-coded priority system
- **Due Date Management**: Calendar integration

## 🚀 Deployment Process

### Automatic Deployment
The application is configured for automatic deployment on Render.com:

1. **Git Push** triggers deployment
2. **Dependencies** installed via Composer
3. **Database** automatically provisioned (PostgreSQL)
4. **Environment** configured from `render.yaml`

### Manual Deployment Steps
1. Ensure all code is committed to Git
2. Push to GitHub repository
3. Connect repository to Render.com
4. Configure environment variables
5. Deploy and access live URL

## 🎯 Cytonn Internship Challenge

This project demonstrates:
- **Full-Stack Development**: Complete web application with modern UI
- **Database Design**: Efficient schema with proper relationships
- **Cloud Deployment**: Professional hosting on Render.com
- **Email Integration**: Automated notification system
- **Security Best Practices**: Authentication and data protection
- **Code Quality**: Clean, documented, maintainable code

## 📝 API Endpoints

### Task Management
- `POST /api/delete-task.php` - Delete task
- `POST /api/update-task-status.php` - Update task status

### Authentication
- `POST /auth/login.php` - User login
- `GET /auth/logout.php` - User logout

## 📞 Contact

**Developer**: [Your Name]
**Email**: [Your Email]
**GitHub**: [Your GitHub Profile]
**LinkedIn**: [Your LinkedIn Profile]

---

**Built with ❤️ for the Cytonn Internship Challenge**
   - Configure your web server to route requests properly

4. **Access the Application**
   - Navigate to your domain/server
   - Use the login credentials provided on the login page

## Default Login Credentials

### Administrator Account
- **Email:** admin@cytonn.com
- **Password:** password

### User Accounts
- **Email:** john@example.com
- **Password:** password
- **Email:** jane@example.com
- **Password:** password

## File Structure

```
task-management-system/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   └── Task.php
│   ├── Services/
│   │   └── EmailService.php
│   └── Auth.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── public/
│   ├── api/
│   │   ├── update-task-status.php
│   │   └── delete-task.php
│   ├── auth/
│   │   ├── login.php
│   │   └── logout.php
│   ├── index.php (Login page)
│   ├── dashboard.php
│   ├── manage-users.php
│   └── manage-tasks.php
└── logs/
    └── email.log
```

## Technology Stack

- **Backend:** PHP 7.4+ with OOP design patterns
- **Frontend:** Vue.js 3 with Bootstrap 5
- **Database:** MySQL with PDO
- **Email:** PHP Mail function (easily upgradeable to PHPMailer)
- **Authentication:** Session-based with role management

## API Endpoints

- `POST /api/update-task-status.php` - Update task status
- `POST /api/delete-task.php` - Delete task (Admin only)

## Security Features

- Password hashing using PHP's password_hash()
- SQL injection prevention using prepared statements
- Session-based authentication
- Role-based access control
- CSRF protection considerations
- Input validation and sanitization

## Email Notifications

The system sends HTML email notifications for:
- New task assignments
- Task status updates

Email logs are stored in `logs/email.log` for debugging purposes.

## Browser Compatibility

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## Development Notes

This project demonstrates:
- Clean, object-oriented PHP code
- Modern JavaScript (Vue.js) integration
- Responsive design principles
- Database design best practices
- Email integration
- User experience considerations

## Support

For questions or issues, contact the development team.

---

**Developed for Cytonn Kenya Software Engineering Internship Challenge**
**Author:** [Your Name]
**Date:** July 2025
