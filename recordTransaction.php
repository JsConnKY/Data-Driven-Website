<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pull only active item-location combinations
$itemSql = "SELECT 
                inventoryitemlocation.ItemLocationID,
                inventoryitem.ItemName,
                inventoryitem.SkuCode,
                inventoryitem.IsActive,
                location.LocationName,
                inventoryitemlocation.QuantityAtLocation
            FROM inventoryitemlocation
            INNER JOIN inventoryitem 
                ON inventoryitemlocation.ItemID = inventoryitem.ItemID
            INNER JOIN location 
                ON inventoryitemlocation.LocationID = location.LocationID
            WHERE inventoryitem.IsActive = 1
            ORDER BY inventoryitem.ItemName, location.LocationName";

$itemResult = $conn->query($itemSql);

if (!$itemResult) {
    die("Item-location query failed: " . $conn->error);
}

// Pull transaction types
$typeSql = "SELECT TransactionTypeID, TransactionTypeName
            FROM transactiontype
            ORDER BY TransactionTypeName";

$typeResult = $conn->query($typeSql);

if (!$typeResult) {
    die("Transaction type query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Transaction</title>
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
            width: 90%;
            max-width: 900px;
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
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
            padding: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        select,
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: #0077cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #005fa3;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            background-color: #e0ffe5;
            color: #006600;
        }

        .error {
            background-color: #ffe0e0;
            color: #990000;
        }

        .helper {
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
    </style>
</head>
<body>

<div class="header">
    <h1>Inventory Management System</h1>
    <p>Record Inventory Transaction</p>
</div>

<div class="top-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="assignItemLocation.php">Assign Item to Location</a>
    <a href="viewTransactions.php">View Transactions</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="container">
    <h2>Record Transaction</h2>
    <p class="helper">Use this form to record inventory coming in or going out of a location.</p>

    <?php if (isset($_GET['success'])): ?>
        <div class="message success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="message error">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <?php if ($itemResult->num_rows > 0): ?>
        <form action="saveTransaction.php" method="POST" id="transactionForm">

            <label for="item_location_id">Item and Location</label>
            <select id="item_location_id" name="item_location_id" required>
                <option value="">-- Select Item and Location --</option>
                <?php while ($row = $itemResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['ItemLocationID']; ?>">
                        <?php
                        echo htmlspecialchars(
                            $row['ItemName'] . ' (' . $row['SkuCode'] . ') - ' .
                            $row['LocationName'] . ' | Qty on hand: ' . $row['QuantityAtLocation']
                        );
                        ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="transaction_type_id">Transaction Type</label>
            <select id="transaction_type_id" name="transaction_type_id" required>
                <option value="">-- Select Transaction Type --</option>
                <?php while ($type = $typeResult->fetch_assoc()): ?>
                    <option value="<?php echo $type['TransactionTypeID']; ?>">
                        <?php echo htmlspecialchars($type['TransactionTypeName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="quantity_changed">Quantity</label>
            <input type="number" id="quantity_changed" name="quantity_changed" min="1" required>

            <label for="notes">Notes</label>
            <textarea id="notes" name="notes"></textarea>

            <button type="submit">Save Transaction</button>
        </form>
    <?php else: ?>
        <div class="empty-note">
            No active item-location assignments are available. Assign an active item to a location first.
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById("transactionForm")?.addEventListener("submit", function(event) {
    const itemLocation = document.getElementById("item_location_id").value;
    const transactionType = document.getElementById("transaction_type_id").value;
    const quantity = document.getElementById("quantity_changed").value;

    if (itemLocation === "" || transactionType === "" || quantity === "") {
        alert("Please complete all required fields.");
        event.preventDefault();
        return;
    }

    if (parseInt(quantity) < 1) {
        alert("Quantity must be at least 1.");
        event.preventDefault();
    }
});
</script>

</body>
</html>
<?php
$conn->close();
?>