# ⬡ LibraX – Library Management System

A modern, full-stack Library Management System built with **PHP**, **MySQL**, **HTML/CSS**, and **JavaScript**.

---

## 📁 Folder Structure

```
librax/                         ← Project root (place in htdocs)
├── index.php                   ← Home page
├── login.php                   ← Admin login
├── logout.php                  ← Session destroy
├── dashboard.php               ← Admin dashboard
├── books.php                   ← Book management (CRUD)
├── students.php                ← Student management (CRUD)
├── issue_return.php            ← Issue & return system
│
├── includes/
│   ├── db.php                  ← MySQL database connection
│   ├── header.php              ← Shared navigation header
│   └── footer.php              ← Shared footer + scripts
│
├── assets/
│   ├── css/
│   │   └── style.css           ← Complete stylesheet
│   └── js/
│       └── main.js             ← JavaScript (search, validation, etc.)
│
└── database/
    └── librax_db.sql           ← Full DB schema + sample data
```

---

## 🚀 Setup Instructions (XAMPP)

### Step 1 – Install XAMPP
Download and install XAMPP from https://www.apachefriends.org  
Start both **Apache** and **MySQL** from the XAMPP Control Panel.

### Step 2 – Copy Project Files
Copy the entire `librax/` folder into your XAMPP `htdocs` directory:
```
Windows: C:\xampp\htdocs\librax\
macOS:   /Applications/XAMPP/htdocs/librax/
Linux:   /opt/lampp/htdocs/librax/
```

### Step 3 – Create the Database

**Option A – via phpMyAdmin (Recommended)**
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar
3. Create a database named `librax_db`
4. Select `librax_db`, then click the **"Import"** tab
5. Click **Choose File** → select `database/librax_db.sql`
6. Click **Go** to run the SQL

**Option B – via MySQL CLI**
```bash
mysql -u root -p < C:\xampp\htdocs\librax\database\librax_db.sql
```

### Step 4 – Configure Database Credentials
Open `includes/db.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password
define('DB_NAME', 'librax_db');
```

### Step 5 – Run the Project
Open your browser and visit:
```
http://localhost/librax/
```

---

## 🔐 Admin Login

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> ⚠️ Change these credentials in production by updating the `admins` table with a new bcrypt hash.

To generate a new password hash in PHP:
```php
echo password_hash('your_new_password', PASSWORD_BCRYPT);
```

---

## ✨ Features

| Feature              | Details                                              |
|----------------------|------------------------------------------------------|
| 🏠 Home Page          | Hero section, stats, feature cards                  |
| 📖 Book Management    | Add / Edit / Delete books, availability tracking     |
| 🎓 Student Management | Add / Edit / Delete students, department filter      |
| 🔄 Issue & Return     | Issue books, return with one click, history log      |
| 🛡️ Admin Dashboard    | Stats overview, quick links, recent transactions     |
| 🔍 Live Search        | Filter tables instantly without page reload          |
| ✅ Form Validation    | Client-side + server-side validation                 |
| 📱 Responsive Design  | Works on mobile, tablet, and desktop                 |
| 🌙 Dark Theme         | Elegant dark UI with gold accents                   |

---

## 🗄️ Database Tables

### `admins`
| Column    | Type         | Notes                    |
|-----------|--------------|--------------------------|
| id        | INT PK AUTO  |                          |
| username  | VARCHAR(50)  | Unique                   |
| password  | VARCHAR(255) | bcrypt hashed            |

### `books`
| Column    | Type         | Notes                    |
|-----------|--------------|--------------------------|
| id        | INT PK AUTO  |                          |
| title     | VARCHAR(200) |                          |
| author    | VARCHAR(150) |                          |
| category  | VARCHAR(80)  |                          |
| quantity  | INT          | Total copies             |
| available | INT          | Copies not issued        |

### `students`
| Column     | Type         | Notes                   |
|------------|--------------|-------------------------|
| id         | INT PK AUTO  |                         |
| name       | VARCHAR(120) |                         |
| student_id | VARCHAR(50)  | Unique roll number      |
| department | VARCHAR(100) |                         |
| email      | VARCHAR(150) | Optional                |
| phone      | VARCHAR(20)  | Optional                |

### `transactions`
| Column      | Type | Notes                           |
|-------------|------|---------------------------------|
| id          | INT PK AUTO |                          |
| book_id     | INT  | FK → books.id                  |
| student_id  | INT  | FK → students.id               |
| issue_date  | DATE |                                |
| return_date | DATE | NULL = book still issued        |

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3 (custom, no Bootstrap), Vanilla JS
- **Backend**: PHP 8+ (MySQLi, sessions, prepared statements)
- **Database**: MySQL 5.7+ / MariaDB 10+
- **Fonts**: Playfair Display + DM Sans (Google Fonts)
- **Server**: Apache via XAMPP

---

## 🔒 Security Features

- SQL injection prevention via **prepared statements**
- **password_verify()** for secure password checking
- **htmlspecialchars()** on all output to prevent XSS
- Session-based authentication with **session_start()**
- Admin-only pages redirect unauthenticated users

---

## 📞 Troubleshooting

| Problem | Solution |
|---|---|
| Blank page / 500 error | Enable PHP error display in `php.ini` |
| Database connection failed | Ensure MySQL is running; check `db.php` credentials |
| Page not found | Confirm files are in `htdocs/librax/` and Apache is running |
| Login fails | Re-import `librax_db.sql` to reset credentials |
| Fonts not loading | Check internet connection (Google Fonts CDN) |
