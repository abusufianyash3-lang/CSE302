<?php

$conn = new mysqli("localhost", "root", "", "universitybustransport");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
