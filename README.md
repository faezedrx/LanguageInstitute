# LanguageInstitute 🎓

A web-based **Language Institute Management System** built with PHP and MySQL.

LanguageInstitute is designed to help language schools manage students, teachers, evaluations, placement tests, and administrative tasks through a simple and practical dashboard.

---

## 🚀 Features

### 🔐 Authentication System
- Secure user login system
- Role-based access control
- Different dashboards based on user permissions

Supported roles:
- Admin
- Education department
- Office staff
- Teachers

---

## 📊 Dashboard

The dashboard provides:

- Quick access to institute management sections
- User-specific features
- Teacher performance ranking
- Evaluation statistics
- Student management tools

---

## 📝 Teacher Evaluation System

Manage teacher performance through evaluation forms.

Features:

- Student feedback collection
- Teacher score calculation
- Automatic ranking system
- Top teacher leaderboard

The system calculates teacher rankings based on evaluation scores.

---

## 🎯 Placement Test System

Manage student level assessment.

Features:

- Student level determination
- Registration and evaluation of placement results
- Dynamic level management

---

## 👨‍🏫 Teacher Management

Teachers can:

- View evaluation reports
- Monitor performance results
- Access their assigned information

Administrators can analyze teacher performance using ranking reports.

---

## 🗂 Project Structure
LanguageInstitute/

│
├── index.php # Main page
├── login.php # Authentication
├── logout.php # Logout handler
├── dashboard.php # User dashboard
├── config.php # Configuration
│
├── evaluation/ # Teacher evaluation module
│
├── level/ # Placement test module
│
├── school/ # School management module
│
└── localhost.sql # Database structure


---

## 🛠 Technologies

- PHP
- MySQL
- HTML5
- CSS
- JavaScript

---

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/faezedrx/LanguageInstitute.git
```

## 2. Create Database

Import the database file:
```bash
localhost.sql
```
into MySQL.

Example:
```bash
CREATE DATABASE language_institute;
```

Then import:
```bash
mysql -u root -p language_institute < localhost.sql
```

## 3. Configure Database
Edit:
```bash
config.php
```

Set your database credentials:
```bash
$host = "localhost";
$username = "root";
$password = "";
$database = "language_institute";
```

4. Run Project
Move the project into your web server directory:

Example:
```bash
htdocs/LanguageInstitute
```

Run:
```bash
http://localhost/LanguageInstitute
```
---

## 🔑 User Roles
| Role      | Description                  |
| --------- | ---------------------------- |
| Admin     | Full system management       |
| Office    | Administrative operations    |
| Education | Educational management       |
| Teacher   | View reports and evaluations |

---


## 📈 Teacher Ranking
The system includes a ranking mechanism that:

Calculates teacher scores
Sorts teachers based on performance
Displays top performers

---

## 🔒 Security
Implemented security concepts:

Session-based authentication
Role validation
Input escaping
Database connection management

---

## 📌 Future Improvements
Possible enhancements:

Student online portal
Attendance management
Payment management
SMS notification system
Advanced analytics dashboard
REST API integration
Improved UI/UX

---

## 👩‍💻 Developer
Developed by Faeze Darbeheshti

GitHub:

https://github.com/faezedrx



