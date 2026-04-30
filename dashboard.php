<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Get user info
$userSql = "
    SELECT staffuser.UserName, staffuser.Email, role.RoleName
    FROM staffuser
    INNER JOIN role ON staffuser.RoleID = role.RoleID
    WHERE staffuser.UserID = ?
";

$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

// Get selected shipping address from session
$shipAddress = null;

if (isset($_SESSION['selected_ship_address_id'])) {
    $selectedShipAddressID = intval($_SESSION['selected_ship_address_id']);

    $shipSql = "
        SELECT RecipientName, StreetAddress, City, State, ZipCode
        FROM shipaddress
        WHERE ShipAddressID = ? AND UserID = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($shipSql);
    $stmt->bind_param("ii", $selectedShipAddressID, $userID);
    $stmt->execute();
    $shipResult = $stmt->get_result();
    $shipAddress = $shipResult->fetch_assoc();
    $stmt->close();
}

// Get recent transactions
$transSql = "
    SELECT 
        transactiontype.TransactionTypeName,
        inventoryitem.ItemName,
        location.LocationName,
        inventorytransaction.QuantityChanged,
        inventorytransaction.TransactionDate
    FROM inventorytransaction
    INNER JOIN inventoryitemlocation 
        ON inventorytransaction.ItemLocationID = inventoryitemlocation.ItemLocationID
    INNER JOIN inventoryitem 
        ON inventoryitemlocation.ItemID = inventoryitem.ItemID
    INNER JOIN location 
        ON inventoryitemlocation.LocationID = location.LocationID
    INNER JOIN transactiontype 
        ON inventorytransaction.TransactionTypeID = transactiontype.TransactionTypeID
    WHERE inventorytransaction.UserID = ?
    ORDER BY inventorytransaction.TransactionDate DESC
    LIMIT 5
";

$stmt = $conn->prepare($transSql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$transactionResult = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Dashboard</title>

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

        .container {
            width: 85%;
            margin: 25px auto;
        }

        .card {
            background: white;
            padding: 24px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.12);
        }

        h2 {
            margin-top: 0;
        }

        h3 {
            margin-top: 0;
        }

        a {
            color: #0077cc;
            font-weight: bold;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .logout {
            color: red;
            font-weight: bold;
        }

        .transaction-type {
            color: #cc0000;
            font-weight: bold;
        }

        li {
            margin-bottom: 8px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 15px;
        }

        .nav-card {
            background: #f9fbfd;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px;
            box-shadow: 0 0 6px rgba(0,0,0,0.08);
        }

        .nav-card p {
            color: #555;
            margin-bottom: 12px;
        }

        .dash-button {
            display: block;
            background: #0077cc;
            color: white;
            text-align: center;
            padding: 11px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            margin-bottom: 8px;
        }

        .dash-button:hover {
            background: #005fa3;
            text-decoration: none;
        }

        .shipping-mini {
            margin-top: 12px;
            padding: 10px;
            background: #eef5fb;
            border-left: 4px solid #0077cc;
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 800px) {
            .card-grid {
                grid-template-columns: 1fr;
            }

            .container {
                width: 92%;
            }
        }
    </style>
</head>

<body>

<div class="page-title">Inventory Management Dashboard</div>

<div class="container">

    <div class="card">
        <h2>User Information</h2>

        <p><strong>Username:</strong> <?= htmlspecialchars($user['UserName']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['Email']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['RoleName']) ?></p>

        <p><a class="logout" href="logout.php">Log Out</a></p>
    </div>

    <div class="card">
        <h2>Recent Transactions</h2>

        <?php if ($transactionResult->num_rows > 0): ?>
            <ul>
                <?php while ($row = $transactionResult->fetch_assoc()): ?>
                    <li>
                        <span class="transaction-type">
                            <?= htmlspecialchars($row['TransactionTypeName']) ?>
                        </span>
                        -
                        <?= htmlspecialchars($row['ItemName']) ?>
                        at
                        <?= htmlspecialchars($row['LocationName']) ?>
                        |
                        Qty:
                        <?= htmlspecialchars($row['QuantityChanged']) ?>
                        |
                        <?= htmlspecialchars($row['TransactionDate']) ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No recent transactions found.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Project Navigation</h2>

        <div class="card-grid">

            <div class="nav-card">
                <h3>Inventory Setup</h3>
                <p>Create and manage inventory items and storage locations.</p>

                <a class="dash-button" href="addItem.php">Add Inventory Item</a>
                <a class="dash-button" href="viewItems.php">View or Remove Inventory Items</a>
                <a class="dash-button" href="manageLocations.php">Manage Locations</a>
            </div>

            <div class="nav-card">
                <h3>Assign Inventory</h3>
                <p>Place inventory items into specific storage locations.</p>

                <a class="dash-button" href="assignItemLocation.php">Assign Item to Location</a>
            </div>

            <div class="nav-card">
                <h3>Inventory Movement</h3>
                <p>Record inventory coming in or going out, then review the history.</p>

                <a class="dash-button" href="recordTransaction.php">Record Transaction</a>
                <a class="dash-button" href="viewTransactions.php">View Transactions</a>
            </div>

            <div class="nav-card">
                <h3>Shipping</h3>
                <p>Create, choose, and manage shipping addresses for an order.</p>

                <a class="dash-button" href="createShipAddress.php">Create Ship To Address</a>
                <a class="dash-button" href="selectShipAddress.php">Select Ship To Address</a>
                <a class="dash-button" href="manageShipAddresses.php">Manage Shipping Addresses</a>

                <?php if ($shipAddress): ?>
                    <div class="shipping-mini">
                        <strong>Current Shipping Address:</strong><br>
                        <?= htmlspecialchars($shipAddress['RecipientName']) ?><br>
                        <?= htmlspecialchars($shipAddress['StreetAddress']) ?><br>
                        <?= htmlspecialchars($shipAddress['City']) ?>,
                        <?= htmlspecialchars($shipAddress['State']) ?>
                        <?= htmlspecialchars($shipAddress['ZipCode']) ?>
                    </div>
                <?php else: ?>
                    <div class="shipping-mini">
                        <strong>No shipping address selected.</strong><br>
                        Use the shipping options above to create or select an address.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>