<?php
/**
 * Library - Admin Login
 * login.php
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Use prepared statement to prevent SQL injection
       $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");

if (!$stmt) {
    die("Prepare Error: " . $conn->error);
}

$stmt->bind_param('s', $username);  // ✅ now correct place
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
}
            // Verify password (uses password_hash in DB)
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username or password.';
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Library </title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">⬡</div>
            <h1>LibraX</h1>
            <p>Admin Panel Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" data-validate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="Enter admin username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:24px;justify-content:center;">
                Login to Dashboard
            </button>
        </form>

        <div style="margin-top:20px;text-align:center;">
            <a href="index.php" style="color:var(--text-muted);font-size:0.85rem;">← Back to Home</a>
        </div>

        <div class="alert alert-info" style="margin-top:20px;font-size:0.82rem;">
            <strong>Default credentials:</strong><br>
            Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code>
        </div>
    </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
