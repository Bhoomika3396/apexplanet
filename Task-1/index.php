<?php
/*
 
 * Setting Up the Development Environment
 * ✅ Installed XAMPP/WAMP/MAMP
 * ✅ Verified Apache & MySQL via http://localhost
 * ✅ Installed VS Code
 * ✅ Git Initialized & First Commit Made
 * ✅ Project Files: index.php
 * 
 * GitHub Repo: https://github.com/your-username/apexplanet-task1
 */

// Display basic info
echo "<h1>Welcome to ApexPlanet Internship</h1>";
echo "<h2>Task 1: Development Environment Setup</h2>";

// Show date/time
echo "<p>Server Date & Time: <strong>" . date("Y-m-d H:i:s") . "</strong></p>";

// Sample database connection (optional test)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test"; // You can create a sample DB called 'test'

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "<p style='color:red;'>❌ Database Connection Failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>✅ Successfully connected to MySQL database: <strong>$dbname</strong></p>";
}

$conn->close();
?>
