<?php
/**
 * LibraX - Shared Header Include
 * includes/header.php
 *
 * Usage: require_once 'includes/header.php';
 * Set $page_title and $active_nav before including.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = $page_title ?? 'LibraX';
$active_nav = $active_nav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> – Library</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <span class="logo-icon">
                <img scr="assets/book=book.png" width="35">
            </span>
            <span class="logo-text">📖Library</span>
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" <?= $active_nav==='home'?'class="active"':'' ?>>Home</a></li>
            <li><a href="books.php" <?= $active_nav==='books'?'class="active"':'' ?>>Books</a></li>
            <li><a href="students.php" <?= $active_nav==='students'?'class="active"':'' ?>>Students</a></li>
            <li><a href="issue_return.php" <?= $active_nav==='issue'?'class="active"':'' ?>>Issue / Return</a></li>
            <?php if (isset($_SESSION['admin'])): ?>
                <li><a href="dashboard.php" <?= $active_nav==='dashboard'?'class="active"':'' ?>>Dashboard</a></li>
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
