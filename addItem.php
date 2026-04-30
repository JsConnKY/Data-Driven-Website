<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$categorySql = "SELECT CategoryID, CategoryName FROM category ORDER BY CategoryName";
$categoryResult = $conn->query($categorySql);

$unitSql = "SELECT UnitID, UnitName FROM unitofmeasure ORDER BY UnitName";
$unitResult = $conn->query($unitSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory Item</title>
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

        .container {
            width: 90%;
            max-width: 700px;
            margin: 30px auto;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        h1, h2 {
            margin-top: 0;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
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

        input[type="submit"] {
            width: 100%;
            background-color: #0077cc;
            color: white;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #005fa3;
        }

        .top-links {
            margin-bottom: 20px;
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

        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .error {
            background-color: #ffe0e0;
            color: #990000;
        }

        .success {
            background-color: #e0ffe5;
            color: #006600;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Inventory Management System</h1>
        <p>Add Inventory Item</p>
    </div>

    <div class="container">
        <div class="top-links">
            <a href="dashboard.php">Back to Dashboard</a>
            <a href="logout.php">Log Out</a>
        </div>

        <h2>Add New Item</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <form action="insertItem.php" method="POST" id="addItemForm">
            <label for="sku_code">SKU Code</label>
            <input type="text" id="sku_code" name="sku_code" maxlength="40" required>

            <label for="item_name">Item Name</label>
            <input type="text" id="item_name" name="item_name" maxlength="100" required>

            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>

            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php while ($row = $categoryResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['CategoryID']; ?>">
                        <?php echo htmlspecialchars($row['CategoryName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="unit_id">Unit of Measure</label>
            <select id="unit_id" name="unit_id" required>
                <option value="">-- Select Unit --</option>
                <?php while ($row = $unitResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['UnitID']; ?>">
                        <?php echo htmlspecialchars($row['UnitName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="reorder_threshold">Reorder Threshold</label>
            <input type="number" id="reorder_threshold" name="reorder_threshold" min="0" required>

            <input type="submit" value="Add Item">
        </form>
    </div>

    <script>
        document.getElementById("addItemForm").addEventListener("submit", function(event) {
            const sku = document.getElementById("sku_code").value.trim();
            const itemName = document.getElementById("item_name").value.trim();
            const category = document.getElementById("category_id").value;
            const unit = document.getElementById("unit_id").value;
            const reorder = document.getElementById("reorder_threshold").value;

            if (sku === "" || itemName === "" || category === "" || unit === "" || reorder === "") {
                alert("Please fill in all required fields.");
                event.preventDefault();
                return;
            }

            if (parseInt(reorder) < 0) {
                alert("Reorder threshold cannot be negative.");
                event.preventDefault();
            }
        });
    </script>

</body>
</html>
<?php
$conn->close();
?>