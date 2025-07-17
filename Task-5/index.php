<?php
session_start();
$conn = new mysqli("localhost", "root", "", "test1");
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

// Auth Functions
function login($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashed, $role);
        $stmt->fetch();
        if (password_verify($password, $hashed)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            return true;
        }
    }
    return false;
}

function register($conn, $username, $password) {
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $stmt->bind_param("ss", $username, password_hash($password, PASSWORD_DEFAULT));
    return $stmt->execute();
}

function createPost($conn, $title, $content) {
    $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    return $stmt->execute();
}

function getPosts($conn) {
    return $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
}

// Actions
$msg = '';
$showPosts = isset($_GET['showPosts']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        if (register($conn, $_POST['username'], $_POST['password'])) {
            $msg = "✅ Registered. Please log in.";
        } else {
            $msg = "❌ Registration failed (maybe username taken).";
        }
    } elseif (isset($_POST['login'])) {
        if (login($conn, $_POST['username'], $_POST['password'])) {
            $msg = "✅ Logged in.";
        } else {
            $msg = "❌ Login failed.";
        }
    } elseif (isset($_POST['post']) && isset($_SESSION['user_id'])) {
        if (createPost($conn, $_POST['title'], $_POST['content'])) {
            $msg = "✅ Post created.";
        } else {
            $msg = "❌ Post creation failed.";
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🚀 ApexPlanet Final Project</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: auto; padding: 20px; }
        form { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        input, textarea, button { width: 100%; margin: 8px 0; padding: 8px; }
        .post { background: #f8f8f8; padding: 10px; border-left: 5px solid #007bff; margin-bottom: 10px; }
        .logout { float: right; }
    </style>
</head>
<body>

<h2>🚀 ApexPlanet Internship — Final Project (Task 5)</h2>
<?php if ($msg) echo "<p><strong>$msg</strong></p>"; ?>

<?php if (!isset($_SESSION['user_id'])): ?>

<!-- Register -->
<form method="POST">
    <h3>Register</h3>
    <input type="text" name="username" required placeholder="Username">
    <input type="password" name="password" required placeholder="Password">
    <button name="register">Register</button>
</form>

<!-- Login -->
<form method="POST">
    <h3>Login</h3>
    <input type="text" name="username" required placeholder="Username">
    <input type="password" name="password" required placeholder="Password">
    <button name="login">Login</button>
</form>

<?php else: ?>

<p>
    👋 Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
    <a class="logout" href="?logout=1">Logout</a>
</p>

<!-- Create Post -->
<form method="POST">
    <h3>Create a Post</h3>
    <input type="text" name="title" required placeholder="Post Title">
    <textarea name="content" required placeholder="Post Content"></textarea>
    <button name="post">Publish</button>
</form>

<!-- Show All Posts -->
<form method="GET">
    <input type="hidden" name="showPosts" value="1">
    <button type="submit">📄 Show All Posts</button>
</form>

<!-- Post List -->
<?php if ($showPosts): ?>
    <h3>📰 All Blog Posts</h3>
    <?php $posts = getPosts($conn); ?>
    <?php while ($row = $posts->fetch_assoc()): ?>
        <div class="post">
            <h4><?= htmlspecialchars($row['title']) ?></h4>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small>🕒 <?= $row['created_at'] ?></small>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php endif; ?>

</body>
</html>
