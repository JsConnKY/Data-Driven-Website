<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT 
            inventorytransaction.TransactionID,
            inventorytransaction.QuantityChanged,
            inventorytransaction.TransactionDate,
            inventorytransaction.Notes,
            inventoryitem.ItemName,
            inventoryitem.SkuCode,
            inventoryitem.IsActive,
            location.LocationName,
            staffuser.UserName,
            transactiontype.TransactionTypeName
        FROM inventorytransaction
        INNER JOIN inventoryitemlocation
            ON inventorytransaction.ItemLocationID = inventoryitemlocation.ItemLocationID
        INNER JOIN inventoryitem
            ON inventoryitemlocation.ItemID = inventoryitem.ItemID
        INNER JOIN location
            ON inventoryitemlocation.LocationID = location.LocationID
        INNER JOIN staffuser
            ON inventorytransaction.UserID = staffuser.UserID
        INNER JOIN transactiontype
            ON inventorytransaction.TransactionTypeID = transactiontype.TransactionTypeID
        ORDER BY inventorytransaction.TransactionDate DESC, inventorytransaction.TransactionID DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #0077cc;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .top-links {
            width: 95%;
            max-width: 1250px;
            margin: 20px auto 0 auto;
        }

        .top-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #0077cc;
            font-weight: bold;
        }

        .top-links a:hover {
            text-decoration: underline;
        }

        .container {
            width: 95%;
            max-width: 1250px;
            margin: 20px auto 30px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
            overflow-x: auto;
        }

        h1, h2 {
            margin-top: 0;
        }

        .small-text {
            color: #666666;
            font-size: 14px;
        }

        .empty-note {
            background-color: #fff4cc;
            border: 1px solid #e0c96d;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background-color: #0077cc;
            color: white;
            text-align: left;
            padding: 12px;
        }

        td {
            border-bottom: 1px solid #dddddd;
            padding: 12px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .type-in {
            color: #006600;
            font-weight: bold;
        }

        .type-out {
            color: #990000;
            font-weight: bold;
        }

        .status-active {
            color: #006600;
            font-weight: bold;
        }

        .status-inactive {
            color: #666666;
            font-weight: bold;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Inventory Management System</h1>
    <p>View Transactions</p>
</div>

<div class="top-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="recordTransaction.php">Record Transaction</a>
    <a href="assignItemLocation.php">Assign Item to Location</a>
    <a href="viewItems.php">View Inventory Items</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="container">
    <h2>Transaction History</h2>
    <p class="small-text">This page displays inventory movement records by item, location, type, quantity, user, and date. Historical records are preserved even if an item becomes inactive.</p>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Item</th>
                <th>SKU</th>
                <th>Item Status</th>
                <th>Location</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Recorded By</th>
                <th>Date</th>
                <th>Notes</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $typeName = strtoupper($row['TransactionTypeName']);
                $typeClass = ($typeName === 'IN') ? 'type-in' : 'type-out';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['TransactionID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ItemName']); ?></td>
                    <td><?php echo htmlspecialchars($row['SkuCode']); ?></td>
                    <td>
                        <?php if ((int)$row['IsActive'] === 1): ?>
                            <span class="status-active">Active</span>
                        <?php else: ?>
                            <span class="status-inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['LocationName']); ?></td>
                    <td class="<?php echo $typeClass; ?>">
                        <?php echo htmlspecialchars($row['TransactionTypeName']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['QuantityChanged']); ?></td>
                    <td><?php echo htmlspecialchars($row['UserName']); ?></td>
                    <td><?php echo htmlspecialchars($row['TransactionDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['Notes'] ?? ''); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="empty-note">
            No transactions have been recorded yet.
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php
$conn->close();
?>