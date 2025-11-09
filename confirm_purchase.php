<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['transaction_id'])) {
    header("Location: index.php");
    exit();
}

$transaction_id = $_GET['transaction_id'];
$seller_id = $_SESSION['user_id'];

// Verify the transaction belongs to the seller's property
$sql = "SELECT t.*, p.user_id AS seller_id 
        FROM transactions t 
        JOIN properties p ON t.property_id = p.property_id 
        WHERE t.transaction_id = :transaction_id AND t.status = 'pending'";
$stmt = $con->prepare($sql);
$stmt->execute([':transaction_id' => $transaction_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction || $transaction['seller_id'] != $seller_id) {
    die("Invalid transaction or unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Update transaction status to confirmed
        $sql = "UPDATE transactions SET status = 'confirmed' WHERE transaction_id = :transaction_id";
        $stmt = $con->prepare($sql);
        $stmt->execute([':transaction_id' => $transaction_id]);

        // Optionally update property status (e.g., to 'sold' for sales)
        $new_property_status = ($transaction['transaction_type'] == 'sale') ? 'sold' : 'pending';
        $sql = "UPDATE properties SET status = :status WHERE property_id = :property_id";
        $stmt = $con->prepare($sql);
        $stmt->execute([
            ':status' => $new_property_status,
            ':property_id' => $transaction['property_id']
        ]);

        $success = "Purchase confirmed successfully!";
    } catch (PDOException $e) {
        $error = "Error confirming purchase: " . $e->getMessage();
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
        <h1>Confirm Buyer Request</h1>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!isset($success)): ?>
            <p><strong>Property ID:</strong> <?php echo $transaction['property_id']; ?></p>
            <p><strong>Transaction Type:</strong> <?php echo $transaction['transaction_type']; ?></p>
            <p><strong>Buyer ID:</strong> <?php echo $transaction['buyer_id']; ?></p>
            <form method="POST">
                <button type="submit">Confirm Purchase</button>
            </form>
        <?php endif; ?>
        <a href="seller_dashboard.php">Back to Seller Dashboard</a>
    </div>
</body>
</html>