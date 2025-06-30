# Rayox Web Application

![Rayox Banner](assets/LOGO/Banner(white).png)

A modern web application built with PHP that provides a sleek and responsive user interface for various functionalities.

## 🌟 Features

- **User Authentication System**
  - Secure login and registration with CSRF protection
  - User profile management
  - Session handling with security enhancements
  - Password strength validation

- **Admin Dashboard**
  - Administrative controls
  - User management
  - System monitoring

- **Casino Module**
  - Interactive gaming interface
  - Secure transaction system

- **Security Features**
  - CSRF protection on all forms
  - Input sanitization and validation
  - SQL injection prevention
  - XSS protection
  - Secure session management
  - Activity logging
  - Security headers implementation

- **Responsive Design**
  - Modern UI/UX
  - Mobile-friendly interface
  - Smooth animations and transitions

## 🛠️ Technologies Used

- PHP 7.4+
- MySQL
- HTML5
- CSS3
- JavaScript
- Font Awesome 6.4.0

## 📁 Project Structure

```
MyWebsite/
├── admin/          # Administrative dashboard
├── assets/         # Static assets (images, icons)
├── casino/         # Casino gaming module
├── dashboard/      # User dashboard
├── login/          # Authentication pages
├── profile/        # User profile management
├── register/       # User registration
├── config.php      # Centralized configuration
├── security_check.php # Security checker script
├── database_updates.sql # Database security improvements
├── .htaccess      # Apache configuration with security headers
├── index.php      # Main entry point
└── logout.php     # Session termination
```

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- mod_rewrite enabled

### Installation

1. Clone the repository
   ```bash
   git clone [your-repository-url]
   ```

2. Import the database
   ```bash
   mysql -u [username] -p [database_name] < Dump20250503.sql
   ```

3. Run database updates for security improvements
   ```bash
   mysql -u [username] -p [database_name] < database_updates.sql
   ```

4. Configure your database credentials in `config.php`
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'login_app');
   ```

5. Configure your web server to point to the project directory

6. Access the application through your web browser
   ```
   http://localhost/[project-directory]
   ```

7. Run security check (optional)
   ```
   http://localhost/[project-directory]/security_check.php
   ```

## 🔒 Security Features

- **CSRF Protection**: All forms include CSRF tokens to prevent cross-site request forgery
- **Input Validation**: Comprehensive input sanitization and validation
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Output encoding and Content Security Policy headers
- **Session Security**: Secure session management with timeout and regeneration
- **Security Headers**: Comprehensive security headers including CSP, X-Frame-Options, etc.
- **Activity Logging**: User activity tracking for security monitoring
- **Password Security**: Strong password requirements and secure hashing
- **File Protection**: Sensitive files are protected from direct access

## 🛡️ Security Checklist

- [x] CSRF protection implemented
- [x] SQL injection prevention
- [x] XSS protection
- [x] Input validation and sanitization
- [x] Secure session management
- [x] Security headers configured
- [x] Password strength requirements
- [x] Activity logging
- [x] File access protection
- [x] Error handling without information disclosure

## 📝 Recent Updates

### Security Improvements (Latest)
- Added centralized configuration management
- Implemented CSRF protection on all forms
- Enhanced input validation and sanitization
- Added security headers and CSP
- Improved session security
- Added activity logging system
- Created security checker script
- Enhanced database security with new tables
- Updated PWA manifest with proper metadata

## 🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to check [issues page](your-repo-url/issues).

## 📝 License

This project is [MIT](https://choosealicense.com/licenses/mit/) licensed.

---
⭐️ From RayoxOnly