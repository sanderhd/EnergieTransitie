<?php
// Verbind met de database
include_once '../db_conn.php';
// Start een sessie
session_start();
// Controleer of gebruiker is ingelogd
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
// Controleer of er een ID is meegegeven
if (!isset($_GET['id'])) {
    echo "Geen huis ID opgegeven.";
    exit();
}
// Haal het huis ID op
$huisId = $_GET['id'];
// Bereid de SQL statement voor om het huis te verwijderen
$stmt = $conn->prepare("DELETE FROM huizen WHERE id = :id");
$stmt->bindParam(':id', $huisId, PDO::PARAM_INT);
// Voer de statement uit
if ($stmt->execute()) {
    // Redirect naar de admin dashboard na succesvolle verwijdering
    header("Location: ../admin_dashboard.php?message=Huis+verwijderd");
} else {
    // Geef een foutmelding als het verwijderen niet is gelukt
    echo "Fout bij het verwijderen van het huis.";
}
?>