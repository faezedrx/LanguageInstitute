<?php
$conn = new mysqli("localhost", "", "", "level");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
?>
