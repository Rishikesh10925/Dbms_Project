<?php
$host = 'localhost';
$db = 'real_estate_db';
$user = 'root'; // Replace with your MySQL username
$pass = '';     // Replace with your MySQL password
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>