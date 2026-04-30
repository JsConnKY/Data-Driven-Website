<?php
session_start();
include 'db_connect.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: createShipAddress.php");
    exit();
}

$userID = $_SESSION['user_id'];
$recipientName = trim($_POST['recipient_name'] ?? '');
$street = trim($_POST['street'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$zip = trim($_POST['zip'] ?? '');

// Validate required fields
if ($recipientName === '' || $street === '' || $city === '' || $state === '' || $zip === '') {
    header("Location: createShipAddress.php?error=" . urlencode("All fields are required."));
    exit();
}

// Basic ZIP validation
if (!preg_match('/^[0-9]{5}(-[0-9]{4})?$/', $zip)) {
    header("Location: createShipAddress.php?error=" . urlencode("Invalid ZIP code format."));
    exit();
}

// Insert address
$sql = "INSERT INTO shipaddress (UserID, RecipientName, StreetAddress, City, State, ZipCode)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("isssss", $userID, $recipientName, $street, $city, $state, $zip);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: createShipAddress.php?success=" . urlencode("Shipping address saved successfully."));
    exit();
} else {
    $errorMessage = "Error saving shipping address: " . $stmt->error;
    $stmt->close();
    $conn->close();
    header("Location: createShipAddress.php?error=" . urlencode($errorMessage));
    exit();
}
?>