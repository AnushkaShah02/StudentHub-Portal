# StudentHub Portal — Integrated Academic Management System

**Course:** Web Development Laboratory (Practical 15 Final Integration)  
**Institution:** Charotar University of Science and Technology (CHARUSAT)  
**Branch:** Computer Science & Engineering (Semester 3)  
**Student ID:** 25CS097  
**Student Name:** Anushka Shah  

---

## 1. Project Overview
StudentHub is an integrated, full-stack student portal designed for academic profiling, dynamic schedule tracking, interactive event discovery, attendance monitoring, and administrative governance. The system consolidates all 15 curriculum practicals into a unified architecture.

---

## 2. Practical Implementation Mapping
| Practical | Core Competency | Primary Files |
| :--- | :--- | :--- |
| **Prac 1 & 2** | Requirement Analysis, Sitemap, Wireframe, Semantic HTML5 | `about.html`, `profile.html`, `attendance.html` |
| **Prac 3** | Responsive UI (CSS Grid, Flexbox, Bootstrap 5) | `style.css`, `portal-bootstrap.html` |
| **Prac 4** | DOM Manipulation, Theme Switcher, Modal, Live Clock, Sliders | `script.js`, `dashboard.html` |
| **Prac 5** | Frontend Registration Form & Regular Expression Validation | `register.html`, `register.php` |
| **Prac 6** | Fetch API, Dynamic JSON Rendering, Search, Dependent Dropdowns | `events.html`, `events.json`, `students.json`, `faqs.json` |
| **Prac 7** | Server-Side Validation, CSRF Protection, CSV & JSON File Storage | `process_contact.php`, `data/contacts.csv` |
| **Prac 8** | Relational Database Design, ER Modeling, PDO Connection | `config/db.php`, `studenthub.sql` |
| **Prac 9** | Secure Registration, `password_hash()`, Duplicate Email Check | `register.php` |
| **Prac 10** | Session Authentication, RBAC (Student/Admin), Session Timeout | `login.php`, `logout.php` |
| **Prac 11** | Student Management CRUD with Search & Filter | `admin_students.php` |
| **Prac 12** | Event CRUD with File Upload (MIME & Size Validation) | `admin_events.php`, `uploads/posters/` |
| **Prac 13** | Asynchronous REST JSON API & Fetch/AJAX (Zero Page Reload) | `api.php`, `script.js` |
| **Prac 14** | Admin Dashboard with Aggregations, RBAC Menus, Audit Log | `admin_dashboard.php` |
| **Prac 15** | End-to-End System Integration, Testing, Deployment | Full Suite |

---

## 3. Local Deployment Instructions

### Prerequisites
- Install **XAMPP** (PHP 8.0+ and MySQL).
- Modern web browser (Chrome, Edge, Firefox).

### Setup Steps
1. Place the `studenthub` folder inside your web server directory:
   - Windows: `C:\xampp\htdocs\studenthub\`
   - macOS / Linux: `/opt/lampp/htdocs/studenthub/` or `/var/www/html/studenthub/`
2. Start the **Apache** and **MySQL** services in the XAMPP Control Panel.
3. Open your browser and go to `http://localhost/phpmyadmin/`.
4. Create a new database named **`studenthub`**.
5. Select the `studenthub` database, click the **Import** tab, choose `studenthub.sql`, and click **Import**.
6. Access the application in your browser:
   - **Login Gateway:** `http://localhost/studenthub/login.php`
   - **Student Dashboard:** `http://localhost/studenthub/dashboard.html`
   - **Admin Command Center:** `http://localhost/studenthub/admin_dashboard.php`

---

## 4. Default Demonstration Accounts
- **Administrator Role:**
  - Email: `admin@charusat.edu.in`
  - Password: `Admin@1234`
- **Student Role:**
  - Email: `anushka.shah@student.edu`
  - Password: `Admin@1234`