<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REMA - Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
        <h2>Choose Your Role:</h2>
        <div class="role-buttons">
            <a href="seller_dashboard.php"><button>Sell</button></a>
            <a href="buyer_dashboard.php"><button>Buy</button></a>
            <a href="agent_dashboard.php"><button>Agent</button></a>
        </div>
        <a href="logout.php"><button class="logout">Logout</button></a>
    </div>
</body>
</html>