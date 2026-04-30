<?php
session_start();
include 'db_connect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: selectShipAddress.php");
    exit();
}

$userID = $_SESSION['user_id'];
$shipAddressID = trim($_POST['ship_address_id'] ?? '');

if ($shipAddressID === '' || !is_numeric($shipAddressID)) {
    header("Location: selectShipAddress.php?error=" . urlencode("Please select a valid shipping address."));
    exit();
}

// Make sure the selected address belongs to the logged-in user
$sql = "SELECT ShipAddressID
        FROM shipaddress
        WHERE ShipAddressID = ? AND UserID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$shipAddressIDInt = (int)$shipAddressID;
$stmt->bind_param("ii", $shipAddressIDInt, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $_SESSION['ship_address_id'] = $shipAddressIDInt;

    $stmt->close();
    $conn->close();

    header("Location: selectShipAddress.php?success=" . urlencode("Shipping address selected for current order."));
    exit();
} else {
    $stmt->close();
    $conn->close();

    header("Location: selectShipAddress.php?error=" . urlencode("That address is not available for your account."));
    exit();
}
?>