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
    echo "Niet ingelogt.";
    exit();
}
// Haal het huis ID op
$huisId = $_GET['id'];
// Bereid de SQL statement voor om het huis te verwijderen
$stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
$stmt->bindParam(':id', $huisId, PDO::PARAM_INT);

if ($stmt->execute()) {
    // Stel een succesbericht in voordat we de sessie vernietigen
    $_SESSION['delete_message'] = "Uw account is succesvol verwijderd.";    
    session_write_close(); // Zorg ervoor dat het bericht wordt opgeslagen
    
    session_unset();
    session_destroy();
    header("Location: ../index.php"); // Verander de redirect naar index.php
    exit();
} else {
    echo "Fout bij het verwijderen van het account..";
}
?>