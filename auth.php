<?php
include 'config.php';
if (!isset($_SESSION['officer_name'])) {
    header("Location: login.php");
    exit();
}
?>