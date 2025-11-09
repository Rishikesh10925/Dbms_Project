<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['property_ids'])) {
    header("Location: index.php");
    exit();
}

$agent_id = $_SESSION['user_id'];
$property_ids = $_POST['property_ids'];

foreach ($property_ids as $property_id) {
    $sql = "INSERT INTO transactions (property_id, agent_id, transaction_type, status) 
            VALUES (:property_id, :agent_id, 'sale', 'pending')";
    $stmt = $con->prepare($sql);
    $stmt->execute([
        ':property_id' => $property_id,
        ':agent_id' => $agent_id
    ]);

    $sql = "UPDATE properties SET status = 'pending' WHERE property_id = :property_id";
    $stmt = $con->prepare($sql);
    $stmt->execute([':property_id' => $property_id]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Agent Buy</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Bulk Purchase Complete</h1>
        <p>Selected properties have been requested for purchase.</p>
        <a href="agent_dashboard.php?action=buy">Back to Agent Dashboard</a>
    </div>
</body>
</html>