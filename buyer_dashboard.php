<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$properties = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $city = $_POST['city'];
        $property_type = $_POST['property_type'];
        $usage_type = $_POST['usage_type'];
        $min_price = $_POST['min_price'] ?: 0;
        $max_price = $_POST['max_price'] ?: 999999999;
        $min_size = $_POST['min_size'] ?: 0;
        $max_size = $_POST['max_size'] ?: 999999999;
        $negotiation = isset($_POST['negotiation']) ? 1 : null;
        $brokering = isset($_POST['brokering']) ? 1 : null;

        $sql = "SELECT * FROM properties 
                WHERE city = :city 
                AND property_type = :property_type 
                AND usage_type = :usage_type 
                AND total_value BETWEEN :min_price AND :max_price 
                AND property_size BETWEEN :min_size AND :max_size 
                AND status = 'available'";
        if ($negotiation !== null) $sql .= " AND negotiation = :negotiation";
        if ($brokering !== null) $sql .= " AND brokering = :brokering";

        $stmt = $pdo->prepare($sql);
        $params = [
            ':city' => $city,
            ':property_type' => $property_type,
            ':usage_type' => $usage_type,
            ':min_price' => $min_price,
            ':max_price' => $max_price,
            ':min_size' => $min_size,
            ':max_size' => $max_size
        ];
        if ($negotiation !== null) $params[':negotiation'] = $negotiation;
        if ($brokering !== null) $params[':brokering'] = $brokering;
        
        $stmt->execute($params);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error searching properties: " . $e->getMessage();
    }
}

// Fetch user's purchase history
$sql = "SELECT p.*, t.transaction_type, t.status AS transaction_status 
        FROM transactions t 
        JOIN properties p ON t.property_id = p.property_id 
        WHERE t.buyer_id = :buyer_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':buyer_id' => $_SESSION['user_id']]);
$purchase_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Buyer Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Buyer Dashboard</h1>
        <h2>Search Properties</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>City:</label>
            <input type="text" name="city" required>
            <label>Property Type:</label>
            <select name="property_type" required>
                <option value="plot">Plot</option>
                <option value="flat">Flat</option>
                <option value="villa">Villa</option>
                <option value="farm_land">Farm Land</option>
                <option value="farm_house">Farm House</option>
                <option value="barren_land">Barren Land</option>
            </select>
            <label>Usage Type:</label>
            <select name="usage_type" required>
                <option value="sale">Sale</option>
                <option value="rent">Rent</option>
                <option value="lease">Lease</option>
                <option value="development">Development</option>
            </select>
            <label>Price Range:</label>
            <input type="number" name="min_price" step="0.01" placeholder="Min Price">
            <input type="number" name="max_price" step="0.01" placeholder="Max Price">
            <label>Size Range (sqm):</label>
            <input type="number" name="min_size" step="0.01" placeholder="Min Size">
            <input type="number" name="max_size" step="0.01" placeholder="Max Size">
            <label>Negotiation Available:</label>
            <input type="checkbox" name="negotiation">
            <label>Brokering Available:</label>
            <input type="checkbox" name="brokering">
            <button type="submit">Search</button>
        </form>

        <h2>Available Properties</h2>
        <?php if (!empty($properties)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Location</th>
                    <th>City</th>
                    <th>Total Value</th>
                    <th>Negotiation</th>
                    <th>Brokering</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($properties as $property): ?>
                    <tr>
                        <td><?php echo $property['property_id']; ?></td>
                        <td><?php echo $property['property_type']; ?></td>
                        <td><?php echo $property['property_size'] . ' ' . $property['size_unit']; ?></td>
                        <td><?php echo $property['location']; ?></td>
                        <td><?php echo $property['city']; ?></td>
                        <td><?php echo $property['total_value']; ?></td>
                        <td><?php echo $property['negotiation'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $property['brokering'] ? 'Yes' : 'No'; ?></td>
                        <td><a href="buy_property.php?id=<?php echo $property['property_id']; ?>">Buy</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No properties found.</p>
        <?php endif; ?>

        <h2>Your Purchase History</h2>
        <?php if (!empty($purchase_history)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>City</th>
                    <th>Total Value</th>
                    <th>Transaction Type</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($purchase_history as $purchase): ?>
                    <tr>
                        <td><?php echo $purchase['property_id']; ?></td>
                        <td><?php echo $purchase['property_type']; ?></td>
                        <td><?php echo $purchase['city']; ?></td>
                        <td><?php echo $purchase['total_value']; ?></td>
                        <td><?php echo $purchase['transaction_type']; ?></td>
                        <td><?php echo $purchase['transaction_status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>You have not made any purchases yet.</p>
        <?php endif; ?>
        <a href="dashboard.php">Back to Main Dashboard</a>
    </div>
</body>
</html>