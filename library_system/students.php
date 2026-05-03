<?php
/**
 * Library - Student Management
 * students.php
 */
session_start();
require_once 'includes/db.php';

$msg = '';
$msg_type = 'success';
$edit_student = null;

// ----- Handle POST actions (Admin only) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $name       = trim($_POST['name'] ?? '');
        $student_id = trim($_POST['student_id'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');

        if (!$name || !$student_id || !$department) {
            $msg = 'Name, Student ID, and Department are required.';
            $msg_type = 'danger';
        } elseif ($action === 'add') {
            // Check duplicate student_id
            $check = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
            $check->bind_param('s', $student_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $msg = "Student ID \"$student_id\" already exists.";
                $msg_type = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO students (name, student_id, department, email, phone) VALUES (?,?,?,?,?)");
                $stmt->bind_param('sssss', $name, $student_id, $department, $email, $phone);
                $stmt->execute();
                $msg = "Student \"$name\" added successfully!";
            }
        } else {
            $sid = (int)$_POST['sid'];
            $stmt = $conn->prepare("UPDATE students SET name=?, student_id=?, department=?, email=?, phone=? WHERE id=?");
            $stmt->bind_param('sssssi', $name, $student_id, $department, $email, $phone, $sid);
            $stmt->execute();
            $msg = 'Student record updated successfully!';
        }
    }

    if ($action === 'delete') {
        $sid = (int)$_POST['sid'];
        // Check if student has active issues
        $active = $conn->query("SELECT COUNT(*) FROM transactions WHERE student_id = $sid AND return_date IS NULL")->fetch_row()[0];
        if ($active > 0) {
            $msg = 'Cannot delete student with active book issues. Return books first.';
            $msg_type = 'danger';
        } else {
            $conn->query("DELETE FROM students WHERE id = $sid");
            $msg = 'Student deleted successfully.';
            $msg_type = 'info';
        }
    }
}

// Fetch student for edit
if (isset($_GET['edit']) && isset($_SESSION['admin'])) {
    $id = (int)$_GET['edit'];
    $edit_student = $conn->query("SELECT * FROM students WHERE id = $id")->fetch_assoc();
}

// Fetch all students with book count
$students = $conn->query("
    SELECT s.*,
           COUNT(t.id) AS total_issued,
           SUM(CASE WHEN t.return_date IS NULL THEN 1 ELSE 0 END) AS active_issues
    FROM students s
    LEFT JOIN transactions t ON s.id = t.student_id
    GROUP BY s.id
    ORDER BY s.name ASC
");

$page_title = 'Students';
$active_nav = 'students';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-title">🎓 Student Management</div>
            <div class="page-subtitle">Manage registered library members</div>
        </div>
    </div>
</div>

<div class="content-area">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Add/Edit Form (admin only) -->
    <?php if (isset($_SESSION['admin'])): ?>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><?= $edit_student ? '✏️ Edit Student' : '➕ Add New Student' ?></div>
        </div>
        <form method="POST" action="students.php" data-validate>
            <input type="hidden" name="action" value="<?= $edit_student ? 'update' : 'add' ?>">
            <?php if ($edit_student): ?>
                <input type="hidden" name="sid" value="<?= $edit_student['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" class="form-control" required
                           placeholder="e.g. John Doe"
                           value="<?= htmlspecialchars($edit_student['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" class="form-control" required
                           placeholder="e.g. CS2024001"
                           value="<?= htmlspecialchars($edit_student['student_id'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department" class="form-control" required>
                        <option value="">Select Department</option>
                        <?php
                        $depts = ['Computer Science','Information Technology','Electronics','Mechanical','Civil','Electrical','Business Administration','Mathematics','Physics','Other'];
                        foreach ($depts as $dept):
                            $sel = ($edit_student['department'] ?? '') === $dept ? 'selected' : '';
                        ?>
                        <option value="<?= $dept ?>" <?= $sel ?>><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="student@example.com"
                           value="<?= htmlspecialchars($edit_student['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control"
                           placeholder="e.g. +1234567890"
                           value="<?= htmlspecialchars($edit_student['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $edit_student ? '💾 Update Student' : '➕ Add Student' ?>
                </button>
                <?php if ($edit_student): ?>
                    <a href="students.php" class="btn btn-ghost">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Students Table -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">All Students</div>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="🔍 Search students…">
            </div>
        </div>
        <div class="table-wrap">
            <?php if ($students->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Active Issues</th>
                        <th>Total Issued</th>
                        <?php if (isset($_SESSION['admin'])): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                        <td><code style="color:var(--accent);font-size:0.85rem;"><?= htmlspecialchars($s['student_id']) ?></code></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($s['department']) ?></span></td>
                        <td style="color:var(--text-muted);font-size:0.85rem;"><?= htmlspecialchars($s['email'] ?: '—') ?></td>
                        <td>
                            <?php if ($s['active_issues'] > 0): ?>
                                <span class="badge badge-warning"><?= $s['active_issues'] ?> book(s)</span>
                            <?php else: ?>
                                <span class="badge badge-success">None</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $s['total_issued'] ?></td>
                        <?php if (isset($_SESSION['admin'])): ?>
                        <td>
                            <div class="td-actions">
                                <a href="students.php?edit=<?= $s['id'] ?>" class="btn btn-info btn-sm">Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm confirm-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="emptySearch" style="display:none;" class="empty-state">
                <p>No students match your search.</p>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🎓</div>
                    <h3>No Students Found</h3>
                    <?php if (isset($_SESSION['admin'])): ?>
                    <p>Add your first student using the form above.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
