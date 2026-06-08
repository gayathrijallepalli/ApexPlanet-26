# SmartHire Pro

**Recruitment & Placement Management System** — a production-grade capstone project built with PHP 8, MySQL, Bootstrap 5, and AJAX.

![Tech Stack](https://img.shields.io/badge/PHP-8-blue) ![MySQL](https://img.shields.io/badge/MySQL-8-orange) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple)

## Features

### Job Seekers
- Profile with photo, education, skills, experience, resume upload
- **Resume Strength Analyzer** (weighted score out of 100%)
- AJAX job search with filters (location, experience, salary, job type)
- Apply, save jobs, track application pipeline
- Real-time notifications

### Recruiters
- Company profile management (logo, description, website)
- Create, edit, delete job postings
- Review applicants, download resumes, update application status

### Admin
- Platform dashboard with Chart.js analytics
- Approve recruiters, block/delete users
- Manage all jobs, view reports and admin logs

### Platform
- Email OTP verification (dev mode: OTP shown on screen + logged)
- Forgot password with OTP
- Role-based access control (RBAC)
- Dark mode toggle
- Fully responsive mobile design

## Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Backend | PHP 8 |
| Database | MySQL |
| Charts | Chart.js |
| Email | PHPMailer |
| Server | XAMPP (local) / InfinityFree (deploy) |

## Quick Start (XAMPP)

1. Copy project to `C:\xampp\htdocs\Task-5`
2. Start **Apache** and **MySQL** in XAMPP
3. Open `http://localhost/Task-5/install.php` to create database and admin user
4. Login as admin:
   - **Email:** `admin@smarthire.pro`
   - **Password:** `Admin@123`
5. Delete `install.php` after setup (production)

### Optional: Composer (PHPMailer for production email)

```bash
cd C:\xampp\htdocs\Task-5
composer install
```

Copy `config/app.example.php` to `config/app.php` and configure SMTP for live email.

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@smarthire.pro | Admin@123 |

> Change the admin password immediately after first login in production.

## Project Structure

```
Task-5/
├── admin/          # Admin dashboard & controls
├── auth/           # Login, register, OTP, password reset
├── jobseeker/      # Job seeker dashboard & features
├── recruiter/      # Recruiter dashboard & job management
├── ajax/           # AJAX API endpoints
├── assets/         # CSS, JS, images
├── config/         # App & database configuration
├── database/       # SQL schema
├── includes/       # Shared PHP includes
├── services/       # Business logic services
├── uploads/        # Resumes, photos, logos
└── docs/           # Documentation
```

## Documentation

- [Installation Guide](docs/INSTALLATION.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
- [Project Report](docs/PROJECT_REPORT.md)

## Color Theme

| Token | Hex |
|-------|-----|
| Primary | `#0F172A` |
| Secondary | `#2563EB` |
| Accent | `#14B8A6` |
| Background | `#F8FAFC` |

## License

MIT — free for portfolio and educational use.
