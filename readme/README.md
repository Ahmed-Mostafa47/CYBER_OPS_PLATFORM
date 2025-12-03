# 🛡️ CYBER_OPS_PLATFORM - Cybersecurity Training Platform

![Platform](https://img.shields.io/badge/Platform-Cybersecurity%20Training-green)
![React](https://img.shields.io/badge/React-18.2-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![Vite](https://img.shields.io/badge/Build-Vite-purple)
![Tailwind](https://img.shields.io/badge/Styling-TailwindCSS-blue)

A professional **cybersecurity training platform** built with **React** and **PHP**, featuring hacker-themed UI components and pages for White Box and Black Box vulnerability labs. This platform allows users to learn and practice cybersecurity skills through interactive challenges.

---

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Configuration](#-configuration)
- [Running the Project](#-running-the-project)
- [API Endpoints](#-api-endpoints)
- [Development](#-development)
- [Deployment](#-deployment)
- [Troubleshooting](#-troubleshooting)

---

## 🎯 Features

### Authentication & User Management
- **🔐 Secure Authentication** - Email-based registration with verification codes
- **🔑 Password Management** - Change password, reset password, forgot password
- **👤 User Profiles** - Comprehensive user profiles with role management
- **🎭 Role-Based Access Control** - Admin, Instructor, and Student roles

### Training & Labs
- **🎓 White Box & Black Box Labs** - Different types of vulnerability labs
- **🏆 Challenge System** - Interactive challenges with flag submission
- **📊 Progress Tracking** - Points, ranks, and achievement system
- **💬 Community Features** - Comments and discussions

### User Interface
- **🎨 Hacker-Themed UI** - Matrix-style terminal interface
- **📱 Responsive Design** - Works on desktop and mobile devices
- **⚡ Fast Performance** - Optimized with Vite and React
- **🎭 Modern Animations** - Smooth transitions and effects

---

## 🛠️ Technology Stack

### Frontend
- **React 18.2** - Modern UI library
- **React Router DOM 7.9** - Client-side routing
- **Vite 5.0** - Fast build tool and dev server
- **Tailwind CSS 3.3** - Utility-first CSS framework
- **Axios 1.13** - HTTP client for API requests
- **Lucide React** - Icon library
- **React Icons** - Additional icon set

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Relational database
- **PHPMailer 7.0** - Email sending library
- **MySQLi** - MySQL database extension

### Development Tools
- **Node.js 16+** - JavaScript runtime
- **npm/yarn** - Package manager
- **Composer** - PHP dependency manager
- **XAMPP/WAMP** - Local development server (optional)

---

## 🏗️ Project Structure

```
graduatoin_project/
├── src/                          # Frontend React application
│   ├── features/                 # Feature-based organization
│   │   ├── auth/
│   │   │   └── pages/            # Authentication pages
│   │   │       ├── LoginPage.jsx
│   │   │       ├── RegisterPage.jsx
│   │   │       ├── SetPasswordPage.jsx
│   │   │       ├── ChangePasswordPage.jsx
│   │   │       ├── ResetPasswordPage.jsx
│   │   │       ├── ForgotPasswordPage.jsx
│   │   │       └── EmailVerificationPage.jsx
│   │   ├── dashboard/            # Dashboard pages
│   │   ├── home/                 # Home page
│   │   ├── labs/                 # Lab pages and components
│   │   ├── layout/               # Layout components (Navbar)
│   │   ├── network/             # Network/Comments pages
│   │   ├── profile/             # Profile pages
│   │   └── shared/              # Shared components
│   │       └── ui/              # Reusable UI components
│   ├── hooks/                   # Custom React hooks
│   │   ├── useAuth.js
│   │   └── useLabs.js
│   ├── services/                # API services
│   │   ├── apiClient.js
│   │   └── commentsService.js
│   ├── data/                    # Mock data and constants
│   ├── styles/                  # Global styles
│   │   └── animations.css
│   ├── App.jsx                  # Root component
│   └── main.jsx                 # Entry point
│
├── server/                      # Backend PHP application
│   ├── auth/                    # Authentication endpoints
│   │   ├── login.php
│   │   ├── send_verification.php
│   │   ├── verify_code.php
│   │   ├── set_password.php
│   │   ├── change_password.php
│   │   ├── forgot_password.php
│   │   ├── reset_password.php
│   │   ├── create_reset_token.php
│   │   └── vendor/              # PHPMailer dependencies
│   ├── api/                     # API endpoints
│   │   └── comments/
│   │       └── index.php
│   ├── utils/                   # Utility files
│   │   ├── db_connect.php      # Database connection
│   │   └── certs/               # SSL certificates (if using)
│   └── sql/                     # SQL scripts
│       ├── ctf_platform.sql     # Main database schema
│       ├── add_password_resets_table.sql
│       └── add_role_requests_table.sql
│
├── public/                      # Static assets
├── package.json                 # Node.js dependencies
├── composer.json                # PHP dependencies
├── vite.config.js               # Vite configuration
├── tailwind.config.js           # Tailwind configuration
└── README.md                     # This file
```

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

### Required Software
- **Node.js** (v16 or higher) - [Download](https://nodejs.org/)
- **npm** or **yarn** (comes with Node.js)
- **PHP** (v7.4 or higher) - [Download](https://www.php.net/downloads.php)
- **MySQL** (v5.7 or higher) - [Download](https://dev.mysql.com/downloads/)
- **Composer** - [Download](https://getcomposer.org/download/)

### Optional (Recommended for Local Development)
- **XAMPP** (includes Apache, MySQL, PHP) - [Download](https://www.apachefriends.org/)
- **phpMyAdmin** (for database management) - Usually included with XAMPP

---

## 🚀 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/Ahmed-Mostafa47/CYBER_OPS_PLATFORM.git
cd CYBER_OPS_PLATFORM
```

### Step 2: Install Frontend Dependencies

```bash
npm install
```

### Step 3: Install Backend Dependencies

```bash
composer install
```

This will install PHPMailer and other PHP dependencies in `server/auth/vendor/`.

---

## 🗄️ Database Setup

### Option 1: Using XAMPP (Recommended for Beginners)

#### Step 1: Start XAMPP Services
1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL** services
3. Ensure both services are running (green status)

#### Step 2: Create Database via phpMyAdmin
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar
3. Enter database name: `ctf_platform`
4. Select collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

#### Step 3: Import SQL Scripts
1. In phpMyAdmin, select the `ctf_platform` database
2. Click on the **"Import"** tab
3. Click **"Choose File"** and select: `server/sql/ctf_platform.sql`
4. Click **"Go"** at the bottom
5. Wait for the import to complete

#### Step 4: Import Additional Tables (if needed)
Repeat the import process for:
- `server/sql/add_password_resets_table.sql`
- `server/sql/add_role_requests_table.sql`

### Option 2: Using MySQL Command Line

#### Step 1: Access MySQL
```bash
# On Windows (if MySQL is in PATH)
mysql -u root -p

# On Linux/Mac
sudo mysql -u root -p
```

#### Step 2: Create Database and Import Schema
```bash
# Create database
CREATE DATABASE ctf_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Use the database
USE ctf_platform;

# Import main schema
SOURCE server/sql/ctf_platform.sql;

# Import additional tables (if needed)
SOURCE server/sql/add_password_resets_table.sql;
SOURCE server/sql/add_role_requests_table.sql;

# Exit MySQL
EXIT;
```

### Option 3: Using MySQL Workbench

1. Open **MySQL Workbench**
2. Connect to your MySQL server
3. Click **File** → **Open SQL Script**
4. Select `server/sql/ctf_platform.sql`
5. Click the **Execute** button (⚡ icon)
6. Repeat for other SQL files if needed

### Verify Database Setup

You can verify the database was created correctly by running:

```bash
# Via MySQL command line
mysql -u root -p -e "USE ctf_platform; SHOW TABLES;"
```

You should see tables like: `users`, `labs`, `challenges`, `roles`, etc.

---

## ⚙️ Configuration

### Step 1: Configure Database Connection

Edit `server/utils/db_connect.php`:

```php
<?php
// For local development with XAMPP
$dbHost = 'localhost';        // or '127.0.0.1'
$dbUser = 'root';             // Your MySQL username
$dbPass = '';                 // Your MySQL password (empty for XAMPP default)
$dbName = 'ctf_platform';     // Database name
$dbPort = 3306;               // Default MySQL port
?>
```

**For XAMPP (default settings):**
- Host: `localhost`
- User: `root`
- Password: (leave empty)
- Port: `3306`

**For custom MySQL installation:**
- Update the credentials according to your setup

### Step 2: Configure Email Settings (Optional)

If you want email verification to work, edit `server/auth/send_verification.php`:

```php
// Gmail SMTP Configuration
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';  // Gmail App Password
```

**To get Gmail App Password:**
1. Go to your Google Account settings
2. Enable 2-Step Verification
3. Go to App Passwords
4. Generate a new app password for "Mail"
5. Use that password in the configuration

**Note:** For local development, you can skip email configuration. The system will use PHP's `mail()` function as fallback.

### Step 3: Configure API Base URL (Frontend)

The frontend is configured to use:
- Development: `http://localhost/graduatoin_project/server/api`
- Update in `src/services/apiClient.js` if your setup differs

---

## ▶️ Running the Project

### Step 1: Start Backend Server

#### Using XAMPP:
1. Ensure **Apache** and **MySQL** are running in XAMPP Control Panel
2. Your PHP files should be accessible at: `http://localhost/graduatoin_project/server/`

#### Using PHP Built-in Server (Alternative):
```bash
# Navigate to server directory
cd server

# Start PHP server (adjust port if needed)
php -S localhost:8000
```

### Step 2: Start Frontend Development Server

```bash
# From project root
npm run dev
```

The frontend will be available at: `http://localhost:5173` (or the port shown in terminal)

### Step 3: Access the Application

1. Open your browser
2. Navigate to: `http://localhost:5173`
3. You should see the login page

### Step 4: Create Your First User

1. Click on **"Register"** or navigate to `/register`
2. Fill in the registration form:
   - Username
   - Email
   - Full Name
3. You'll receive a verification code (check email or console for development)
4. Enter the verification code
5. Set your password
6. You're ready to use the platform!

---

## 📡 API Endpoints

### Authentication Endpoints

Base URL: `http://localhost/graduatoin_project/server/auth/`

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login.php` | User login |
| POST | `/send_verification.php` | Send email verification code |
| POST | `/verify_code.php` | Verify email code |
| POST | `/set_password.php` | Set password after registration |
| POST | `/change_password.php` | Change user password |
| POST | `/forgot_password.php` | Request password reset |
| POST | `/reset_password.php` | Reset password with token |
| POST | `/create_reset_token.php` | Create password reset token |

### API Endpoints

Base URL: `http://localhost/graduatoin_project/server/api/`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/comments/index.php` | Comments management |

---

## 💻 Development

### Available Scripts

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Code Structure Guidelines

- **Frontend**: Feature-based organization in `src/features/`
- **Backend**: Organized by functionality in `server/`
- **Components**: Reusable components in `src/features/shared/`
- **Hooks**: Custom React hooks in `src/hooks/`
- **Services**: API services in `src/services/`

### Database Schema

Key tables:
- `users` - User accounts and profiles
- `labs` - Lab definitions
- `challenges` - Individual challenges
- `testcases` - Test cases for challenges
- `roles` - User roles (admin, instructor, student)
- `user_roles` - User-role assignments
- `password_resets` - Password reset tokens
- `role_requests` - Role upgrade requests

---

## 🚀 Deployment

### Frontend Deployment

```bash
# Build the project
npm run build

# The dist/ folder contains the production build
# Deploy to:
# - Netlify
# - Vercel
# - GitHub Pages
# - Any static hosting service
```

### Backend Deployment

1. Upload `server/` directory to your web server
2. Ensure PHP 7.4+ is installed
3. Configure database connection in `server/utils/db_connect.php`
4. Set up SSL certificates if using HTTPS
5. Configure Apache/Nginx for PHP

### Environment Variables (Production)

For production, consider using environment variables instead of hardcoding credentials:

1. Create `.env` file (not committed to git)
2. Use PHP's `getenv()` or a library like `vlucas/phpdotenv`
3. Update `.gitignore` to exclude `.env` files

---

## 🔧 Troubleshooting

### Database Connection Issues

**Problem:** "Database connection failed"

**Solutions:**
1. Verify MySQL is running
2. Check credentials in `server/utils/db_connect.php`
3. Ensure database `ctf_platform` exists
4. Check MySQL user permissions
5. Verify port number (default: 3306)

### Frontend Not Connecting to Backend

**Problem:** API requests failing

**Solutions:**
1. Verify backend server is running
2. Check CORS settings in PHP files
3. Verify API base URL in `src/services/apiClient.js`
4. Check browser console for errors
5. Ensure Apache is running (if using XAMPP)

### Email Not Sending

**Problem:** Verification emails not received

**Solutions:**
1. Check email configuration in `server/auth/send_verification.php`
2. For Gmail, use App Password (not regular password)
3. Check PHP error logs
4. For local development, check console/terminal for verification codes
5. Verify SMTP settings are correct

### Port Already in Use

**Problem:** "Port 5173 is already in use"

**Solutions:**
```bash
# Kill process on port 5173 (Windows)
netstat -ano | findstr :5173
taskkill /PID <PID> /F

# Kill process on port 5173 (Linux/Mac)
lsof -ti:5173 | xargs kill -9

# Or use a different port
npm run dev -- --port 3000
```

### Module Not Found Errors

**Problem:** "Cannot find module"

**Solutions:**
```bash
# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear npm cache
npm cache clean --force
```

---

## 📝 Additional Notes

### Security Considerations

- Never commit database credentials to version control
- Use environment variables for sensitive data
- Enable HTTPS in production
- Implement rate limiting for API endpoints
- Use prepared statements for all database queries (already implemented)
- Hash passwords using bcrypt (already implemented)

### Development Tips

- Use browser DevTools for debugging
- Check PHP error logs: `C:\xampp\php\logs\php_error_log` (XAMPP)
- Use Postman or curl for API testing
- Enable CORS for local development
- Use React DevTools browser extension

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 👥 Authors

- **Ahmed Mostafa** - [GitHub](https://github.com/Ahmed-Mostafa47)

---

## 🙏 Acknowledgments

- React team for the amazing framework
- Tailwind CSS for the utility-first CSS framework
- PHPMailer for email functionality
- All contributors and users of this platform

---

## 📞 Support

If you encounter any issues or have questions:

1. Check the [Troubleshooting](#-troubleshooting) section
2. Search existing [Issues](https://github.com/Ahmed-Mostafa47/CYBER_OPS_PLATFORM/issues)
3. Create a new issue with detailed information

---

<div align="center">

**CYBER_OPS_PLATFORM** — _Designed for interactive cybersecurity learning environments_ ⚡

[Installation](#-installation) | [Database Setup](#-database-setup) | [Running the Project](#-running-the-project)

</div>
