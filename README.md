# Mini Mart Project

A PHP-based mini mart management system using XAMPP.

## Prerequisites

- XAMPP installed at `/Applications/XAMPP/xamppfiles/`
- MySQL running on port 3306
- Apache server running
- PHP 8.3+

## Setup Instructions

### 1. Start XAMPP Services

Make sure Apache and MySQL are running:
- Open XAMPP Manager application
- Start Apache and MySQL services

Or use command line:
```bash
sudo /Applications/XAMPP/xamppfiles/xampp start
```

### 2. Database Configuration

The database has already been imported. If you need to reimport:

```bash
mysql -u root -pLensomnang@21 mini-mart < mini-mart.sql
```

**Database Details:**
- Host: `127.0.0.1:3306`
- Username: `root`
- Password: `Lensomnang@21`
- Database: `mini-mart`

### 3. Access the Application

Open your browser and navigate to:
```
http://localhost/mini_mart/
```

## Login Credentials

From the SQL database dump:
- **Username:** `admin`
- **Password:** `12345` (stored as MD5 hash in database)

## Project Structure

```
mini_mart/
├── index.php          # Main entry point
├── login.php          # Admin login page
├── auth.php           # Authentication check
├── config.php         # Database configuration
├── home.php           # Home page
├── product.php        # Products page
├── about.php          # About page
├── contact.php        # Contact page
├── navigator.php      # Navigation menu
├── footer.php         # Footer
├── content.php        # Content template
├── mini-mart.sql      # Database schema
└── style/
    └── style.css      # Stylesheet
```

## Features

- Admin authentication system
- Product management
- Multi-page navigation (Home, Products, About, Contact)
- Session management
- MySQL database integration

## Security Notes

⚠️ **Important:** This project uses MD5 for password hashing, which is not secure. For production, consider upgrading to:
- bcrypt
- Argon2
- password_hash() with PASSWORD_DEFAULT

## Troubleshooting

### Port 3306 Connection Issues
If MySQL is running on a different port, update `config.php`:
```php
$server = "127.0.0.1:YOUR_PORT";
```

### Apache Not Starting
Check if port 80 is already in use:
```bash
lsof -i :80
```

### Database Connection Failed
Verify MySQL is running:
```bash
ps aux | grep mysql
```

## Development

The project uses standard PHP with MySQLi for database operations and session management for authentication.








