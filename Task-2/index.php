<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "blog");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if (!empty($username) && !empty($_POST['password'])) {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            echo "✅ Registered successfully. Please log in.";
        } else {
            echo "❌ Registration failed: " . $conn->error;
        }
    } else {
        echo "❌ All fields are required.";
    }
}

// Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_pw);
            $stmt->fetch();
            if (password_verify($password, $hashed_pw)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                header("Location: index.php?page=home");
                exit;
            } else {
                echo "❌ Invalid credentials.";
            }
        } else {
            echo "❌ User not found.";
        }
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}

// Create Post
if (isset($_POST['submit_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $title, $content);
            $stmt->execute();
            echo "✅ Post created successfully.";
        } else {
            echo "❌ Prepare failed: " . $conn->error;
        }
    } else {
        echo "❌ Title and content are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ApexPlanet Task 2</title>
</head>
<body>
<?php
$page = $_GET['page'] ?? 'login';

if ($page === 'register'): ?>
    <h2>Register</h2>
    <form method="post">
        <input name="username" placeholder="Username"><br>
        <input name="password" type="password" placeholder="Password"><br>
        <button name="register">Register</button>
    </form>
    <p>Already have an account? <a href="index.php?page=login">Login</a></p>

<?php elseif ($page === 'login'): ?>
    <h2>Login</h2>
    <form method="post">
        <input name="username" placeholder="Username"><br>
        <input name="password" type="password" placeholder="Password"><br>
        <button name="login">Login</button>
    </form>
    <p>Don't have an account? <a href="index.php?page=register">Register</a></p>

<?php elseif ($page === 'home' && isset($_SESSION['user_id'])): ?>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <a href="index.php?logout=1">Logout</a>

    <h3>Create a New Post</h3>
    <form method="post">
        <input type="text" name="title" placeholder="Title"><br><br>
        <textarea name="content" placeholder="Content"></textarea><br><br>
        <button name="submit_post">Submit Post</button>
    </form>

    <h3>All Posts</h3>
    <?php
    $result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<h4>" . htmlspecialchars($row['title']) . "</h4>";
        echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p><hr>";
    }
    ?>

<?php else: ?>
    <p>You must <a href="index.php?page=login">log in</a> first.</p>
<?php endif; ?>
</body>
</html>
