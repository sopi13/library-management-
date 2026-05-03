<?php
/**
 * Library - Admin Dashboard
 * dashboard.php
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

// Fetch dashboard stats
$total_books     = $conn->query("SELECT SUM(quantity) FROM books")->fetch_row()[0] ?? 0;
$total_students  = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0;
$books_issued    = $conn->query("SELECT COUNT(*) FROM transactions WHERE return_date IS NULL")->fetch_row()[0] ?? 0;
$books_returned  = $conn->query("SELECT COUNT(*) FROM transactions WHERE return_date IS NOT NULL")->fetch_row()[0] ?? 0;

// Recent transactions
$recent = $conn->query("
    SELECT t.id, b.title, s.name AS student_name, t.issue_date, t.return_date
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN students s ON t.student_id = s.id
    ORDER BY t.id DESC LIMIT 10
");

$page_title = 'Dashboard';
$active_nav = 'dashboard';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-title">👉Dashboard</div>
            <div class="page-subtitle">Welcome back, <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong></div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="books.php" class="btn btn-primary btn-sm">+ Add Book</a>
            <a href="students.php" class="btn btn-info btn-sm">+ Add Student</a>
        </div>
    </div>
</div>

<div class="content-area">
    <!-- Stats -->
    <div class="dash-stats">
        <div class="dash-stat books">
            <div class="dash-stat-icon">📚</div>
            <div class="dash-stat-num"><?= number_format($total_books) ?></div>
            <div class="dash-stat-label">Total Book Copies</div>
        </div>
        <div class="dash-stat students">
            <div class="dash-stat-icon">🎓</div>
            <div class="dash-stat-num"><?= number_format($total_students) ?></div>
            <div class="dash-stat-label">Registered Students</div>
        </div>
        <div class="dash-stat issued">
            <div class="dash-stat-icon">📤</div>
            <div class="dash-stat-num"><?= number_format($books_issued) ?></div>
            <div class="dash-stat-label">Books Currently Issued</div>
        </div>
        <div class="dash-stat returned">
            <div class="dash-stat-icon">✅</div>
            <div class="dash-stat-num"><?= number_format($books_returned) ?></div>
            <div class="dash-stat-label">Books Returned</div>
        </div>
    </div>

    <!-- Quick Links -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
        <a href="books.php" class="panel" style="text-decoration:none;display:flex;align-items:center;gap:16px;padding:20px;">
            <span style="font-size:2rem;">📖</span>
            <div>
                <div style="font-weight:600;">Manage Books</div>
                <div style="font-size:0.82rem;color:var(--text-muted);">Add, edit, delete books</div>
            </div>
        </a>
        <a href="students.php" class="panel" style="text-decoration:none;display:flex;align-items:center;gap:16px;padding:20px;">
            <span style="font-size:2rem;">👨‍🎓</span>
            <div>
                <div style="font-weight:600;">Manage Students</div>
                <div style="font-size:0.82rem;color:var(--text-muted);">Add, edit, delete students</div>
            </div>
        </a>
        <a href="issue_return.php" class="panel" style="text-decoration:none;display:flex;align-items:center;gap:16px;padding:20px;">
            <span style="font-size:2rem;">🔄</span>
            <div>
                <div style="font-weight:600;">Issue / Return</div>
                <div style="font-size:0.82rem;color:var(--text-muted);">Issue or return books</div>
            </div>
        </a>
    </div>

    <!-- Recent Transactions -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Recent Transactions</div>
            <a href="issue_return.php" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <?php if ($recent->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Student</th>
                        <th>Issue Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= date('d M Y', strtotime($row['issue_date'])) ?></td>
                        <td>
                            <?php if ($row['return_date']): ?>
                                <span class="badge badge-success">Returned</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Issued</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3>No Transactions Yet</h3>
                    <p>Issue a book to a student to see transactions here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
