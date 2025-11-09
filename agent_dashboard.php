<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$properties = [];
$action = $_GET['action'] ?? 'buy';

// Handle bulk buying
if ($action == 'buy' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $city = $_POST['city'];
    $sql = "SELECT * FROM properties WHERE city = :city AND status = 'available'";
    $stmt = execute_named_query($con, $sql, [':city' => $city]);
    $properties = [];
    if ($stmt) {
        $res = $stmt->get_result();
        $properties = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        $error = "Error searching properties: " . mysqli_error($con);
    }
}

// Fetch agent's listed properties for bulk selling
$sql = "SELECT * FROM properties WHERE user_id = :user_id AND status = 'available'";
$stmt = execute_named_query($con, $sql, [':user_id' => $_SESSION['user_id']]);
$agent_properties = [];
if ($stmt) {
    $res = $stmt->get_result();
    $agent_properties = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
} else {
    $agent_properties = [];
}

// Fetch agent's transaction history
$sql = "SELECT p.*, t.transaction_type, t.status AS transaction_status 
        FROM transactions t 
        JOIN properties p ON t.property_id = p.property_id 
        WHERE t.agent_id = :agent_id";
$stmt = execute_named_query($con, $sql, [':agent_id' => $_SESSION['user_id']]);
$agent_history = [];
if ($stmt) {
    $res = $stmt->get_result();
    $agent_history = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
} else {
    $agent_history = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Agent Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Agent Dashboard</h1>
        <div class="role-buttons">
            <a href="agent_dashboard.php?action=buy"><button>Buy</button></a>
            <a href="agent_dashboard.php?action=sell"><button>Sell</button></a>
            <a href="agent_dashboard.php?action=development"><button>Development</button></a>
        </div>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <?php if ($action == 'buy'): ?>
            <h2>Bulk Buy Properties</h2>
            <form method="POST">
                <label>City:</label>
                <input type="text" name="city" required>
                <button type="submit">Search</button>
            </form>
            <?্র

            <?php if (!empty($properties)): ?>
                <form method="POST" action="agent_buy.php">
                    <table>
                        <tr>
                            <th>Select</th>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>City</th>
                            <th>Total Value</th>
                        </tr>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><input type="checkbox" name="property_ids[]" value="<?php echo $property['property_id']; ?>"></td>
                                <td><?php echo $property['property_id']; ?></td>
                                <td><?php echo $property['property_type']; ?></td>
                                <td><?php echo $property['property_size'] . ' ' . $property['size_unit']; ?></td>
                                <td><?php echo $property['city']; ?></td>
                                <td><?php echo $property['total_value']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <button type="submit">Buy Selected</button>
                </form>
            <?php endif; ?>

        <?php elseif ($action == 'sell'): ?>
            <h2>Bulk Sell Properties</h2>
            <?php if (!empty($agent_properties)): ?>
                <form method="POST" action="agent_bulk_sell.php">
                    <table>
                        <tr>
                            <th>Select</th>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>City</th>
                            <th>Total Value</th>
                        </tr>
                        <?php foreach ($agent_properties as $property): ?>
                            <tr>
                                <td><input type="checkbox" name="property_ids[]" value="<?php echo $property['property_id']; ?>"></td>
                                <td><?php echo $property['property_id']; ?></td>
                                <td><?php echo $property['property_type']; ?></td>
                                <td><?php echo $property['property_size'] . ' ' . $property['size_unit']; ?></td>
                                <td><?php echo $property['city']; ?></td>
                                <td><?php echo $property['total_value']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <button type="submit">Sell Selected as Bulk</button>
                </form>
            <?php else: ?>
                <p>You have no properties available to sell.</p>
                <a href="seller_dashboard.php">List Properties</a>
            <?php endif; ?>

        <?php elseif ($action == 'development'): ?>
            <h2>Development Properties</h2>
            <p>List properties for development (TBD).</p>
        <?php endif; ?>

        <h2>Your Agent Transactions</h2>
        <?php if (!empty($agent_history)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>City</th>
                    <th>Total Value</th>
                    <th>Transaction Type</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($agent_history as $transaction): ?>
                    <tr>
                        <td><?php echo $transaction['property_id']; ?></td>
                        <td><?php echo $transaction['property_type']; ?></td>
                        <td><?php echo $transaction['city']; ?></td>
                        <td><?php echo $transaction['total_value']; ?></td>
                        <td><?php echo $transaction['transaction_type']; ?></td>
                        <td><?php echo $transaction['transaction_status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>You have not conducted any agent transactions yet.</p>
        <?php endif; ?>
        <a href="dashboard.php">Back to Main Dashboard</a>
    </div>
</body>
</html>