<?php
/**
 * Library - Database Connection
 * includes/db.php
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP MySQL user
define('DB_PASS', '');           // Default XAMPP MySQL password (empty)
define('DB_NAME', 'library_system'); 

// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Show a friendly error page instead of a PHP error
    http_response_code(503);
    echo '<!DOCTYPE html><html><head>
        <title>Database Error – LibraX</title>
        <style>
            body{font-family:sans-serif;background:#0d0f14;color:#e8eaf0;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
            .box{text-align:center;padding:40px;background:#1e2330;border-radius:16px;border:1px solid #2a2f3f;max-width:480px;}
            h2{color:#f87171;margin-bottom:12px;} code{background:#13161e;padding:8px 12px;border-radius:6px;display:block;margin:12px 0;font-size:0.85rem;color:#e8c56b;}
        </style></head><body>
        <div class="box">
            <h2>⚠️ Database Connection Failed</h2>
            <p>Could not connect to MySQL. Please ensure:</p>
            <ul style="text-align:left;color:#8892a4;margin:16px 0;line-height:2">
                <li>XAMPP Apache &amp; MySQL are running</li>
                <li>Database <strong>librax_db</strong> exists</li>
                <li>Credentials in <code>includes/db.php</code> are correct</li>
            </ul>
            <code>' . htmlspecialchars($conn->connect_error) . '</code>
        </div></body></html>';
    exit;
}

// Set character set
$conn->set_charset('utf8mb4');
?>
