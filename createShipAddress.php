<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientName = trim($_POST['recipientName']);
    $streetAddress = trim($_POST['streetAddress']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zipCode = trim($_POST['zipCode']);

    if ($recipientName && $streetAddress && $city && $state && $zipCode) {
        $stmt = $conn->prepare("
            INSERT INTO shipaddress 
            (UserID, RecipientName, StreetAddress, City, State, ZipCode)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssss",
            $userID,
            $recipientName,
            $streetAddress,
            $city,
            $state,
            $zipCode
        );

        $stmt->execute();
        $stmt->close();

        header("Location: selectShipAddress.php");
        exit();
    } else {
        $message = "Please fill out all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Shipping Address</title>

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
            max-width: 700px;
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

        label {
            font-weight: bold;
            display: block;
            margin-top: 14px;
            margin-bottom: 6px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 14px;
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
            font-weight: bold;
            margin-top: 10px;
        }

        button:hover {
            background: #005fa3;
        }

        .error {
            background: #fdecea;
            color: #b00020;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="page-title">Create Shipping Address</div>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="selectShipAddress.php">Select Shipping Address</a>
    <a href="manageShipAddresses.php">Manage Shipping Addresses</a>
    <a href="logout.php">Log Out</a>
</nav>

<div class="container">
    <div class="card">
        <h2>Create Shipping Address</h2>

        <p class="note">
            Add a shipping address that can be selected later for an order.
        </p>

        <?php if ($message): ?>
            <div class="error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="recipientName">Recipient Name</label>
            <input type="text" id="recipientName" name="recipientName" required>

            <label for="streetAddress">Street Address</label>
            <input type="text" id="streetAddress" name="streetAddress" required>

            <label for="city">City</label>
            <input type="text" id="city" name="city" required>

            <label for="state">State</label>
            <input type="text" id="state" name="state" maxlength="50" required>

            <label for="zipCode">Zip Code</label>
            <input type="text" id="zipCode" name="zipCode" maxlength="15" required>

            <button type="submit">Save Address</button>
        </form>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>