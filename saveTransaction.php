<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recordTransaction.php");
    exit();
}

$userID = $_SESSION['user_id'];
$itemLocationID = trim($_POST['item_location_id'] ?? '');
$transactionTypeID = trim($_POST['transaction_type_id'] ?? '');
$quantityChanged = trim($_POST['quantity_changed'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($itemLocationID === '' || $transactionTypeID === '' || $quantityChanged === '') {
    header("Location: recordTransaction.php?error=" . urlencode("All required fields must be completed."));
    exit();
}

if (!is_numeric($itemLocationID) || !is_numeric($transactionTypeID) || !is_numeric($quantityChanged)) {
    header("Location: recordTransaction.php?error=" . urlencode("Invalid numeric input."));
    exit();
}

$itemLocationID = (int)$itemLocationID;
$transactionTypeID = (int)$transactionTypeID;
$quantityChanged = (int)$quantityChanged;

if ($quantityChanged < 1) {
    header("Location: recordTransaction.php?error=" . urlencode("Quantity must be at least 1."));
    exit();
}

// Find transaction type name
$typeSql = "SELECT TransactionTypeName
            FROM transactiontype
            WHERE TransactionTypeID = ?";

$typeStmt = $conn->prepare($typeSql);

if (!$typeStmt) {
    die("Prepare failed: " . $conn->error);
}

$typeStmt->bind_param("i", $transactionTypeID);
$typeStmt->execute();
$typeResult = $typeStmt->get_result();

if ($typeResult->num_rows !== 1) {
    $typeStmt->close();
    $conn->close();
    header("Location: recordTransaction.php?error=" . urlencode("Invalid transaction type."));
    exit();
}

$typeRow = $typeResult->fetch_assoc();
$transactionTypeName = strtoupper(trim($typeRow['TransactionTypeName']));
$typeStmt->close();

// Get current quantity
$qtySql = "SELECT QuantityAtLocation
           FROM inventoryitemlocation
           WHERE ItemLocationID = ?";

$qtyStmt = $conn->prepare($qtySql);

if (!$qtyStmt) {
    die("Prepare failed: " . $conn->error);
}

$qtyStmt->bind_param("i", $itemLocationID);
$qtyStmt->execute();
$qtyResult = $qtyStmt->get_result();

if ($qtyResult->num_rows !== 1) {
    $qtyStmt->close();
    $conn->close();
    header("Location: recordTransaction.php?error=" . urlencode("Invalid item location."));
    exit();
}

$qtyRow = $qtyResult->fetch_assoc();
$currentQuantity = (int)$qtyRow['QuantityAtLocation'];
$qtyStmt->close();

// Calculate new quantity
if ($transactionTypeName === 'IN') {
    $newQuantity = $currentQuantity + $quantityChanged;
} elseif ($transactionTypeName === 'OUT') {
    $newQuantity = $currentQuantity - $quantityChanged;

    if ($newQuantity < 0) {
        $conn->close();
        header("Location: recordTransaction.php?error=" . urlencode("Cannot remove more inventory than is available."));
        exit();
    }
} else {
    $conn->close();
    header("Location: recordTransaction.php?error=" . urlencode("Unsupported transaction type."));
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert transaction record
    $insertSql = "INSERT INTO inventorytransaction
                  (ItemLocationID, UserID, TransactionTypeID, QuantityChanged, Notes)
                  VALUES (?, ?, ?, ?, ?)";

    $insertStmt = $conn->prepare($insertSql);

    if (!$insertStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $insertStmt->bind_param("iiiis", $itemLocationID, $userID, $transactionTypeID, $quantityChanged, $notes);
    $insertStmt->execute();
    $insertStmt->close();

    // Update quantity at location
    $updateSql = "UPDATE inventoryitemlocation
                  SET QuantityAtLocation = ?
                  WHERE ItemLocationID = ?";

    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $updateStmt->bind_param("ii", $newQuantity, $itemLocationID);
    $updateStmt->execute();
    $updateStmt->close();

    $conn->commit();

    header("Location: recordTransaction.php?success=" . urlencode("Transaction recorded successfully."));
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: recordTransaction.php?error=" . urlencode("Transaction failed: " . $e->getMessage()));
    exit();
}
?>