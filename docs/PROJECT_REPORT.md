# SmartHire Pro — Final Project Report

## Abstract

SmartHire Pro is a web-based Recruitment & Placement Management System designed to streamline hiring for job seekers, recruiters, and platform administrators. The system provides profile management, job search, application tracking, recruiter tools, and admin analytics in a modern SaaS-style interface.

## 1. Introduction

Traditional recruitment processes involve fragmented tools for posting jobs, reviewing resumes, and tracking candidate status. SmartHire Pro consolidates these workflows into a single platform with role-based dashboards and real-time notifications.

## 2. Objectives

### Functional Requirements
- Multi-role authentication (Admin, Recruiter, Job Seeker)
- Email OTP verification and password reset
- Job posting, search, and application management
- Application status pipeline (6 stages)
- Profile strength analyzer
- Admin analytics with Chart.js

### Non-Functional Requirements
- Responsive design for mobile devices
- Secure password hashing (bcrypt)
- PDO prepared statements (SQL injection prevention)
- CSRF protection on forms
- Activity and admin audit logs

## 3. System Design

### Architecture
Three-tier architecture: Presentation (Bootstrap/HTML), Application (PHP services), Data (MySQL).

### Database Tables
`users`, `roles`, `companies`, `jobs`, `applications`, `saved_jobs`, `notifications`, `otp_verifications`, `activity_logs`, `admin_logs`, `job_seeker_profiles`

### User Roles
| Role | Capabilities |
|------|-------------|
| Job Seeker | Profile, apply, save jobs, track applications |
| Recruiter | Company profile, post jobs, manage applicants |
| Admin | Platform oversight, analytics, user management |

## 4. Implementation

### Technologies
- PHP 8, MySQL, Bootstrap 5, JavaScript, Chart.js, PHPMailer, AJAX

### Key Modules
1. **Authentication** — Registration, login, OTP, RBAC
2. **Job Seeker** — Profile strength, job search, applications
3. **Recruiter** — Company & job management, applicant review
4. **Admin** — Dashboard charts, user/recruiter approval
5. **AJAX** — Live job search, notifications, status updates

### Security Measures
- `password_hash()` / `password_verify()`
- Session regeneration on login
- CSRF tokens
- File upload validation (type & size)
- XSS prevention via `htmlspecialchars()`

## 5. Testing

| ID | Test Case | Expected Result |
|----|-----------|-----------------|
| T01 | Register job seeker + OTP | Account verified, dashboard access |
| T02 | Recruiter posts job | Job visible after company approved |
| T03 | Apply duplicate job | Error: already applied |
| T04 | Admin blocks user | User cannot login |
| T05 | AJAX search "developer" | Results without page reload |
| T06 | Update application status | Job seeker receives notification |
| T07 | Profile strength calculator | Score updates based on completeness |
| T08 | Dark mode toggle | Theme persists via localStorage |

## 6. Results

SmartHire Pro delivers a complete recruitment platform suitable for:
- Software engineering capstone projects
- Internship/placement portfolios
- LinkedIn project showcases
- Real-world deployment on free hosting

## 7. Future Enhancements

- AI-powered resume parsing and job matching
- In-app messaging between recruiters and candidates
- Video interview scheduling integration
- Multi-language support
- Advanced reporting with PDF export

## 8. References

- Bootstrap 5 Documentation — https://getbootstrap.com
- Chart.js Documentation — https://www.chartjs.org
- PHPMailer — https://github.com/PHPMailer/PHPMailer
- PHP PDO — https://www.php.net/manual/en/book.pdo.php

## 9. Conclusion

SmartHire Pro successfully implements a modern recruitment SaaS platform with distinct role-based experiences, secure authentication, analytics dashboards, and responsive design — exceeding basic job board functionality through pipeline tracking, profile analysis, and real-time notifications.
