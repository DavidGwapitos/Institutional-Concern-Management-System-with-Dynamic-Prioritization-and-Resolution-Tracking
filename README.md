# ICMS-DPT-RRT: Institutional Concern Management System with Dynamic Prioritization and Resolution Tracking

**Institutional Context**: Surigao del Norte State University (SNSU) — College of Computing & Information Sciences  
**Degree Program**: Bachelor of Science in Information Technology  
**Authors**: Curt Chesril M. Tan, Charles David V. Felominos (May 2026)  
**Technology Stack**: Vanilla PHP (8.x), Semantic HTML5, Custom Vanilla CSS, Vanilla JavaScript (ES6+), MySQL (via PDO)  
**Dependencies**: **0 external dependencies** (No Composer, no Node.js/npm required).

---

## Table of Contents
1. [System Prerequisites](#system-prerequisites)
2. [Quick Start Guide (Method 1: PHP Built-in Server)](#quick-start-guide-method-1-php-built-in-server)
3. [WampServer / XAMPP Guide (Method 2: Apache Web Server)](#wampserver--xampp-guide-method-2-apache-web-server)
4. [Database Configuration & Auto-Setup](#database-configuration--auto-setup)
5. [Demo User Accounts](#demo-user-accounts)
6. [Automated Verification Suite](#automated-verification-suite)
7. [System Features & User Guide](#system-features--user-guide)
8. [AI Dynamic Prioritization Engine (DPT-RRT)](#ai-dynamic-prioritization-engine-dpt-rrt)
9. [Project Directory Structure](#project-directory-structure)
10. [Troubleshooting & Common Questions](#troubleshooting--common-questions)

---

## System Prerequisites

Before launching the system, ensure the following are installed on your Windows machine:
1. **PHP 8.0 or higher** (CLI or installed via WampServer / XAMPP)
   - Verify in terminal: `php -v`
2. **MySQL 5.7+ or MySQL 8.x / MariaDB**
   - Active on default port **3306** (via WampServer, XAMPP, or Windows MySQL Service)
3. **Web Browser** (Google Chrome, Microsoft Edge, Mozilla Firefox, or Safari)

---

## Quick Start Guide (Method 1: PHP Built-in Server)

*Recommended for thesis presentations, defense demonstrations, and quick local testing.*

### Step 1: Ensure MySQL is Running
- If you use **WampServer**: Launch WampServer and ensure the system tray icon is **green** (Apache & MySQL services active).
- If you use **XAMPP**: Open the XAMPP Control Panel and start **MySQL**.
- If you use **Windows MySQL Service**: Ensure the `MySQL80` service is running.

### Step 2: Open Terminal in Project Directory
Open **PowerShell** or **Command Prompt** and navigate to the project directory:
```powershell
cd c:\ICMS-DPT-RRT
```

### Step 3: Start the PHP Web Server
Execute the following command:
```powershell
php -S 127.0.0.1:8000
```
*Output will indicate:*
```
[Mon Sep  8 03:00:00 2026] PHP 8.5.0 Development Server (http://127.0.0.1:8000) started
```

### Step 4: Access in Your Browser
Open your browser and visit:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

> [!NOTE]
> **Zero Setup Database**: On your very first visit, [config/db.php](file:///c:/ICMS-DPT-RRT/config/db.php) will **automatically** create the `icms_dpt_rrt` database, build all 8 tables, and populate seed data (`SCF-2024-001` to `SCF-2024-032`). No manual SQL imports are required!

---

## WampServer / XAMPP Guide (Method 2: Apache Web Server)

If you prefer serving through Apache (`http://localhost/ICMS-DPT-RRT`):

### Option A: Using WampServer (`wamp64`)
1. Copy or move the `ICMS-DPT-RRT` folder into your WampServer document root:
   ```
   C:\wamp64\www\ICMS-DPT-RRT
   ```
2. Start WampServer from your Start Menu.
3. Open your browser and navigate to:
   👉 **`http://localhost/ICMS-DPT-RRT/`**

### Option B: Using XAMPP
1. Copy or move the `ICMS-DPT-RRT` folder into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\ICMS-DPT-RRT
   ```
2. In the XAMPP Control Panel, click **Start** for both **Apache** and **MySQL**.
3. Open your browser and navigate to:
   👉 **`http://localhost/ICMS-DPT-RRT/`**

---

## Database Configuration & Auto-Setup

The database connection is managed centrally in [config/db.php](file:///c:/ICMS-DPT-RRT/config/db.php).

### Default Settings
| Setting | Default Value | Description |
| :--- | :--- | :--- |
| **Host** | `127.0.0.1` | Local MySQL server host |
| **Port** | `3306` | Standard MySQL port |
| **Username** | `root` | Standard local development user |
| **Password** | `""` (blank) | Default WAMP/XAMPP password |
| **Database Name**| `icms_dpt_rrt` | Auto-created if missing |

### How to Change Database Credentials
If your MySQL setup uses a custom password or port, open `config/db.php` and edit lines 9–13:
```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
define('DB_NAME', 'icms_dpt_rrt');
```

### Manual Database Import (Optional)
If you ever want to re-import or inspect the raw schema using **phpMyAdmin** (`http://localhost/phpmyadmin`):
1. Open phpMyAdmin.
2. Click **Import** -> Browse for [database/schema.sql](file:///c:/ICMS-DPT-RRT/database/schema.sql) -> Click **Go**.
3. Click **Import** -> Browse for [database/seed.sql](file:///c:/ICMS-DPT-RRT/database/seed.sql) -> Click **Go**.

---

## Demo User Accounts

The login screen ([index.php](file:///c:/ICMS-DPT-RRT/index.php)) includes **1-Click Demo Fill Buttons** to speed up demonstrations during presentations.

### 1. Student Account
- **Identifier**: `juan.delacruz@student.com` (or Student No: `SNSU-2022-04189`)
- **Password**: `student123`
- **Role**: Student (BS Information Technology)
- **Portal Access**: Submit Concerns, Real-time AI preview, Track Status Timeline, Submit 5-Star Feedback, View Announcements, Edit Profile.

### 2. Administrator Account
- **Identifier**: `admin@school.edu`
- **Password**: `admin123`
- **Role**: Institutional Administrator
- **Portal Access**: Admin Dashboard, Manage Concerns with Multi-Filter, Rapid Response Team (RRT) Alerts, Manage Users (Add/Deactivate/Delete), Publish Announcements, Review Feedback, Reports & ISO 25010 Compliance.

### Additional Pre-Seeded Students (Password: `student123`)
- Maria Santos: `maria.santos@school.edu` (BS Computer Science)
- John Reyes: `john.reyes@school.edu` (BS Industrial Technology)
- Pedro Reyes: `pedro.reyes@school.edu` (BS Civil Engineering)
- Anne Garcia: `anne.garcia@school.edu` (BSEd English)
- Mark Villanueva: `mark.villanueva@school.edu` (BS Information Systems)

---

## Automated Verification Suite

A full automated diagnostic test script is included in the workspace to verify that all parts of the application are working properly.

### Running the Test Suite
Open PowerShell in the workspace root and run:
```powershell
pwsh -File ./test_system.ps1
```

### What the Test Suite Checks:
1. **PHP CLI Environment**: Confirms PHP 8.x is functional.
2. **Database Connectivity**: Confirms MySQL connection and verifies all 8 relational tables.
3. **Web Server Status**: Verifies HTTP 200 response from the login portal.
4. **AI Dynamic Prioritization Engine**: Validates live NLP hazard detection, urgency scoring, SLA calculation, and RRT trigger flags.
5. **Seeded Accounts**: Checks that student demo records and tickets (`SCF-2024-001` through `SCF-2024-032`) are loaded.
6. **Code Syntax**: Performs `php -l` lint checks on **100% of project PHP files** with zero syntax errors.

---

## System Features & User Guide

### Student Portal Guide
1. **Login** (`index.php`):
   - Enter your student email or student number.
   - Click **Login** to enter the Student Dashboard.
2. **Dashboard** (`student/dashboard.php`):
   - View your 4 concern metric cards (*Total Concerns*, *Pending*, *In Progress*, *Resolved*).
   - Review your recent tickets and latest campus bulletins.
3. **Submit a Concern** (`student/submit_concern.php`):
   - Choose a Category (*Facilities*, *Academic*, *Services*, *Student Welfare*).
   - Enter Title and Detailed Description.
   - **Watch the AI Dynamic Urgency Meter**: As you type words like *"water leak"*, *"sparking"*, *"exposed wire"*, the score bar dynamically climbs to **Critical** and displays the target SLA deadline.
   - Attach supporting evidence (JPG, PNG, PDF).
   - Click **Submit Concern**.
4. **My Concerns & Tracking** (`student/my_concerns.php`):
   - Search and filter your submitted concerns.
   - Click the view icon to view the ticket's resolution progress.
5. **Ticket Details & Resolution Timeline** (`student/concern_details.php`):
   - View step progress tracker (*Pending ➔ In Progress ➔ Resolved*).
   - Download submitted attachments.
   - Read official administrator responses, department endorsements, and timestamped actions.
6. **Submit Feedback** (`student/feedback.php`):
   - Rate your experience from 1 to 5 stars.
   - Submit suggestions for system and facility improvements.

---

### Administrator Portal Guide
1. **Admin Login** (`index.php`):
   - Click **"Login as Administrator"** or use the **"Admin User"** 1-click button.
2. **Admin Dashboard** (`admin/dashboard.php`):
   - System-wide metric overview (**32 Total Concerns, 12 Pending, 10 In Progress, 10 Resolved**).
   - **Rapid Response Team (RRT) Banner**: Displays flashing urgent alerts for Critical tickets needing immediate intervention.
3. **Manage Concerns** (`admin/concerns.php`):
   - Multi-filter tickets by Category, Status, or Priority.
   - Live search by student name or ticket ID.
   - Click to view or respond.
4. **Respond & Endorse Concern** (`admin/concern_details.php`):
   - Review student issue details and evidence.
   - Select updated status (*In Progress* or *Resolved*).
   - Endorse to a university office (*Facilities & Maintenance*, *CCIS Dean Office*, *Registrar*, *MIS*).
   - Click **Quick Response Templates** for 1-click professional response text.
   - Click **Publish Response & Update Status**.
5. **Manage Users** (`admin/users.php`):
   - View registered students and staff.
   - Click **+ Add User** to register new accounts.
   - Toggle user accounts between **Active** and **Inactive**.
6. **Announcements** (`admin/announcements.php`):
   - Click **+ New Announcement** to post bulletins.
   - Check **Mark as Urgent** to display high-priority alerts to students.
7. **Reports & Monitoring** (`admin/reports.php`):
   - View resolution efficiency rates and average turnaround time.
   - Review **ISO 25010 Software Quality Benchmarks** (Usability: 4.88, Reliability: 4.82, Efficiency: 4.90, Security: 4.95).
   - Click **Print / Export Report** for a clean, print-formatted PDF summary.

---

## AI Dynamic Prioritization Engine (DPT-RRT)

The **DPT-RRT** engine is implemented through dual client-server modules:

### Urgency Scoring Matrix
| Priority Tier | Score Range | SLA Deadline | Action Triggered |
| :--- | :--- | :--- | :--- |
| **Critical** | `80 – 100` | **24 Hours** | **Rapid Response Team (RRT)** Alert Banner triggered on Admin Dashboard; immediate priority. |
| **High** | `60 – 79` | **48 Hours** | Elevated priority for midterm exams, classroom equipment, or room lockouts. |
| **Medium** | `40 – 59` | **72 Hours** | Standard processing for sanitation, Wi-Fi, or food service complaints. |
| **Low** | `1 – 39` | **120 Hours** | General inquiries, cosmetic repairs, and suggestions. |

### Keyword Dictionaries
- **Critical Safety Keywords**: `exposed wire`, `wiring`, `spark`, `fire`, `smoke`, `short circuit`, `water leak`, `flooding`, `ceiling collapsed`, `medical emergency`, `hazard`.
- **High Operational Keywords**: `broken chair`, `projector not working`, `exam`, `examination`, `midterm`, `deadline`, `missing grade`, `server down`.
- **Medium Service Keywords**: `internet`, `wifi`, `restroom`, `canteen`, `printer jam`, `schedule conflict`.

### REST API Usage
Developers can query the prioritization engine programmatically:
```http
GET /api/ai_prioritize.php?title=exposed%20wire&description=water%20leak%20near%20terminals&category=Facilities
```
**JSON Output**:
```json
{
  "status": "success",
  "data": {
    "score": 100,
    "priority": "Critical",
    "sla_hours": 24,
    "is_rrt_alert": 1,
    "reasons": [
      "Severe hazard keywords detected: exposed wire, wire, water leak"
    ]
  },
  "engine": "ICMS-DPT-RRT NLP Urgency & SLA Engine v2.0"
}
```

---

## Project Directory Structure

```
c:\ICMS-DPT-RRT\
├── actions/                     # Action form processors
│   ├── announcement_action.php  # Handles create & delete announcements
│   ├── auth_action.php          # Student & admin login authenticator
│   ├── concern_action.php       # Concern submission & file upload handler
│   ├── feedback_action.php      # Student feedback & star rating processor
│   ├── profile_action.php       # Profile details & password updater
│   ├── response_action.php      # Admin response & status transition handler
│   └── user_action.php          # User CRUD & active/inactive toggle
├── admin/                       # Administrator Portal Screens
│   ├── announcements.php        # Manage & publish announcements (Figure 12)
│   ├── concern_details.php      # Review concern & respond center
│   ├── concerns.php             # Manage Concerns with multi-filter (Figure 15)
│   ├── dashboard.php            # Admin Dashboard & RRT Alert Feed (Figure 10)
│   ├── feedback.php             # Review student feedback & ratings (Figure 13)
│   ├── profile.php              # Administrator profile (Figure 14)
│   ├── reports.php              # SLA turnaround & ISO 25010 compliance reports
│   └── users.php                # Manage user accounts (Figure 11)
├── api/
│   └── ai_prioritize.php        # JSON REST API endpoint for DPT-RRT engine
├── assets/
│   ├── css/
│   │   ├── base.css             # Typography, reset, utility classes
│   │   ├── components.css       # Cards, metrics, tables, pills, buttons, modals
│   │   ├── layout.css           # App shell, responsive header, and sidebar
│   │   ├── timeline.css         # Step progression bar & audit trail styles
│   │   └── variables.css        # SNSU institutional color tokens & theme
│   └── js/
│       ├── ai_preview.js        # Live client-side dynamic prioritization preview
│       ├── filter.js            # Real-time multi-filter and table searching
│       └── main.js              # Mobile sidebar, modal controls, star ratings
├── config/
│   └── db.php                   # PDO database connection & auto-migrator
├── database/
│   ├── schema.sql               # Relational MySQL schema (Page 34 Class Diagram)
│   └── seed.sql                 # Demo records matching Figures 1-15
├── includes/                    # Reusable layout partials & guards
│   ├── auth_check.php           # Session authentication guards
│   ├── footer.php               # Footer partial & Figure 9 Logout modal
│   ├── header.php               # HTML head and stylesheets
│   ├── helpers.php              # DPT-RRT engine, XSS sanitizer, badge helpers
│   ├── navbar.php               # Top navigation bar (Figures 2 & 10)
│   └── sidebar.php              # Contextual navigation sidebar
├── student/                     # Student Portal Screens
│   ├── announcements.php        # Campus announcements feed (Figure 7)
│   ├── concern_details.php      # Concern Details & Response Timeline (Figure 6)
│   ├── dashboard.php            # Student Dashboard (Figure 2)
│   ├── feedback.php             # 5-Star Feedback Form (Figure 3)
│   ├── my_concerns.php          # My Concerns table (Figure 5)
│   ├── profile.php              # Student profile management (Figure 8)
│   └── submit_concern.php       # Concern submission with AI Preview (Figure 4)
├── uploads/                     # Storage folder for student file attachments
├── index.php                    # Split-screen Login Page (Figure 1)
├── logout.php                   # Safe session destruction script
├── test_system.ps1              # Automated PowerShell verification test suite
└── README.md                    # System documentation & setup guide
```

---

## Troubleshooting & Common Questions

### 1. Port 8000 is already in use
If port 8000 is occupied by another application, launch PHP on another port:
```powershell
php -S 127.0.0.1:8080
```
Then visit `http://127.0.0.1:8080`.

### 2. "Database Connection Error" appears on the screen
Ensure MySQL is actively running:
- Open WampServer or XAMPP and confirm MySQL is running.
- In PowerShell, run: `Test-NetConnection -ComputerName 127.0.0.1 -Port 3306` to verify the port is responding.

### 3. How do I reset the database to its original demo state?
Open MySQL CLI or phpMyAdmin and execute:
```sql
DROP DATABASE IF EXISTS `icms_dpt_rrt`;
```
Then refresh any page in your browser. `config/db.php` will automatically re-create the database and re-seed all initial records.

### 4. File attachments not uploading
Ensure the `uploads/` directory exists and is writeable. The system automatically creates `uploads/` with proper permissions on the first upload. Allowed file extensions are `.jpg`, `.jpeg`, `.png`, `.pdf`, and `.docx` up to 5MB.

---

## Academic Citation

```
Tan, C. C. M., & Felominos, C. D. V. (2026). 
An AI-Powered Institutional Concern Management System with Dynamic Prioritization and Resolution Tracking. 
Undergraduate Capstone Project, College of Computing & Information Sciences, 
Surigao del Norte State University (SNSU), Surigao City.
```
