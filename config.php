<?php
session_start();   // Important: Must be at the top

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehisecure_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>