# Cytonn Task Management System

A comprehensive task management system built with PHP and Vue.js for the Cytonn Kenya internship challenge.

## Features

### Administrator Features
- Add, edit, and delete users
- Assign tasks to users with deadlines
- Manage task statuses (Pending, In Progress, Completed)
- View all tasks and user statistics
- Dashboard with comprehensive overview

### User Features
- View assigned tasks
- Update task status
- Receive email notifications for new task assignments
- Dashboard with personal task overview

### Technical Features
- Object-Oriented PHP architecture
- Vue.js for interactive frontend
- Responsive Bootstrap UI
- Email notifications system
- MySQL database with proper relationships
- Session-based authentication
- RESTful API endpoints

## Installation Instructions

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Mail server configuration (for email notifications)

### Setup Steps

1. **Database Setup**
   - Create a MySQL database named `task_management`
   - Import the schema from `database/schema.sql`
   - Update database credentials in `config/database.php`

2. **Web Server Configuration**
   - Copy the project to your web server's document root
   - Ensure the `public` folder is set as the document root or create a virtual host
   - Make sure the `logs` directory is writable

3. **Configuration**
   - Update email settings in `app/Services/EmailService.php`
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
