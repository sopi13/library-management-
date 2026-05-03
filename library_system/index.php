<?php
/**
 * Library Management System - Home Page
 * index.php
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library – Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">⬡</span>
                <span class="logo-text">Library</span>
            </a>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="books.php">Books</a></li>
                <li><a href="students.php">Students</a></li>
                <li><a href="issue_return.php">Issue / Return</a></li>
                <?php if (isset($_SESSION['admin'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-btn btn-outline">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-btn">Admin Login</a></li>
                <?php endif; ?>
            </ul>
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg">
            <div class="hero-grid"></div>
        </div>
        <div class="hero-content">
            <div class="hero-badge">📚 Smart Library Platform</div>
            <h1 class="hero-title">
                Manage Your<br>
                <span class="accent-text">Library</span><br>
                Effortlessly
            </h1>
            <p class="hero-desc">
                A modern, all-in-one system to manage books, students,
                and transactions — with real-time availability tracking.
            </p>
            <div class="hero-actions">
                <a href="books.php" class="btn btn-primary">Explore Books</a>
                <a href="login.php" class="btn btn-ghost">Admin Panel →</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="book-stack">
                <div class="book b1"><span>Computer</span></div>
                <div class="book b2"><span>Math</span></div>
                <div class="book b3"><span>History</span></div>
                <div class="book b4"><span>English</span></div>
                <div class="book b5"><span>Tamil</span></div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <?php
        require_once 'includes/db.php';
        $book_count = $conn->query("SELECT COUNT(*) FROM books")->fetch_row()[0] ?? 0;
        $student_count = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0;
        $issued_count = $conn->query("SELECT COUNT(*) FROM transactions WHERE return_date IS NULL")->fetch_row()[0] ?? 0;
        ?>
        <div class="stat-card">
            <div class="stat-number"><?= $book_count ?></div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $student_count ?></div>
            <div class="stat-label">Registered Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $issued_count ?></div>
            <div class="stat-label">Books Issued</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">24/7</div>
            <div class="stat-label">System Uptime</div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="section-header">
            <h2>Everything You Need</h2>
            <p>Built for librarians, designed for everyone.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📖</div>
                <h3>Book Catalog</h3>
                <p>Add, update, and search books with category and availability status in real time.</p>
                <a href="books.php" class="feature-link">Browse Books →</a>
            </div>
            <div class="feature-card featured">
                <div class="feature-icon">🎓</div>
                <h3>Student Records</h3>
                <p>Manage student registrations, departments, and borrowing history from one place.</p>
                <a href="students.php" class="feature-link">View Students →</a>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔄</div>
                <h3>Issue & Return</h3>
                <p>Seamlessly issue books and process returns with automatic database updates.</p>
                <a href="issue_return.php" class="feature-link">Manage Issues →</a>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Admin Dashboard</h3>
                <p>Secure login system with full control over books, students, and transactions.</p>
                <a href="login.php" class="feature-link">Admin Login →</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="logo-icon">⬡</span> <strong>Library</strong>
            </div>
            <p>Library Management System &copy; <?= date('2026') ?>. Built with PHP, MySQL & ❤️</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
