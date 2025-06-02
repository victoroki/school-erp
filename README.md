# School ERP System

A comprehensive School Enterprise Resource Planning (ERP) system built with Laravel 10 and Bootstrap, designed to streamline and automate all aspects of educational institution management.

## 🏫 Overview

This School ERP System is a complete solution for managing educational institutions, from small schools to large colleges. It provides a centralized platform to handle academic, administrative, and financial operations efficiently.

## ✨ Features

### 🔐 User Management
- **Role-Based Access Control**: Comprehensive role and permission management
- **User Roles**: Admin, Teachers, Students, Parents, Staff
- **Permission System**: Granular control over user access and capabilities

### 🎓 Academic Management
- **Academic Year Management**: Define and manage academic sessions
- **Class & Section Management**: Organize classes and sections
- **Subject Management**: Create subjects and assign to classes
- **Teacher-Subject Assignment**: Link teachers with their subjects
- **Period & Timetable Management**: Schedule classes and periods
- **Classroom Management**: Manage physical classroom resources

### 👨‍🎓 Student Management
- **Student Registration**: Complete student profile management
- **Class Enrollment**: Enroll students in classes and sections
- **Parent Relationships**: Link students with their parents/guardians
- **Document Management**: Store and manage student documents
- **Student History**: Track academic progress and records

### 📝 Examination Management
- **Exam Types**: Define different types of examinations
- **Grading Scales**: Flexible grading and marking systems
- **Exam Scheduling**: Plan and schedule examinations
- **Result Management**: Record and manage exam results
- **Report Cards**: Generate comprehensive academic reports

### 👥 Human Resources
- **Department Management**: Organize staff by departments
- **Job Position Management**: Define roles and positions
- **Staff Document Management**: Store employee documents
- **Leave Management**: Handle leave requests and approvals
- **Payroll System**: Manage staff salaries and payments

### 💰 Financial Management
- **Expense Tracking**: Monitor and categorize expenses
- **Income Management**: Track various income sources
- **Bank Account Integration**: Manage multiple bank accounts
- **Financial Reports**: Generate financial statements and reports

### 🏷️ Fee Management
- **Fee Categories**: Define different types of fees
- **Fee Structures**: Create flexible fee structures
- **Student Discounts**: Manage fee discounts and scholarships
- **Payment Tracking**: Monitor fee payments and dues

### 📚 Library Management
- **Book Cataloging**: Organize books by categories
- **Inventory Management**: Track book availability
- **Member Management**: Handle library memberships
- **Issue/Return System**: Manage book lending

### 📦 Inventory Management
- **Item Categorization**: Organize inventory by categories
- **Stock Management**: Track inventory levels and movements
- **Supplier Management**: Maintain supplier information
- **Purchase Orders**: Handle procurement processes

### 🏠 Hostel Management
- **Hostel Administration**: Manage multiple hostels
- **Room Management**: Track room availability and assignments
- **Student Allocation**: Assign students to hostel rooms
- **Hostel Fees**: Manage hostel-related charges

### 🚌 Transportation
- **Route Management**: Define bus routes and schedules
- **Stop Management**: Manage pickup and drop points
- **Student Transport**: Assign students to routes
- **Driver Management**: Handle driver information

### 📱 Communication
- **SMS Templates**: Pre-defined message templates
- **Email Templates**: Automated email communications
- **Notification System**: Send alerts and announcements
- **Parent Communication**: Direct communication channels

## 🛠️ Technology Stack

- **Backend**: Laravel 10 (PHP 8.1+)
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Database**: MySQL 8.0+
- **Icons**: Font Awesome 5+
- **Authentication**: Laravel Authentication
- **Authorization**: Laravel Policies & Gates

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Node.js & NPM (for asset compilation)
- Web server (Apache/Nginx)

## 🚀 Installation

```bash
git clone https://github.com/yourusername/school-erp.git
cd school-erp

composer install

npm install

cp .env.example .env
php artisan key:generate

# Edit .env with your DB credentials
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=school_erp
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

php artisan migrate --seed

npm run dev

php artisan serve
