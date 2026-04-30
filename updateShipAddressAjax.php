<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User is not logged in.'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

$userID = (int)$_SESSION['user_id'];
$shipAddressID = trim($_POST['ship_address_id'] ?? '');
$recipientName = trim($_POST['recipient_name'] ?? '');
$street = trim($_POST['street'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$zip = trim($_POST['zip'] ?? '');

if ($shipAddressID === '' || !is_numeric($shipAddressID)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid shipping address ID. Received: [' . $shipAddressID . ']'
    ]);
    exit();
}

if ($recipientName === '' || $street === '' || $city === '' || $state === '' || $zip === '') {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit();
}

if (!preg_match('/^[0-9]{5}(-[0-9]{4})?$/', $zip)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ZIP code format.'
    ]);
    exit();
}

$shipAddressID = (int)$shipAddressID;

$checkSql = "SELECT ShipAddressID
             FROM shipaddress
             WHERE ShipAddressID = ? AND UserID = ?";

$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare failed: ' . $conn->error
    ]);
    exit();
}

$checkStmt->bind_param("ii", $shipAddressID, $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows !== 1) {
    $checkStmt->close();
    $conn->close();

    echo json_encode([
        'success' => false,
        'message' => 'That shipping address does not belong to your account.'
    ]);
    exit();
}

$checkStmt->close();

$updateSql = "UPDATE shipaddress
              SET RecipientName = ?, StreetAddress = ?, City = ?, State = ?, ZipCode = ?
              WHERE ShipAddressID = ? AND UserID = ?";

$updateStmt = $conn->prepare($updateSql);

if (!$updateStmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare failed: ' . $conn->error
    ]);
    exit();
}

$updateStmt->bind_param(
    "sssssii",
    $recipientName,
    $street,
    $city,
    $state,
    $zip,
    $shipAddressID,
    $userID
);

if ($updateStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Shipping address updated successfully.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating shipping address: ' . $updateStmt->error
    ]);
}

$updateStmt->close();
$conn->close();
?>