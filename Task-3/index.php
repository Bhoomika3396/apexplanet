<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "test1"; // Make sure this DB exists and has `users` and `posts` tables

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB connection failed: " . $conn->connect_error);

$page = $_GET['page'] ?? '';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Register
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    echo "<p>✅ Registered successfully. Please log in.</p>";
}

// Login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    if (!$stmt) die("❌ SQL Error: " . $conn->error);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';
        $page = 'post';
    } else {
        echo "<p style='color:red;'>❌ Invalid login.</p>";
    }
}

// Post Submission
if (isset($_POST['submit_post']) && isLoggedIn()) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    $stmt->execute();
    echo "<p>✅ Post added.</p>";
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>📝 ApexPlanet Blog - Task 3</title>
</head>
<body>
<h2>📝 ApexPlanet Blog - Task 3</h2>

<?php if (!isLoggedIn()): ?>
    <?php if ($page === 'register'): ?>
        <h3>Register</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Already registered? <a href="index.php?page=login">Login here</a></p>

    <?php else: ?>
        <h3>Login</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit" name="login">Login</button>
        </form>
        <p>No account? <a href="index.php?page=register">Register here</a></p>
    <?php endif; ?>

<?php else: ?>
    <p>✅ Logged in as: <strong><?= $_SESSION['username'] ?></strong> |
        <a href="?logout=true">Logout</a></p>

    <?php if ($page === 'see_posts'): ?>
        <h3>All Posts</h3>
        <?php
        $res = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
        while ($row = $res->fetch_assoc()):
        ?>
            <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
                <h4><?= htmlspecialchars($row['title']) ?></h4>
                <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                <small>Posted on: <?= $row['created_at'] ?></small>
            </div>
        <?php endwhile; ?>
        <a href="index.php">⬅️ Back</a>

    <?php else: ?>
        <h3>Create New Post</h3>
        <form method="POST">
            <input type="text" name="title" placeholder="Post Title" required><br><br>
            <textarea name="content" placeholder="Post Content" rows="5" cols="30" required></textarea><br><br>
            <button type="submit" name="submit_post">Post</button>
        </form>

        <p><a href="index.php?page=see_posts">📄 See All Posts</a></p>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
