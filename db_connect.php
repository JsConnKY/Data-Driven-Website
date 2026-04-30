<?php
$host = "localhost";
$dbusername = "root";
$dbpassword = "rootpassword";
$dbname = "JessieConnFPDatabase";

$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>