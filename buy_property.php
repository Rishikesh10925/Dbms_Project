<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$property_id = $_GET['id'];
$buyer_id = $_SESSION['user_id'];

$sql = "SELECT * FROM properties WHERE property_id = :property_id AND status = 'available'";
$stmt = execute_named_query($con, $sql, [':property_id' => $property_id]);
$property = null;
if ($stmt) {
    $res = $stmt->get_result();
    $property = $res ? $res->fetch_assoc() : null;
}

if (!$property) {
    die("Property not found or already sold.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert transaction with pending status
    $sql = "INSERT INTO transactions (property_id, buyer_id, transaction_type, status) 
            VALUES (:property_id, :buyer_id, :transaction_type, 'pending')";
    $stmt = execute_named_query($con, $sql, [':property_id' => $property_id, ':buyer_id' => $buyer_id, ':transaction_type' => $property['usage_type']]);

    // Update property status to pending
    $sql = "UPDATE properties SET status = 'pending' WHERE property_id = :property_id";
    $stmt = execute_named_query($con, $sql, [':property_id' => $property_id]);

    if ($stmt) {
        $success = "Purchase request submitted! Waiting for seller confirmation.";
    } else {
        $error = "Error submitting request: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Confirm Purchase</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Confirm Purchase</h1>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!isset($success)): ?>
            <p><strong>Type:</strong> <?php echo $property['property_type']; ?></p>
            <p><strong>Size:</strong> <?php echo $property['property_size'] . ' ' . $property['size_unit']; ?></p>
            <p><strong>Location:</strong> <?php echo $property['location']; ?></p>
            <p><strong>City:</strong> <?php echo $property['city']; ?></p>
            <p><strong>Total Value:</strong> <?php echo $property['total_value']; ?></p>
            <form method="POST">
                <button type="submit">Request Purchase</button>
            </form>
        <?php endif; ?>
        <a href="buyer_dashboard.php">Back to Buyer Dashboard</a>
    </div>
</body>
</html>