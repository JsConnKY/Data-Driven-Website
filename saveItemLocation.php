<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You must be logged in.'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

$itemID = trim($_POST['item_id'] ?? '');
$locationID = trim($_POST['location_id'] ?? '');
$quantityAtLocation = trim($_POST['quantity_at_location'] ?? '');

if ($itemID === '' || $locationID === '' || $quantityAtLocation === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'All required fields must be completed.'
    ]);
    exit();
}

if (!is_numeric($itemID) || !is_numeric($locationID) || !is_numeric($quantityAtLocation)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid numeric input.'
    ]);
    exit();
}

$itemID = (int)$itemID;
$locationID = (int)$locationID;
$quantityAtLocation = (int)$quantityAtLocation;

if ($quantityAtLocation < 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Starting quantity cannot be negative.'
    ]);
    exit();
}

$checkSql = "SELECT ItemLocationID
             FROM inventoryitemlocation
             WHERE ItemID = ? AND LocationID = ?";

$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare failed: ' . $conn->error
    ]);
    exit();
}

$checkStmt->bind_param("ii", $itemID, $locationID);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'error',
        'message' => 'That item is already assigned to that location.'
    ]);
    exit();
}

$checkStmt->close();

$insertSql = "INSERT INTO inventoryitemlocation (ItemID, LocationID, QuantityAtLocation)
              VALUES (?, ?, ?)";

$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare failed: ' . $conn->error
    ]);
    exit();
}

$insertStmt->bind_param("iii", $itemID, $locationID, $quantityAtLocation);

if ($insertStmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Item assigned successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error assigning item: ' . $insertStmt->error
    ]);
}

$insertStmt->close();
$conn->close();
?>