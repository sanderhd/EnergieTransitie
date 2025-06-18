<?php
// Verbind met de database
include_once '../db_conn.php';

// Haal de zoekterm op uit de URL, of gebruik een lege string als die er niet is
$q = $_GET['q'] ?? '';

// Als er geen zoekterm is, geef een lege lijst terug en stop
if ($q === '') {
    echo json_encode([]);
    exit();
}

// Zoek maximaal 10 gebruikersnamen die beginnen met de zoekterm
$stmt = $conn->prepare("SELECT username FROM users WHERE username LIKE :q LIMIT 10");
$stmt->execute([':q' => $q . '%']);

// Haal de gevonden gebruikersnamen op
$usernames = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Geef de gebruikersnamen terug als JSON
echo json_encode($usernames);
?>