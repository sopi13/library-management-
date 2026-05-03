<?php
/**
 * LibraX - Book Management
 * books.php
 */
session_start();
require_once 'includes/db.php';

$msg = '';
$msg_type = 'success';
$edit_book = null;

// ----- Handle POST actions (Admin only) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'update') {
        $title    = trim($_POST['title'] ?? '');
        $author   = trim($_POST['author'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);

        if (!$title || !$author || !$category || $quantity < 1) {
            $msg = 'All fields are required and quantity must be at least 1.';
            $msg_type = 'danger';
        } elseif ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO books (title, author, category, quantity, available) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssii', $title, $author, $category, $quantity, $quantity);
            $stmt->execute();
            $msg = "Book \"$title\" added successfully!";
        } else {
            $book_id = (int)$_POST['book_id'];
            // Adjust available count proportionally
            $old = $conn->query("SELECT quantity, available FROM books WHERE id = $book_id")->fetch_assoc();
            $diff = $quantity - $old['quantity'];
            $new_avail = max(0, $old['available'] + $diff);
            $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, quantity=?, available=? WHERE id=?");
            $stmt->bind_param('sssiii', $title, $author, $category, $quantity, $new_avail, $book_id);
            $stmt->execute();
            $msg = "Book updated successfully!";
        }
    }

    if ($action === 'delete') {
        $book_id = (int)$_POST['book_id'];
        $conn->query("DELETE FROM books WHERE id = $book_id");
        $msg = 'Book deleted.';
        $msg_type = 'info';
    }
}

// Fetch book for edit
if (isset($_GET['edit']) && isset($_SESSION['admin'])) {
    $id = (int)$_GET['edit'];
    $edit_book = $conn->query("SELECT * FROM books WHERE id = $id")->fetch_assoc();
}

// Fetch all books
$books = $conn->query("SELECT * FROM books ORDER BY title ASC");

$page_title = 'Books';
$active_nav = 'books';
require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="page-header-inner">
        <div>
            <div class="page-title">📖 Book Catalog</div>
            <div class="page-subtitle">Browse and manage library books</div>
        </div>
        <?php if (isset($_SESSION['admin'])): ?>
        <a href="books.php" class="btn btn-primary btn-sm">+ Add New Book</a>
        <?php endif; ?>
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
            <div class="panel-title"><?= $edit_book ? '✏️ Edit Book' : '➕ Add New Book' ?></div>
        </div>
        <form method="POST" action="books.php" data-validate>
            <input type="hidden" name="action" value="<?= $edit_book ? 'update' : 'add' ?>">
            <?php if ($edit_book): ?>
                <input type="hidden" name="book_id" value="<?= $edit_book['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" class="form-control" required
                           placeholder="e.g. The Great Gatsby"
                           value="<?= htmlspecialchars($edit_book['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" class="form-control" required
                           placeholder="e.g. F. Scott Fitzgerald"
                           value="<?= htmlspecialchars($edit_book['author'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php
                        $cats = ['Fiction','Non-Fiction','Science','Technology','History','Biography','Arts','Philosophy','Mathematics','Other'];
                        foreach ($cats as $cat):
                            $sel = ($edit_book['category'] ?? '') === $cat ? 'selected' : '';
                        ?>
                        <option value="<?= $cat ?>" <?= $sel ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" class="form-control" min="1" required
                           placeholder="e.g. 5"
                           value="<?= htmlspecialchars($edit_book['quantity'] ?? '') ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $edit_book ? '💾 Update Book' : '➕ Add Book' ?>
                </button>
                <?php if ($edit_book): ?>
                    <a href="books.php" class="btn btn-ghost">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Books Table -->
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">All Books</div>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="🔍 Search books…">
            </div>
        </div>
        <div class="table-wrap">
            <?php if ($books->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Available</th>
                        <th>Status</th>
                        <?php if (isset($_SESSION['admin'])): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = $books->fetch_assoc()): ?>
                    <tr>
                        <td><?= $book['id'] ?></td>
                        <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars($book['category']) ?></span></td>
                        <td><?= $book['quantity'] ?></td>
                        <td><?= $book['available'] ?></td>
                        <td>
                            <?php if ($book['available'] > 0): ?>
                                <span class="badge badge-success">✓ Available</span>
                            <?php else: ?>
                                <span class="badge badge-danger">✗ Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($_SESSION['admin'])): ?>
                        <td>
                            <div class="td-actions">
                                <a href="books.php?edit=<?= $book['id'] ?>" class="btn btn-info btn-sm">Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm confirm-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div id="emptySearch" style="display:none;" class="empty-state" style="padding:30px;">
                <div class="empty-state-icon">🔍</div>
                <p>No books match your search.</p>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <h3>No Books Found</h3>
                    <p>Add your first book using the form above.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
