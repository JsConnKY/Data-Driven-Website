<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: assignItemLocation.php");
    exit();
}

$itemLocationID = trim($_POST['item_location_id'] ?? '');

if ($itemLocationID === '' || !is_numeric($itemLocationID)) {
    header("Location: assignItemLocation.php?error=" . urlencode("Invalid item-location ID."));
    exit();
}

$itemLocationID = (int)$itemLocationID;

// Check whether transactions already exist for this item-location
$checkSql = "SELECT TransactionID
             FROM inventorytransaction
             WHERE ItemLocationID = ?";

$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    die("Prepare failed: " . $conn->error);
}

$checkStmt->bind_param("i", $itemLocationID);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    header("Location: assignItemLocation.php?error=" . urlencode("Cannot remove this item from location because transactions already exist for it."));
    exit();
}

$checkStmt->close();

// Delete the assignment
$deleteSql = "DELETE FROM inventoryitemlocation WHERE ItemLocationID = ?";
$deleteStmt = $conn->prepare($deleteSql);

if (!$deleteStmt) {
    die("Prepare failed: " . $conn->error);
}

$deleteStmt->bind_param("i", $itemLocationID);

if ($deleteStmt->execute()) {
    $deleteStmt->close();
    $conn->close();
    header("Location: assignItemLocation.php?success=" . urlencode("Item removed from location successfully."));
    exit();
} else {
    $errorMessage = "Error removing item from location: " . $deleteStmt->error;
    $deleteStmt->close();
    $conn->close();
    header("Location: assignItemLocation.php?error=" . urlencode($errorMessage));
    exit();
}
?>