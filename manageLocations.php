<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle add location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['locationName'])) {
    $locationName = trim($_POST['locationName']);

    if (!empty($locationName)) {
        $stmt = $conn->prepare("INSERT INTO location (LocationName) VALUES (?)");
        $stmt->bind_param("s", $locationName);
        $stmt->execute();
        $stmt->close();

        header("Location: manageLocations.php");
        exit();
    }
}

// Handle delete (optional but looks good in demo)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM location WHERE LocationID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: manageLocations.php");
    exit();
}

// Get locations
$result = $conn->query("SELECT * FROM location ORDER BY LocationName ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Locations</title>

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

        .layout {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 25px;
            width: 90%;
            margin: 20px auto;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #0077cc;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background: #f7f7f7;
        }

        .delete-btn {
            background: red;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: darkred;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            background: #0077cc;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background: #005fa3;
        }
    </style>
</head>

<body>

<div class="page-title">Manage Locations</div>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="viewItems.php">View Inventory Items</a>
    <a href="assignItemLocation.php">Assign Item to Location</a>
    <a href="recordTransaction.php">Record Transaction</a>
    <a href="logout.php">Log Out</a>
</nav>

<div class="layout">

    <!-- Existing Locations -->
    <div class="card">
        <h2>Existing Locations</h2>
        <p class="note">
            Locations represent physical storage areas such as rooms, shelves, or departments.
        </p>

        <table>
            <tr>
                <th>ID</th>
                <th>Location Name</th>
                <th>Action</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['LocationID']) ?></td>
                    <td><?= htmlspecialchars($row['LocationName']) ?></td>
                    <td>
                        <a href="?delete=<?= $row['LocationID'] ?>" 
                           onclick="return confirm('Delete this location?')">
                            <button class="delete-btn">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Add Location -->
    <div class="card">
        <h2>Add New Location</h2>

        <form method="post">
            <label for="locationName">Location Name</label>
            <input type="text" id="locationName" name="locationName" required>

            <button type="submit">Save Location</button>
        </form>
    </div>

</div>

</body>
</html>

<?php $conn->close(); ?>