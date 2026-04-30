<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Load only active items
$itemSql = "SELECT ItemID, ItemName, SkuCode
            FROM inventoryitem
            WHERE IsActive = 1
            ORDER BY ItemName";
$itemResult = $conn->query($itemSql);

if (!$itemResult) {
    die("Item query failed: " . $conn->error);
}

// Load locations
$locationSql = "SELECT LocationID, LocationName
                FROM location
                ORDER BY LocationName";
$locationResult = $conn->query($locationSql);

if (!$locationResult) {
    die("Location query failed: " . $conn->error);
}

function renderAssignments(mysqli $conn): array {
    $currentSql = "SELECT 
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
                   ORDER BY location.LocationName, inventoryitem.ItemName";

    $currentResult = $conn->query($currentSql);

    if (!$currentResult) {
        die("Current assignment query failed: " . $conn->error);
    }

    $groupedAssignments = [];

    while ($assignment = $currentResult->fetch_assoc()) {
        $locationName = $assignment['LocationName'];

        if (!isset($groupedAssignments[$locationName])) {
            $groupedAssignments[$locationName] = [
                'items' => [],
                'totalQuantity' => 0
            ];
        }

        $groupedAssignments[$locationName]['items'][] = $assignment;
        $groupedAssignments[$locationName]['totalQuantity'] += (int)$assignment['QuantityAtLocation'];
    }

    return $groupedAssignments;
}

$groupedAssignments = renderAssignments($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Item to Location</title>
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
            max-width: 1300px;
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
            max-width: 1300px;
            margin: 30px auto;
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 20px;
            align-items: start;
        }

        .box {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
            padding: 20px;
        }

        h1, h2, h3 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        select,
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            box-sizing: border-box;
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
            margin-bottom: 15px;
        }

        .location-section {
            background-color: #fafafa;
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .location-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .location-total {
            color: #0077cc;
            font-weight: bold;
        }

        .empty-note {
            background-color: #fff4cc;
            border: 1px solid #e0c96d;
            border-radius: 6px;
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #0077cc;
            color: white;
            text-align: left;
            padding: 10px;
        }

        td {
            border-bottom: 1px solid #dddddd;
            padding: 10px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .remove-btn {
            background-color: #cc0000;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            width: auto;
            margin-top: 0;
        }

        .remove-btn:hover {
            background-color: #990000;
        }

        .inactive-label {
            color: #666666;
            font-style: italic;
        }

        @media (max-width: 950px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Inventory Management System</h1>
    <p>Assign Item to Location</p>
</div>

<div class="top-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="viewItems.php">View Inventory Items</a>
    <a href="manageLocations.php">Manage Locations</a>
    <a href="recordTransaction.php">Record Transaction</a>
    <a href="logout.php">Log Out</a>
</div>

<div class="container">
    <div class="box">
        <h2>Create Item Location Assignment</h2>
        <p class="helper">Select an active item, choose a location, and set the starting quantity for that location.</p>

        <div id="formMessage"></div>

        <form id="assignForm">
            <label for="item_id">Item</label>
            <select id="item_id" name="item_id" required>
                <option value="">-- Select Item --</option>
                <?php while ($item = $itemResult->fetch_assoc()): ?>
                    <option value="<?php echo $item['ItemID']; ?>">
                        <?php echo htmlspecialchars($item['ItemName'] . ' (' . $item['SkuCode'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="location_id">Location</label>
            <select id="location_id" name="location_id" required>
                <option value="">-- Select Location --</option>
                <?php while ($loc = $locationResult->fetch_assoc()): ?>
                    <option value="<?php echo $loc['LocationID']; ?>">
                        <?php echo htmlspecialchars($loc['LocationName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="quantity_at_location">Starting Quantity</label>
            <input type="number" id="quantity_at_location" name="quantity_at_location" min="0" required>

            <button type="submit">Assign Item</button>
        </form>
    </div>

    <div class="box">
        <h2>Inventory by Location</h2>
        <p class="helper">Items are grouped by location so users can quickly review what is stored in each area.</p>

        <div id="assignmentContainer">
            <?php if (!empty($groupedAssignments)): ?>
                <?php foreach ($groupedAssignments as $locationName => $locationData): ?>
                    <div class="location-section">
                        <div class="location-header">
                            <h3><?php echo htmlspecialchars($locationName); ?></h3>
                            <div class="location-total">
                                Total Quantity at Location: <?php echo htmlspecialchars($locationData['totalQuantity']); ?>
                            </div>
                        </div>

                        <table>
                            <tr>
                                <th>Item</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>

                            <?php foreach ($locationData['items'] as $assignment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['ItemName']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['SkuCode']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['QuantityAtLocation']); ?></td>
                                    <td>
                                        <?php if ((int)$assignment['IsActive'] === 1): ?>
                                            Active
                                        <?php else: ?>
                                            <span class="inactive-label">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="deleteItemLocation.php" method="POST"
                                              onsubmit="return confirm('Are you sure you want to remove this item from this location?');">
                                            <input type="hidden" name="item_location_id" value="<?php echo $assignment['ItemLocationID']; ?>">
                                            <button type="submit" class="remove-btn">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-note">
                    No item-location assignments exist yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showMessage(text, type) {
    const box = document.getElementById("formMessage");
    box.className = "message " + type;
    box.textContent = text;
}

function loadAssignments() {
    fetch("getAssignments.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("assignmentContainer").innerHTML = html;
        })
        .catch(() => {
            showMessage("Could not refresh assignment list.", "error");
        });
}

document.getElementById("assignForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const itemId = document.getElementById("item_id").value;
    const locationId = document.getElementById("location_id").value;
    const quantity = document.getElementById("quantity_at_location").value;

    if (itemId === "" || locationId === "" || quantity === "") {
        showMessage("Please complete all required fields.", "error");
        return;
    }

    if (parseInt(quantity) < 0) {
        showMessage("Starting quantity cannot be negative.", "error");
        return;
    }

    const formData = new FormData(this);

    fetch("saveItemLocation.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            showMessage(data.message, "success");
            document.getElementById("assignForm").reset();
            loadAssignments();
        } else {
            showMessage(data.message, "error");
        }
    })
    .catch(() => {
        showMessage("Something went wrong while assigning the item.", "error");
    });
});
</script>

</body>
</html>
<?php
$conn->close();
?>