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

// Deactivate item (soft delete)
$sql = "UPDATE inventoryitem
        SET IsActive = 0
        WHERE ItemID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $itemID);

if ($stmt->execute()) {
    header("Location: viewItems.php?success=" . urlencode("Item deactivated successfully."));
} else {
    header("Location: viewItems.php?error=" . urlencode("Error deactivating item: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>