# 🚌 University Bus Transport Management System

A web-based transport management application for managing university bus fleets, routes, schedules, stops, drivers, and student/faculty registrations.

## 🌟 Key Features

### 🛡️ Admin Module
- **Fleet Management**: Add, edit, and delete buses (Student & Faculty types, seating capacity).
- **Driver Records**: Manage driver profiles, phone numbers, and licenses.
- **Route Network**: Configure route origins, destinations, distances, and travel durations.
- **Bus Stop Management**: Add pickup/dropoff stops linked directly to routes.
- **Master Schedules**: Link buses to routes with departure and return times.
- **User Management**: Full Add, Edit, Delete control over Student (`manage_students.php`) and Faculty (`manage_faculty.php`) accounts.

### 🎓 Student Portal
- **Self-Registration**: Register with Student ID generation.
- **Role-Filtered Bus Stops**: Select preferred bus stops filtered for Student routes.
- **Schedule Viewer**: Access real-time student bus schedules and driver contacts.

### 👨‍🏫 Faculty Portal
- **Self-Registration**: Register with Faculty ID generation.
- **Role-Filtered Bus Stops**: Select preferred bus stops filtered for Faculty routes.
- **Schedule Viewer**: View faculty bus schedules alongside student schedule overviews.
- **Department Student Directory**: View students in the faculty member's department.

---

## 🛠️ Tech Stack
- **Language**: PHP
- **Database**: MySQL / MariaDB (via XAMPP)
- **Styling**: Vanilla CSS (Responsive Dark Mode UI)
- **Web Server**: Apache

---

## 🚀 Setup & Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/masud956/UniversityBusTransport.git
   ```
2. Move the project folder into your web server directory (e.g. `C:\xampp\htdocs\UniversityBusTransport`).
3. Start **Apache** and **MySQL** in XAMPP.
4. **Import Database**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin/`).
   - Create a new database named `universitybustransport`.
   - Import the `universitybustransport.sql` file included in this repository.
5. Open application in browser:
   - `http://localhost/UniversityBusTransport/login.php`

---

## 🔑 Test Credentials

- **Admin**: `admin@university.edu` / `admin123`
- **Student**: `student@university.edu` / `student123`
- **Faculty**: `faculty@university.edu` / `faculty123`
