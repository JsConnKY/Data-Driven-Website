<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

if (
    $username === '' ||
    $first_name === '' ||
    $last_name === '' ||
    $email === '' ||
    $password === '' ||
    $confirm_password === ''
) {
    header("Location: register.php?error=" . urlencode("All fields are required."));
    exit();
}

if ($password !== $confirm_password) {
    header("Location: register.php?error=" . urlencode("Passwords do not match."));
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=" . urlencode("Please enter a valid email address."));
    exit();
}

// Check if username already exists
$checkUserSql = "SELECT UserID FROM staffuser WHERE UserName = ?";
$checkUserStmt = $conn->prepare($checkUserSql);

if (!$checkUserStmt) {
    die("Prepare failed: " . $conn->error);
}

$checkUserStmt->bind_param("s", $username);
$checkUserStmt->execute();
$checkUserStmt->store_result();

if ($checkUserStmt->num_rows > 0) {
    $checkUserStmt->close();
    header("Location: register.php?error=" . urlencode("That username is already taken."));
    exit();
}
$checkUserStmt->close();

// Check if email already exists
$checkEmailSql = "SELECT UserID FROM staffuser WHERE Email = ?";
$checkEmailStmt = $conn->prepare($checkEmailSql);

if (!$checkEmailStmt) {
    die("Prepare failed: " . $conn->error);
}

$checkEmailStmt->bind_param("s", $email);
$checkEmailStmt->execute();
$checkEmailStmt->store_result();

if ($checkEmailStmt->num_rows > 0) {
    $checkEmailStmt->close();
    header("Location: register.php?error=" . urlencode("An account with that email already exists."));
    exit();
}
$checkEmailStmt->close();

// Default new users to Staff role
$roleSql = "SELECT RoleID FROM role WHERE RoleName = 'Staff' LIMIT 1";
$roleResult = $conn->query($roleSql);

if (!$roleResult || $roleResult->num_rows !== 1) {
    die("Staff role not found. Please make sure the role table is seeded.");
}

$roleRow = $roleResult->fetch_assoc();
$roleID = $roleRow['RoleID'];

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertSql = "INSERT INTO staffuser (UserName, FirstName, LastName, Email, PasswordHash, RoleID)
              VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);

if (!$insertStmt) {
    die("Prepare failed: " . $conn->error);
}

$insertStmt->bind_param("sssssi", $username, $first_name, $last_name, $email, $passwordHash, $roleID);

if ($insertStmt->execute()) {
    $insertStmt->close();
    $conn->close();
    header("Location: login.php?error=" . urlencode("Account created successfully. Please log in."));
    exit();
} else {
    $errorMessage = "Error creating account: " . $insertStmt->error;
    $insertStmt->close();
    $conn->close();
    header("Location: register.php?error=" . urlencode($errorMessage));
    exit();
}
?>