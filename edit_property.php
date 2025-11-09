<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$property_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM properties WHERE property_id = :property_id AND user_id = :user_id AND status = 'available'";
$stmt = $pdo->prepare($sql);
$stmt->execute([':property_id' => $property_id, ':user_id' => $user_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Property not found or not editable.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $property_type = $_POST['property_type'];
        $property_size = $_POST['property_size'];
        $size_unit = $_POST['size_unit'];
        $ownership_status = $_POST['ownership_status']; // Now an ENUM value
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

        $sql = "UPDATE properties SET 
                property_type = :property_type, property_size = :property_size, size_unit = :size_unit, 
                ownership_status = :ownership_status, features = :features, description = :description, 
                estimated_price_per_unit = :estimated_price_per_unit, total_value = :total_value, 
                future_value = :future_value, seller_type = :seller_type, negotiation = :negotiation, 
                brokering = :brokering, location = :location, city = :city, usage_type = :usage_type 
                WHERE property_id = :property_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
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
            ':usage_type' => $usage_type,
            ':property_id' => $property_id,
            ':user_id' => $user_id
        ]);
        $success = "Property updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating property: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Edit Property</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Property</h1>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Property Type:</label>
            <select name="property_type" required>
                <option value="plot" <?php if ($property['property_type'] == 'plot') echo 'selected'; ?>>Plot</option>
                <option value="flat" <?php if ($property['property_type'] == 'flat') echo 'selected'; ?>>Flat</option>
                <option value="villa" <?php if ($property['property_type'] == 'villa') echo 'selected'; ?>>Villa</option>
                <option value="farm_land" <?php if ($property['property_type'] == 'farm_land') echo 'selected'; ?>>Farm Land</option>
                <option value="farm_house" <?php if ($property['property_type'] == 'farm_house') echo 'selected'; ?>>Farm House</option>
                <option value="barren_land" <?php if ($property['property_type'] == 'barren_land') echo 'selected'; ?>>Barren Land</option>
            </select>
            <label>Size:</label>
            <input type="number" name="property_size" step="0.01" value="<?php echo $property['property_size']; ?>" required>
            <select name="size_unit">
                <option value="sqm" <?php if ($property['size_unit'] == 'sqm') echo 'selected'; ?>>Square Meters</option>
                <option value="sqyd" <?php if ($property['size_unit'] == 'sqyd') echo 'selected'; ?>>Square Yards</option>
            </select>
            <label>Ownership Status:</label>
            <select name="ownership_status" required>
                <option value="sole" <?php if ($property['ownership_status'] == 'sole') echo 'selected'; ?>>Sole Ownership</option>
                <option value="co_owned" <?php if ($property['ownership_status'] == 'co_owned') echo 'selected'; ?>>Co-Owned</option>
                <option value="mortgaged" <?php if ($property['ownership_status'] == 'mortgaged') echo 'selected'; ?>>Under Mortgage</option>
                <option value="leased" <?php if ($property['ownership_status'] == 'leased') echo 'selected'; ?>>Leased/Occupied</option>
                <option value="pending_transfer" <?php if ($property['ownership_status'] == 'pending_transfer') echo 'selected'; ?>>Pending Transfer</option>
                <option value="disputed" <?php if ($property['ownership_status'] == 'disputed') echo 'selected'; ?>>Disputed</option>
            </select>
            <label>Features:</label>
            <textarea name="features"><?php echo $property['features']; ?></textarea>
            <label>Description:</label>
            <textarea name="description"><?php echo $property['description']; ?></textarea>
            <label>Price per Unit:</label>
            <input type="number" name="estimated_price_per_unit" step="0.01" value="<?php echo $property['estimated_price_per_unit']; ?>" required>
            <label>Total Value:</label>
            <input type="number" name="total_value" step="0.01" value="<?php echo $property['total_value']; ?>" required>
            <label>Future Value (optional):</label>
            <input type="number" name="future_value" step="0.01" value="<?php echo $property['future_value']; ?>">
            <label>Seller Type:</label>
            <select name="seller_type" required>
                <option value="owner" <?php if ($property['seller_type'] == 'owner') echo 'selected'; ?>>Owner</option>
                <option value="third_party" <?php if ($property['seller_type'] == 'third_party') echo 'selected'; ?>>Third Party</option>
            </select>
            <label>Negotiation:</label>
            <input type="checkbox" name="negotiation" <?php if ($property['negotiation']) echo 'checked'; ?>>
            <label>Brokering:</label>
            <input type="checkbox" name="brokering" <?php if ($property['brokering']) echo 'checked'; ?>>
            <label>Location:</label>
            <input type="text" name="location" value="<?php echo $property['location']; ?>" required>
            <label>City:</label>
            <input type="text" name="city" value="<?php echo $property['city']; ?>" required>
            <label>Usage Type:</label>
            <select name="usage_type" required>
                <option value="sale" <?php if ($property['usage_type'] == 'sale') echo 'selected'; ?>>Sale</option>
                <option value="rent" <?php if ($property['usage_type'] == 'rent') echo 'selected'; ?>>Rent</option>
                <option value="lease" <?php if ($property['usage_type'] == 'lease') echo 'selected'; ?>>Lease</option>
                <option value="development" <?php if ($property['usage_type'] == 'development') echo 'selected'; ?>>Development</option>
            </select>
            <button type="submit">Update Property</button>
        </form>
        <a href="seller_dashboard.php">Back to Seller Dashboard</a>
    </div>
</body>
</html>