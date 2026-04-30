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

$userID = (int)$_SESSION['user_id'];
$shipAddressID = $_GET['ship_address_id'] ?? '';

if ($shipAddressID === '' || !is_numeric($shipAddressID)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid shipping address ID.'
    ]);
    exit();
}

$shipAddressID = (int)$shipAddressID;

$sql = "SELECT ShipAddressID, RecipientName, StreetAddress, City, State, ZipCode
        FROM shipaddress
        WHERE ShipAddressID = ? AND UserID = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare failed: ' . $conn->error
    ]);
    exit();
}

$stmt->bind_param("ii", $shipAddressID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $address = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'address' => [
            'ShipAddressID' => $address['ShipAddressID'],
            'RecipientName' => $address['RecipientName'],
            'StreetAddress' => $address['StreetAddress'],
            'City' => $address['City'],
            'State' => $address['State'],
            'ZipCode' => $address['ZipCode']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Shipping address not found.'
    ]);
}

$stmt->close();
$conn->close();
?>