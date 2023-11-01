<?php
require_once "config.php";
// Create a database connection using mysqli
$conn = new mysqli('localhost', 'detaleco_ussd', '$,$gl~wVCaUU', 'detaleco_ussd');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
