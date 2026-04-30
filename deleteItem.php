<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: viewItems.php");
    exit();
}

$itemID = trim($_POST['item_id'] ?? '');

if ($itemID === '' || !is_numeric($itemID)) {
    header("Location: viewItems.php?error=" . urlencode("Invalid item ID."));
    exit();
}

$itemID = (int)$itemID;

// Check whether item is assigned to any location
$checkLocationSql = "SELECT ItemLocationID
                     FROM inventoryitemlocation
                     WHERE ItemID = ?";

$checkLocationStmt = $conn->prepare($checkLocationSql);

if (!$checkLocationStmt) {
    die("Prepare failed: " . $conn->error);
}

$checkLocationStmt->bind_param("i", $itemID);
$checkLocationStmt->execute();
$checkLocationStmt->store_result();

if ($checkLocationStmt->num_rows > 0) {
    $checkLocationStmt->close();
    $conn->close();
    header("Location: viewItems.php?error=" . urlencode("Cannot delete item because it is assigned to a location."));
    exit();
}

$checkLocationStmt->close();

// Delete the item
$deleteSql = "DELETE FROM inventoryitem WHERE ItemID = ?";
$deleteStmt = $conn->prepare($deleteSql);

if (!$deleteStmt) {
    die("Prepare failed: " . $conn->error);
}

$deleteStmt->bind_param("i", $itemID);

if ($deleteStmt->execute()) {
    $deleteStmt->close();
    $conn->close();
    header("Location: viewItems.php?success=" . urlencode("Inventory item deleted successfully."));
    exit();
} else {
    $errorMessage = "Error deleting item: " . $deleteStmt->error;
    $deleteStmt->close();
    $conn->close();
    header("Location: viewItems.php?error=" . urlencode($errorMessage));
    exit();
}
?>