Faculty Evaluation System
> AI-powered faculty performance evaluation platform built for Mindanao State University – Sulu

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

About the Project

This is my **capstone project** — a comprehensive web-based Faculty Evaluation System developed over 5 months for Mindanao State University – Sulu. It automates the faculty performance review process by collecting student feedback and using AI-powered sentiment analysis to generate meaningful insights for academic administrators.

Built with the assistance of AI development tools, this project demonstrates my ability to plan, design, and deliver a real institutional system from scratch.

Features

- **Multi-role Authentication** — Separate dashboards for Admin, Faculty, and Students
- **Evaluation Form System** — Students submit structured feedback on faculty performance
- **AI Sentiment Analysis** — Automatically classifies feedback as Positive, Neutral, or Negative
- **Analytics Dashboard** — Visual charts and performance summaries for administrators
- **Faculty Reports** — Individual performance breakdowns per faculty member
- **Semester Management** — Evaluation periods tied to academic semesters
- **Secure Login System** — Role-based access control throughout the platform

Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (Procedural) |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| AI Feature | Sentiment Analysis Integration |
| Server | Apache (XAMPP) |

 Screenshots

> Dashboard Overview

![Dashboard](screenshots/dashboard.png)

> Evaluation Form

![Evaluation Form](screenshots/evaluation-form.png)

> Analytics & Reports

![Analytics](screenshots/analytics.png)


 Getting Started

Prerequisites
- XAMPP (or any Apache + PHP + MySQL stack)
- PHP 7.4+
- MySQL 5.7+

Installation

1. Clone the repository
bash
git clone https://github.com/eyaafar/faculty-evaluation-system.git


2. Move the project folder to your XAMPP `htdocs` directory
bash
mv faculty-evaluation-system /xampp/htdocs/


3. Import the database
- Open **phpMyAdmin** at `http://localhost/phpmyadmin`
- Create a new database: `faculty_eval_db`
- Import the file: `database/faculty_eval_db.sql`

4. Configure the database connection
php
// config/db.php
$host = 'localhost';
$dbname = 'faculty_eval_db';
$username = 'root';
$password = '';


5. Run the project

http://localhost/faculty-evaluation-system/


Default Login Credentials
| Role | Username | Password |
|---|---|---|
| Admin | admin | admin123 |
| Faculty | faculty | faculty123 |
| Student | student | student123 |


Project Structure

faculty-evaluation-system/
├── config/
│   └── db.php              # Database connection
├── admin/
│   ├── dashboard.php       # Admin dashboard
│   ├── reports.php         # Faculty reports
│   └── manage-users.php    # User management
├── faculty/
│   └── dashboard.php       # Faculty view
├── student/
│   ├── dashboard.php       # Student portal
│   └── evaluate.php        # Evaluation form
├── assets/
│   ├── css/                # Stylesheets
│   └── js/                 # Scripts
├── database/
│   └── faculty_eval_db.sql # Database schema + seed data
└── index.php               # Entry point / Login

 What I Learned

- Designing relational database schemas for multi-role systems
- Implementing secure role-based authentication in PHP
- Integrating AI-assisted sentiment analysis into a web application
- Building data visualization dashboards using JavaScript charts
- Managing a full project lifecycle from planning to delivery


Developer

Farhiya Ayyub
BS Computer Science 
Mindanao State University – Sulu | Class of 2026

[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/farhiya-ayyub-920605412)
[![Portfolio](https://img.shields.io/badge/Portfolio-e63946?style=flat&logo=firefox&logoColor=white)](https://github.com/eyaafar)


> *Built with AI-assisted development tools as part of my Computer Science capstone, 2026.*
