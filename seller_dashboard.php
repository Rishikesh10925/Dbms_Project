<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle property listing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_property_id'])) {
    try {
        $user_id = $_SESSION['user_id'];
        $property_type = $_POST['property_type'];
        $property_size = $_POST['property_size'];
        $size_unit = $_POST['size_unit'];
        $ownership_status = $_POST['ownership_status'];
        $features = $_POST['features'];
        $description = $_POST['description'];
        $estimated_price_per_unit = $_POST['estimated_price_per_unit'];
        $total_value = $_POST['total_value'];
        $future_value = $_POST['future_value'] ?: null;
        $seller_type = $_POST['seller_type'];
        $negotiation = isset($_POST['negotiation']) ? 1 : 0;
        $brokering = isset($_POST['brokering']) ? 1 : 0;
        $location = $_POST['location'];
        $city = $_POST['city'];
        $usage_type = $_POST['usage_type'];

        $sql = "INSERT INTO properties (user_id, property_type, property_size, size_unit, ownership_status, features, description, estimated_price_per_unit, total_value, future_value, seller_type, negotiation, brokering, location, city, usage_type) 
                VALUES (:user_id, :property_type, :property_size, :size_unit, :ownership_status, :features, :description, :estimated_price_per_unit, :total_value, :future_value, :seller_type, :negotiation, :brokering, :location, :city, :usage_type)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':property_type' => $property_type,
            ':property_size' => $property_size,
            ':size_unit' => $size_unit,
            ':ownership_status' => $ownership_status,
            ':features' => $features,
            ':description' => $description,
            ':estimated_price_per_unit' => $estimated_price_per_unit,
            ':total_value' => $total_value,
            ':future_value' => $future_value,
            ':seller_type' => $seller_type,
            ':negotiation' => $negotiation,
            ':brokering' => $brokering,
            ':location' => $location,
            ':city' => $city,
            ':usage_type' => $usage_type
        ]);
        $success = "Property listed successfully!";
    } catch (PDOException $e) {
        $error = "Error listing property: " . $e->getMessage();
    }
}

// Handle property deletion
if (isset($_GET['delete_id'])) {
    try {
        $property_id = $_GET['delete_id'];
        $sql = "DELETE FROM properties WHERE property_id = :property_id AND user_id = :user_id AND status = 'available'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':property_id' => $property_id, ':user_id' => $_SESSION['user_id']]);
        $success = "Property deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting property: " . $e->getMessage();
    }
}

// Fetch user's listed properties
$sql = "SELECT * FROM properties WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$listed_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending buyer requests
$sql = "SELECT t.transaction_id, t.property_id, t.buyer_id, t.transaction_type, p.property_type, p.property_size, p.size_unit, p.location, p.city, p.total_value, u.username AS buyer_username, u.contact_info AS buyer_contact 
        FROM transactions t 
        JOIN properties p ON t.property_id = p.property_id 
        JOIN users u ON t.buyer_id = u.user_id 
        WHERE p.user_id = :user_id AND t.status = 'pending'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Seller Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Seller Dashboard</h1>
        <h2>List a New Property</h2>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Property Type:</label>
            <select name="property_type" required>
                <option value="plot">Plot</option>
                <option value="flat">Flat</option>
                <option value="villa">Villa</option>
                <option value="farm_land">Farm Land</option>
                <option value="farm_house">Farm House</option>
                <option value="barren_land">Barren Land</option>
            </select>
            <label>Size:</label>
            <input type="number" name="property_size" step="0.01" required>
            <select name="size_unit">
                <option value="sqm">Square Meters</option>
                <option value="sqyd">Square Yards</option>
            </select>
            <label>Ownership Status:</label>
            <select name="ownership_status" required>
                <option value="sole">Sole Ownership</option>
                <option value="co_owned">Co-Owned</option>
                <option value="mortgaged">Under Mortgage</option>
                <option value="leased">Leased/Occupied</option>
                <option value="pending_transfer">Pending Transfer</option>
                <option value="disputed">Disputed</option>
            </select>
            <label>Features:</label>
            <textarea name="features"></textarea>
            <label>Description:</label>
            <textarea name="description"></textarea>
            <label>Price per Unit:</label>
            <input type="number" name="estimated_price_per_unit" step="0.01" required>
            <label>Total Value:</label>
            <input type="number" name="total_value" step="0.01" required>
            <label>Future Value (optional):</label>
            <input type="number" name="future_value" step="0.01">
            <label>Seller Type:</label>
            <select name="seller_type" required>
                <option value="owner">Owner</option>
                <option value="third_party">Third Party</option>
            </select>
            <label>Negotiation:</label>
            <input type="checkbox" name="negotiation">
            <label>Brokering:</label>
            <input type="checkbox" name="brokering">
            <label>Location:</label>
            <input type="text" name="location" required>
            <label>City:</label>
            <input type="text" name="city" required>
            <label>Usage Type:</label>
            <select name="usage_type" required>
                <option value="sale">Sale</option>
                <option value="rent">Rent</option>
                <option value="lease">Lease</option>
                <option value="development">Development</option>
            </select>
            <button type="submit">List Property</button>
        </form>

        <h2>Your Listed Properties</h2>
        <?php if (!empty($listed_properties)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Ownership Status</th>
                    <th>Location</th>
                    <th>City</th>
                    <th>Total Value</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($listed_properties as $property): ?>
                    <tr>
                        <td><?php echo $property['property_id']; ?></td>
                        <td><?php echo $property['property_type']; ?></td>
                        <td><?php echo $property['property_size'] . ' ' . $property['size_unit']; ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $property['ownership_status'])); ?></td>
                        <td><?php echo $property['location']; ?></td>
                        <td><?php echo $property['city']; ?></td>
                        <td><?php echo $property['total_value']; ?></td>
                        <td><?php echo $property['status']; ?></td>
                        <td>
                            <?php if ($property['status'] == 'available'): ?>
                                <a href="edit_property.php?id=<?php echo $property['property_id']; ?>">Edit</a> |
                                <a href="seller_dashboard.php?delete_id=<?php echo $property['property_id']; ?>" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>You have not listed any properties yet.</p>
        <?php endif; ?>

        <h2>Pending Buyer Requests</h2>
        <?php if (!empty($pending_requests)): ?>
            <table>
                <tr>
                    <th>Property ID</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Location</th>
                    <th>City</th>
                    <th>Total Value</th>
                    <th>Buyer Username</th>
                    <th>Buyer Contact</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($pending_requests as $request): ?>
                    <tr>
                        <td><?php echo $request['property_id']; ?></td>
                        <td><?php echo $request['property_type']; ?></td>
                        <td><?php echo $request['property_size'] . ' ' . $request['size_unit']; ?></td>
                        <td><?php echo $request['location']; ?></td>
                        <td><?php echo $request['city']; ?></td>
                        <td><?php echo $request['total_value']; ?></td>
                        <td><?php echo $request['buyer_username']; ?></td>
                        <td><?php echo $request['buyer_contact']; ?></td>
                        <td>
                            <a href="confirm_purchase.php?transaction_id=<?php echo $request['transaction_id']; ?>" onclick="return confirm('Confirm this purchase?');">Confirm</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No pending buyer requests.</p>
        <?php endif; ?>
        <a href="dashboard.php">Back to Main Dashboard</a>
    </div>
</body>
</html>