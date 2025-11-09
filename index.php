<?php
session_start();
require 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize error message
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Trim to remove whitespace
    $password = $_POST['password'];

    // Check if username exists
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = execute_named_query($con, $sql, [':username' => $username]);
    $user = null;
    if ($stmt) {
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
    } else {
        $error = "Database error: " . mysqli_error($con);
    }

    // Verify user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        if (empty($error)) $error = "Incorrect username or password";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Real Estate Management Application</h1>
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>