<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$message = "";

// Save selected shipping address to session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shipAddressID'])) {
    $_SESSION['selected_ship_address_id'] = intval($_POST['shipAddressID']);

    header("Location: dashboard.php");
    exit();
}

// Get saved addresses
$stmt = $conn->prepare("
    SELECT ShipAddressID, RecipientName, StreetAddress, City, State, ZipCode
    FROM shipaddress
    WHERE UserID = ?
    ORDER BY RecipientName ASC
");

$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$selectedID = $_SESSION['selected_ship_address_id'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Shipping Address</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial;
            background-color: #eef1f5;
            margin: 0;
        }

        .page-title {
            background-color: #0077cc;
            color: white;
            text-align: center;
            padding: 16px;
            font-size: 18px;
        }

        nav {
            width: 90%;
            margin: 20px auto;
        }

        nav a {
            color: #0077cc;
            font-weight: bold;
            text-decoration: none;
            margin-right: 20px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 25px auto;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.12);
        }

        h2 {
            margin-top: 0;
        }

        .note {
            color: #555;
            margin-bottom: 20px;
        }

        .address-option {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 15px;
            background-color: #fafafa;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .address-option:hover {
            background-color: #f0f7ff;
        }

        .address-text strong {
            display: block;
            margin-bottom: 4px;
        }

        button {
            width: 100%;
            background: #0077cc;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 15px;
        }

        button:hover {
            background: #005fa3;
        }

        .empty {
            color: #555;
            padding: 12px;
            background: #f7f7f7;
            border-left: 4px solid #0077cc;
        }
    </style>
</head>

<body>

<div class="page-title">Select Shipping Address</div>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="createShipAddress.php">Create Shipping Address</a>
    <a href="manageShipAddresses.php">Manage Shipping Addresses</a>
    <a href="logout.php">Log Out</a>
</nav>

<div class="container">
    <div class="card">
        <h2>Select Shipping Address for Current Order</h2>

        <p class="note">
            Choose the address that should be used as the current shipping address.
        </p>

        <?php if ($result->num_rows > 0): ?>
            <form method="post">

                <?php while ($row = $result->fetch_assoc()): ?>
                    <label class="address-option">
                        <input
                            type="radio"
                            name="shipAddressID"
                            value="<?= htmlspecialchars($row['ShipAddressID']) ?>"
                            <?= ($selectedID == $row['ShipAddressID']) ? 'checked' : '' ?>
                            required
                        >

                        <span class="address-text">
                            <strong><?= htmlspecialchars($row['RecipientName']) ?></strong>
                            <?= htmlspecialchars($row['StreetAddress']) ?><br>
                            <?= htmlspecialchars($row['City']) ?>,
                            <?= htmlspecialchars($row['State']) ?>
                            <?= htmlspecialchars($row['ZipCode']) ?>
                        </span>
                    </label>
                <?php endwhile; ?>

                <button type="submit">Use Selected Address</button>
            </form>
        <?php else: ?>
            <p class="empty">
                No shipping addresses found. Create a shipping address first.
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>