<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$message = "";

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateAddress'])) {

    $shipAddressID = intval($_POST['ShipAddressID']);
    $recipientName = trim($_POST['RecipientName']);
    $streetAddress = trim($_POST['StreetAddress']);
    $city = trim($_POST['City']);
    $state = trim($_POST['State']);
    $zipCode = trim($_POST['ZipCode']);

    if ($shipAddressID > 0 && $recipientName && $streetAddress && $city && $state && $zipCode) {

        $stmt = $conn->prepare("
            UPDATE shipaddress
            SET RecipientName=?, StreetAddress=?, City=?, State=?, ZipCode=?
            WHERE ShipAddressID=? AND UserID=?
        ");

        $stmt->bind_param(
            "sssssii",
            $recipientName,
            $streetAddress,
            $city,
            $state,
            $zipCode,
            $shipAddressID,
            $userID
        );

        $stmt->execute();
        $stmt->close();

        header("Location: manageShipAddresses.php");
        exit();
    } else {
        $message = "Please fill out all fields.";
    }
}

// Load addresses
$stmt = $conn->prepare("
    SELECT ShipAddressID, RecipientName, StreetAddress, City, State, ZipCode
    FROM shipaddress
    WHERE UserID = ?
    ORDER BY RecipientName ASC
");

$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Shipping Addresses</title>

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
    grid-template-columns: 1fr 1fr;
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

.address-card {
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 15px;
    background-color: #fafafa;
}

.address-actions {
    margin-top: 10px;
}

button {
    background: #0077cc;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
}

button:hover {
    background: #005fa3;
}

form button {
    margin-top: 15px;
}

label {
    font-weight: bold;
    display: block;
    margin-top: 12px;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.error {
    width: 90%;
    margin: 10px auto;
    background: #fdecea;
    color: #b00020;
    padding: 12px;
    border-radius: 5px;
}
</style>
</head>

<body>

<div class="page-title">Manage Shipping Addresses</div>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="createShipAddress.php">Create Shipping Address</a>
    <a href="selectShipAddress.php">Select Shipping Address</a>
    <a href="logout.php">Log Out</a>
</nav>

<?php if ($message): ?>
    <div class="error"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="layout">

<!-- LEFT SIDE -->
<div class="card">
<h2>Saved Addresses</h2>

<?php while ($row = $result->fetch_assoc()): ?>
    <div class="address-card">
        <strong><?= htmlspecialchars($row['RecipientName']) ?></strong><br>
        <?= htmlspecialchars($row['StreetAddress']) ?><br>
        <?= htmlspecialchars($row['City']) ?>, <?= htmlspecialchars($row['State']) ?> <?= htmlspecialchars($row['ZipCode']) ?>

        <div class="address-actions">
            <button onclick="loadAddress(
                '<?= $row['ShipAddressID'] ?>',
                '<?= htmlspecialchars($row['RecipientName'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['StreetAddress'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['City'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['State'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['ZipCode'], ENT_QUOTES) ?>'
            )">
                Edit Address
            </button>
        </div>
    </div>
<?php endwhile; ?>

</div>

<!-- RIGHT SIDE -->
<div class="card">
<h2>Edit Shipping Address</h2>

<form method="post">
    <input type="hidden" id="ShipAddressID" name="ShipAddressID">

    <label>Recipient Name</label>
    <input type="text" id="RecipientName" name="RecipientName" required>

    <label>Street Address</label>
    <input type="text" id="StreetAddress" name="StreetAddress" required>

    <label>City</label>
    <input type="text" id="City" name="City" required>

    <label>State</label>
    <input type="text" id="State" name="State" required>

    <label>Zip Code</label>
    <input type="text" id="ZipCode" name="ZipCode" required>

    <button type="submit" name="updateAddress">Update Address</button>
</form>

</div>

</div>

<script>
function loadAddress(id, recipient, street, city, state, zip) {
    document.getElementById("ShipAddressID").value = id;
    document.getElementById("RecipientName").value = recipient;
    document.getElementById("StreetAddress").value = street;
    document.getElementById("City").value = city;
    document.getElementById("State").value = state;
    document.getElementById("ZipCode").value = zip;
}
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>