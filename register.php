<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contact_info = $_POST['contact_info'];

    $sql = "INSERT INTO users (username, password, email, contact_info) 
            VALUES (:username, :password, :email, :contact_info)";

    $params = [
        ':username' => $username,
        ':password' => $password,
        ':email' => $email,
        ':contact_info' => $contact_info
    ];

    $stmt = execute_named_query($con, $sql, $params);
    if ($stmt) {
        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <label>Contact Info:</label>
            <input type="text" name="contact_info">
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="index.php">Login here</a></p>
    </div>
</body>
</html>