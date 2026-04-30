<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manageLocations.php");
    exit();
}

$locationName = trim($_POST['location_name'] ?? '');

if ($locationName === '') {
    header("Location: manageLocations.php?error=" . urlencode("Location name is required."));
    exit();
}

// Check for duplicate location name
$checkSql = "SELECT LocationID FROM location WHERE LocationName = ?";
$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    die("Prepare failed: " . $conn->error);
}

$checkStmt->bind_param("s", $locationName);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    header("Location: manageLocations.php?error=" . urlencode("That location already exists."));
    exit();
}

$checkStmt->close();

$insertSql = "INSERT INTO location (LocationName) VALUES (?)";
$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    die("Prepare failed: " . $conn->error);
}

$insertStmt->bind_param("s", $locationName);

if ($insertStmt->execute()) {
    $insertStmt->close();
    $conn->close();
    header("Location: manageLocations.php?success=" . urlencode("Location added successfully."));
    exit();
} else {
    $errorMessage = "Error adding location: " . $insertStmt->error;
    $insertStmt->close();
    $conn->close();
    header("Location: manageLocations.php?error=" . urlencode($errorMessage));
    exit();
}
?>