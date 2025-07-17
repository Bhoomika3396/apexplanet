<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "test1");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Login
function login($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            return true;
        }
    }
    return false;
}

// Register
function register($conn, $username, $password) {
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ss", $username, $hashed);
    return $stmt->execute();
}

// Create Post
function createPost($conn, $title, $content) {
    $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    return $stmt->execute();
}

// Fetch Posts
function getAllPosts($conn) {
    return $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
}

// Handle Actions
$message = '';
$showPosts = isset($_GET['showPosts']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        if (register($conn, $_POST['username'], $_POST['password'])) {
            $message = "✅ Registered successfully. Please log in.";
        } else {
            $message = "❌ Registration failed.";
        }
    } elseif (isset($_POST['login'])) {
        if (login($conn, $_POST['username'], $_POST['password'])) {
            $message = "✅ Logged in.";
        } else {
            $message = "❌ Invalid credentials.";
        }
    } elseif (isset($_POST['post']) && isset($_SESSION['user_id'])) {
        if (createPost($conn, $_POST['title'], $_POST['content'])) {
            $message = "✅ Post added.";
        } else {
            $message = "❌ Post failed.";
        }
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🛡️ ApexPlanet Task 4</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: auto; padding: 20px; }
        input, textarea, button { width: 100%; margin: 8px 0; padding: 8px; }
        .post { background: #f1f1f1; margin-top: 10px; padding: 10px; border-left: 5px solid #007bff; }
        .logout { float: right; }
        form { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>

<h2>🛡️ ApexPlanet Task 4: Secure Blog</h2>
<?php if ($message) echo "<p><strong>$message</strong></p>"; ?>

<?php if (!isset($_SESSION['user_id'])): ?>

<!-- Register Form -->
<form method="POST">
    <h3>Register</h3>
    <input type="text" name="username" placeholder="Username" required pattern="[A-Za-z0-9]{3,}">
    <input type="password" name="password" placeholder="Password" required minlength="5">
    <button name="register">Register</button>
</form>

<!-- Login Form -->
<form method="POST">
    <h3>Login</h3>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button name="login">Login</button>
</form>

<?php else: ?>

<p>
    👋 Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
    <a class="logout" href="?logout=1">Logout</a>
</p>

<!-- Post Form -->
<form method="POST">
    <h3>Create a New Post</h3>
    <input type="text" name="title" placeholder="Post Title" required>
    <textarea name="content" placeholder="Post Content" required></textarea>
    <button name="post">Post</button>
</form>

<!-- Button to Show All Posts -->
<form method="GET">
    <input type="hidden" name="showPosts" value="1">
    <button type="submit">📄 See All Posts</button>
</form>

<!-- Posts Section -->
<?php if ($showPosts): ?>
    <h3>📰 All Posts</h3>
    <?php
    $posts = getAllPosts($conn);
    while ($row = $posts->fetch_assoc()):
    ?>
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
