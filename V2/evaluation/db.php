<?php
$host = "localhost";
$user = "";
$pass = "";
$dbname = "Evaluation";

$mysqli = new mysqli($host, $user, $pass, $dbname);
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
