<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header("Location: login.php?error=" . urlencode("Please enter both email and password."));
    exit();
}

$sql = "SELECT 
            staffuser.UserID,
            staffuser.UserName,
            staffuser.FirstName,
            staffuser.LastName,
            staffuser.Email,
            staffuser.PasswordHash,
            role.RoleName
        FROM staffuser
        INNER JOIN role ON staffuser.RoleID = role.RoleID
        WHERE staffuser.Email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['PasswordHash'])) {
        $_SESSION['user_id'] = $row['UserID'];
        $_SESSION['username'] = $row['UserName'];
        $_SESSION['first_name'] = $row['FirstName'];
        $_SESSION['last_name'] = $row['LastName'];
        $_SESSION['email'] = $row['Email'];
        $_SESSION['role'] = $row['RoleName'];

        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=" . urlencode("Invalid password."));
        exit();
    }
} else {
    header("Location: login.php?error=" . urlencode("No account found with that email address."));
    exit();
}

$stmt->close();
$conn->close();
?>