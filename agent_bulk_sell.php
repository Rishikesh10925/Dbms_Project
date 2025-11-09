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
    // Verify the property belongs to the agent and is available
    $sql = "SELECT * FROM properties WHERE property_id = :property_id AND user_id = :user_id AND status = 'available'";
    $stmt = execute_named_query($con, $sql, [':property_id' => $property_id, ':user_id' => $agent_id]);
    $property = null;
    if ($stmt) {
        $res = $stmt->get_result();
        $property = $res ? $res->fetch_assoc() : null;
    }

    if ($property) {
        $sql = "INSERT INTO transactions (property_id, agent_id, transaction_type, status) 
                VALUES (:property_id, :agent_id, :transaction_type, 'pending')";
        $stmt = execute_named_query($con, $sql, [':property_id' => $property_id, ':agent_id' => $agent_id, ':transaction_type' => $property['usage_type']]);

        $sql = "UPDATE properties SET status = 'pending' WHERE property_id = :property_id";
        $stmt = execute_named_query($con, $sql, [':property_id' => $property_id]);
    }
}
$success = "Bulk sell request submitted successfully!";
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Agent Bulk Sell</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Bulk Sell Complete</h1>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <p>Selected properties have been marked for bulk sale.</p>
        <a href="agent_dashboard.php?action=sell">Back to Agent Dashboard</a>
    </div>
</body>
</html>