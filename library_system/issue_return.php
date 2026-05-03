<?php
/**
 * Library - Issue & Return System
 * issue_return.php
 */
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php?redirect=issue_return.php');
    exit;
}

require_once 'includes/db.php';

$msg = '';
$msg_type = 'success';

// ----- Issue Book -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'issue') {
    $book_id    = (int)$_POST['book_id'];
    $student_id = (int)$_POST['student_id'];
    $issue_date = $_POST['issue_date'] ?? date('Y-m-d');

    // Validate
    $book = $conn->query("SELECT * FROM books WHERE id = $book_id")->fetch_assoc();
    $student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();

    if (!$book || !$student) {
        $msg = 'Invalid book or student selected.';
        $msg_type = 'danger';
    } elseif ($book['available'] < 1) {
        $msg = "\"" . htmlspecialchars($book['title']) . "\" is not available.";
        $msg_type = 'danger';
    } else {
        // Check if student already has this book
        $dup = $conn->query("SELECT id FROM transactions WHERE book_id=$book_id AND student_id=$student_id AND return_date IS NULL");
        if ($dup->num_rows > 0) {
            $msg = 'This student already has this book issued.';
            $msg_type = 'danger';
        } else {
            // Insert transaction
            $stmt = $conn->prepare("INSERT INTO transactions (book_id, student_id, issue_date) VALUES (?,?,?)");
            $stmt->bind_param('iis', $book_id, $student_id, $issue_date);
            $stmt->execute();
            // Decrement available
            $conn->query("UPDATE books SET available = available - 1 WHERE id = $book_id");
            $msg = "Book \"{$book['title']}\" issued to {$student['name']} successfully!";
        }
    }
}

// ----- Return Book -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    $trans_id   = (int)$_POST['trans_id'];
    $return_date = date('Y-m-d');

    $trans = $conn->query("SELECT * FROM transactions WHERE id = $trans_id AND return_date IS NULL")->fetch_assoc();
    if (!$trans) {
        $msg = 'Transaction not found or book already returned.';
        $msg_type = 'danger';
    } else {
        $conn->query("UPDATE transactions SET return_date = '$return_date' WHERE id = $trans_id");
        $conn->query("UPDATE books SET available = available + 1 WHERE id = {$trans['book_id']}");
        $msg = 'Book returned successfully!';
    }
}

// Fetch data for dropdowns
$books    = $conn->query("SELECT id, title, author, available FROM books ORDER BY title ASC");
$students = $conn->query("SELECT id, name, student_id FROM students ORDER BY name ASC");

// Fetch active issues
$active_issues = $conn->query("
    SELECT t.id, b.title, b.author, s.name AS student_name, s.student_id,
           t.issue_date,
           DATEDIFF(CURDATE(), t.issue_date) AS days_out
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN students s ON t.student_id = s.id
    WHERE t.return_date IS NULL
    ORDER BY t.issue_date ASC
");

// Fetch return history
$history = $conn->query("
    SELECT t.id, b.title, s.name AS student_name, t.issue_date, t.return_date,
           DATEDIFF(t.return_date, t.issue_date) AS days_held
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN students s ON t.student_id = s.id
    WHERE t.return_date IS NOT NULL
    ORDER BY t.return_date DESC
    LIMIT 20
");

$page_title = 'Issue / Return';
$active_nav = 'issue';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-title">🔄 Issue & Return</div>
            <div class="page-subtitle">Issue books to students and process returns</div>
        </div>
    </div>
</div>

<div class="content-area">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

        <!-- Issue Book Form -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📤 Issue a Book</div>
            </div>
            <form method="POST" action="issue_return.php" data-validate>
                <input type="hidden" name="action" value="issue">
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Select Book *</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">-- Choose a Book --</option>
                        <?php
                        $books->data_seek(0);
                        while ($b = $books->fetch_assoc()):
                            $avail = $b['available'] > 0 ? "({$b['available']} available)" : "(Out of Stock)";
                            $disabled = $b['available'] < 1 ? 'disabled' : '';
                        ?>
                        <option value="<?= $b['id'] ?>" <?= $disabled ?>>
                            <?= htmlspecialchars($b['title']) ?> – <?= htmlspecialchars($b['author']) ?> <?= $avail ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label>Select Student *</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">-- Choose a Student --</option>
                        <?php
                        $students->data_seek(0);
                        while ($s = $students->fetch_assoc()):
                        ?>
                        <option value="<?= $s['id'] ?>">
                            <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['student_id']) ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label>Issue Date *</label>
                    <input type="date" name="issue_date" class="form-control"
                           value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    📤 Issue Book
                </button>
            </form>
        </div>

        <!-- Active Issues & Return -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📋 Currently Issued (<?= $active_issues->num_rows ?>)</div>
            </div>
            <div class="table-wrap" style="max-height:340px;overflow-y:auto;">
                <?php if ($active_issues->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Student</th>
                            <th>Days Out</th>
                            <th>Return</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $active_issues->fetch_assoc()): ?>
                        <tr>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($row['title']) ?></td>
                            <td style="font-size:0.82rem;">
                                <?= htmlspecialchars($row['student_name']) ?><br>
                                <span style="color:var(--text-muted)"><?= htmlspecialchars($row['student_id']) ?></span>
                            </td>
                            <td>
                                <?php $d = (int)$row['days_out']; ?>
                                <span class="badge <?= $d > 14 ? 'badge-danger' : ($d > 7 ? 'badge-warning' : 'badge-success') ?>">
                                    <?= $d ?>d
                                </span>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="return">
                                    <input type="hidden" name="trans_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return confirm('Mark this book as returned?')">
                                        ✓ Return
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state" style="padding:30px 0;">
                        <div class="empty-state-icon">✅</div>
                        <p>No books currently issued.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Return History -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📜 Return History (Last 20)</div>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="🔍 Search history…">
            </div>
        </div>
        <div class="table-wrap">
            <?php if ($history->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Student</th>
                        <th>Issued On</th>
                        <th>Returned On</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($h = $history->fetch_assoc()): ?>
                    <tr>
                        <td><?= $h['id'] ?></td>
                        <td><?= htmlspecialchars($h['title']) ?></td>
                        <td><?= htmlspecialchars($h['student_name']) ?></td>
                        <td><?= date('d M Y', strtotime($h['issue_date'])) ?></td>
                        <td><?= date('d M Y', strtotime($h['return_date'])) ?></td>
                        <td><span class="badge badge-info"><?= $h['days_held'] ?> day(s)</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="emptySearch" style="display:none;" class="empty-state">
                <p>No records match your search.</p>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📜</div>
                    <h3>No Return History</h3>
                    <p>Returned books will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
