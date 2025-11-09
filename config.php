<?php
// Read database configuration from environment variables (works on Render)
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'real_estate_db';
$user = getenv('DB_USER') ?: 'root'; // Replace with your MySQL username
$pass = getenv('DB_PASS') ?: '';     // Replace with your MySQL password
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In production, avoid exposing raw error details. Render will show logs.
    die("Connection failed: " . $e->getMessage());
}
?>