<?php
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=EnergieTransitie", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Connected successfully");
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
}
?>