<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: addItem.php");
    exit();
}

$sku_code = trim($_POST['sku_code'] ?? '');
$item_name = trim($_POST['item_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = trim($_POST['category_id'] ?? '');
$unit_id = trim($_POST['unit_id'] ?? '');
$reorder_threshold = trim($_POST['reorder_threshold'] ?? '');

if ($sku_code === '' || $item_name === '' || $category_id === '' || $unit_id === '' || $reorder_threshold === '') {
    header("Location: addItem.php?error=" . urlencode("All required fields must be completed."));
    exit();
}

if (!is_numeric($category_id) || !is_numeric($unit_id) || !is_numeric($reorder_threshold)) {
    header("Location: addItem.php?error=" . urlencode("Invalid numeric input."));
    exit();
}

if ((int)$reorder_threshold < 0) {
    header("Location: addItem.php?error=" . urlencode("Reorder threshold cannot be negative."));
    exit();
}

// Check if SKU already exists
$checkSql = "SELECT ItemID FROM inventoryitem WHERE SkuCode = ?";
$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    die("Prepare failed: " . $conn->error);
}

$checkStmt->bind_param("s", $sku_code);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    header("Location: addItem.php?error=" . urlencode("That SKU code already exists."));
    exit();
}
$checkStmt->close();

// Insert item
$insertSql = "INSERT INTO inventoryitem
              (CategoryID, UnitID, SkuCode, ItemName, Description, ReorderThreshold)
              VALUES (?, ?, ?, ?, ?, ?)";

$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    die("Prepare failed: " . $conn->error);
}

$category_id = (int)$category_id;
$unit_id = (int)$unit_id;
$reorder_threshold = (int)$reorder_threshold;

$insertStmt->bind_param(
    "iisssi",
    $category_id,
    $unit_id,
    $sku_code,
    $item_name,
    $description,
    $reorder_threshold
);

if ($insertStmt->execute()) {
    $insertStmt->close();
    $conn->close();
    header("Location: addItem.php?success=" . urlencode("Inventory item added successfully."));
    exit();
} else {
    $errorMessage = "Error adding item: " . $insertStmt->error;
    $insertStmt->close();
    $conn->close();
    header("Location: addItem.php?error=" . urlencode($errorMessage));
    exit();
}
?>