<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Sorting setup
$allowedSorts = [
    'ItemID' => 'inventoryitem.ItemID',
    'SkuCode' => 'inventoryitem.SkuCode',
    'ItemName' => 'inventoryitem.ItemName',
    'CategoryName' => 'category.CategoryName',
    'UnitName' => 'unitofmeasure.UnitName',
    'ReorderThreshold' => 'inventoryitem.ReorderThreshold',
    'IsActive' => 'inventoryitem.IsActive',
    'LastUpdated' => 'inventoryitem.LastUpdated'
];

$sort = $_GET['sort'] ?? 'ItemID';
$dir = $_GET['dir'] ?? 'ASC';

if (!array_key_exists($sort, $allowedSorts)) {
    $sort = 'ItemID';
}

$dir = strtoupper($dir);
if ($dir !== 'ASC' && $dir !== 'DESC') {
    $dir = 'ASC';
}

$orderBy = $allowedSorts[$sort] . " " . $dir;

// Handle activate/deactivate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['action'])) {
    $itemID = intval($_POST['item_id']);
    $newStatus = ($_POST['action'] === 'activate') ? 1 : 0;

    $stmt = $conn->prepare("UPDATE inventoryitem SET IsActive=?, LastUpdated=NOW() WHERE ItemID=?");
    $stmt->bind_param("ii", $newStatus, $itemID);
    $stmt->execute();
    $stmt->close();

    header("Location: viewItems.php?sort=$sort&dir=$dir");
    exit();
}

// Sort link helper
function sortLink($column, $label, $currentSort, $currentDir) {
    $nextDir = 'ASC';

    if ($currentSort === $column && $currentDir === 'ASC') {
        $nextDir = 'DESC';
    }

    $arrow = '';
    if ($currentSort === $column) {
        $arrow = $currentDir === 'ASC' ? ' ▲' : ' ▼';
    }

    return "<a href='?sort=$column&dir=$nextDir' style='color:white; text-decoration:none;'>$label$arrow</a>";
}

// Query
$sql = "SELECT 
            inventoryitem.ItemID,
            inventoryitem.SkuCode,
            inventoryitem.ItemName,
            inventoryitem.Description,
            inventoryitem.ReorderThreshold,
            inventoryitem.LastUpdated,
            inventoryitem.IsActive,
            category.CategoryName,
            unitofmeasure.UnitName
        FROM inventoryitem
        INNER JOIN category ON inventoryitem.CategoryID = category.CategoryID
        INNER JOIN unitofmeasure ON inventoryitem.UnitID = unitofmeasure.UnitID
        ORDER BY $orderBy";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Inventory Items</title>

    <style>
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
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #0077cc;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        .active {
            color: green;
            font-weight: bold;
        }

        .inactive {
            color: red;
            font-weight: bold;
        }

        .btn-red {
            background: red;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
        }

        .btn-green {
            background: green;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
        }

        .note {
            font-size: 12px;
            color: #555;
            margin-top: 5px;
        }
    </style>
</head>

<body>

<div class="page-title">View Inventory Items</div>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="addItem.php">Add Item</a>
    <a href="assignItemLocation.php">Assign Item to Location</a>
    <a href="manageLocations.php">Manage Locations</a>
    <a href="recordTransaction.php">Record Transaction</a>
    <a href="logout.php">Log Out</a>
</nav>

<div class="container">

<p>
This page displays all inventory items currently stored in the system.
Inactive items are preserved for history but removed from workflows until reactivated.
</p>

<table>
<tr>
    <th><?= sortLink('ItemID', 'Item ID', $sort, $dir) ?></th>
    <th><?= sortLink('SkuCode', 'SKU Code', $sort, $dir) ?></th>
    <th><?= sortLink('ItemName', 'Item Name', $sort, $dir) ?></th>
    <th>Description</th>
    <th><?= sortLink('CategoryName', 'Category', $sort, $dir) ?></th>
    <th><?= sortLink('UnitName', 'Unit', $sort, $dir) ?></th>
    <th><?= sortLink('ReorderThreshold', 'Reorder Threshold', $sort, $dir) ?></th>
    <th><?= sortLink('IsActive', 'Status', $sort, $dir) ?></th>
    <th><?= sortLink('LastUpdated', 'Last Updated', $sort, $dir) ?></th>
    <th>Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row['ItemID'] ?></td>
    <td><?= $row['SkuCode'] ?></td>
    <td><?= $row['ItemName'] ?></td>
    <td><?= $row['Description'] ?></td>
    <td><?= $row['CategoryName'] ?></td>
    <td><?= $row['UnitName'] ?></td>
    <td><?= $row['ReorderThreshold'] ?></td>

    <td>
        <?php if ($row['IsActive']): ?>
            <span class="active">Active</span>
        <?php else: ?>
            <span class="inactive">Inactive</span>
        <?php endif; ?>
    </td>

    <td><?= $row['LastUpdated'] ?></td>

    <td>
        <?php if ($row['IsActive']): ?>
            <form method="post">
                <input type="hidden" name="item_id" value="<?= $row['ItemID'] ?>">
                <input type="hidden" name="action" value="deactivate">
                <button class="btn-red">Deactivate</button>
            </form>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="item_id" value="<?= $row['ItemID'] ?>">
                <input type="hidden" name="action" value="activate">
                <button class="btn-green">Reactivate</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>

<script>
document.querySelectorAll('th a').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        window.location.replace(this.href);
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>