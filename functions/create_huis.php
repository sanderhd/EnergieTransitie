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

// Functie om user ID op te halen via gebruikersnaam
function getUserIdByUsername($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    return $stmt->fetchColumn();
}

// Functie om een huis aan te maken met maximaal 2 bewoners
function createHuis($bewoners) {
    global $conn;

    // Verwijder lege strings uit de bewonerslijst
    $bewoners = array_filter(array_map('trim', $bewoners));

    // Controleer of er niet meer dan 2 bewoners zijn
    if (count($bewoners) > 2) {
        echo "Maximaal 2 bewoners toegestaan.";
        return false;
    }

    // Controleer of bewoners uniek zijn
    if (count($bewoners) !== count(array_unique($bewoners))) {
        echo "Bewoners mogen niet hetzelfde zijn.";
        return false;
    }

    $bewonerIds = [];
    // Haal de user ID's op van de bewoners
    foreach ($bewoners as $b) {
        $id = getUserIdByUsername($b);
        if (!$id) {
            echo "Gebruiker '$b' bestaat niet.";
            return false;
        }
        $bewonerIds[] = $id;
    }

    // Vul aan met NULL als er minder dan 2 bewoners zijn
    while (count($bewonerIds) < 2) {
        $bewonerIds[] = null;
    }

    try {
        // Voeg het huis toe aan de database
        $stmt = $conn->prepare("INSERT INTO huizen (bewoner1, bewoner2) VALUES (:b1, :b2)");
        $stmt->execute([
            ':b1' => $bewonerIds[0],
            ':b2' => $bewonerIds[1],
        ]);
        return true;
    } catch (PDOException $e) {
        // Foutmelding als het niet lukt
        echo "Fout bij aanmaken huis: " . $e->getMessage();
        return false;
    }
}

// Als het formulier is verstuurd (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Haal de ingevulde bewoners op
    $bewoner1 = $_POST['bewoner1'] ?? '';
    $bewoner2 = $_POST['bewoner2'] ?? '';

    // Controleer of beide velden leeg zijn
    if ($bewoner1 === '' && $bewoner2 === '') {
        echo "Voer minstens één bewoner in of laat beide leeg voor een leeg huis.";
    } else {
        // Probeer het huis aan te maken
        if (createHuis([$bewoner1, $bewoner2])) {
            echo "Huis succesvol aangemaakt.";
        } else {
            echo "Er is een fout opgetreden bij het aanmaken van het huis.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Huis Aanmaken</title>

<style>
/* Stijl voor de suggestie-box onder de invoervelden */
.suggest-box {
    border: 1px solid #ccc;
    max-height: 150px;
    overflow-y: auto;
    position: absolute;
    background: white;
    width: 200px;
    z-index: 1000;
}
.suggest-box div {
    padding: 5px;
    cursor: pointer;
}
.suggest-box div:hover {
    background-color: #eee;
}
.input-container {
    position: relative;
    margin-bottom: 1.5em;
}
</style>

</head>
<body>
<!-- Link terug naar het dashboard -->
<a href="../admin_dashboard.php">&larr; Terug</a>
<h1>Nieuw Huis Aanmaken</h1>

<!-- Formulier om een huis aan te maken -->
<form action="create_huis.php" method="post" autocomplete="off">
    <div class="input-container">
        <label for="bewoner1">Bewoner 1 (gebruikersnaam):</label><br>
        <input type="text" id="bewoner1" name="bewoner1">
        <div id="suggest1" class="suggest-box" style="display:none;"></div>
    </div>

    <div class="input-container">
        <label for="bewoner2">Bewoner 2 (gebruikersnaam):</label><br>
        <input type="text" id="bewoner2" name="bewoner2">
        <div id="suggest2" class="suggest-box" style="display:none;"></div>
    </div>

    <input type="submit" value="Maak Huis Aan">
</form>

<script>
// Functie om autocomplete (suggesties) te tonen bij het typen
function setupAutocomplete(inputId, suggestId) {
    const input = document.getElementById(inputId);
    const suggestBox = document.getElementById(suggestId);

    input.addEventListener('input', async () => {
        const query = input.value.trim();
        // Als er niks is ingevuld, verberg suggesties
        if (query.length === 0) {
            suggestBox.style.display = 'none';
            suggestBox.innerHTML = '';
            return;
        }

        // Haal suggesties op van de server
        const response = await fetch('user_suggest.php?q=' + encodeURIComponent(query));
        const suggestions = await response.json();

        // Geen suggesties? Verberg de box
        if (suggestions.length === 0) {
            suggestBox.style.display = 'none';
            suggestBox.innerHTML = '';
            return;
        }

        // Toon suggesties
        suggestBox.innerHTML = '';
        for (const name of suggestions) {
            const div = document.createElement('div');
            div.textContent = name;
            // Als je op een suggestie klikt, vul het veld in
            div.addEventListener('click', () => {
                input.value = name;
                suggestBox.style.display = 'none';
                suggestBox.innerHTML = '';
            });
            suggestBox.appendChild(div);
        }
        suggestBox.style.display = 'block';
    });

    // Verberg suggesties als het veld niet meer in focus is
    input.addEventListener('blur', () => {
        setTimeout(() => {
            suggestBox.style.display = 'none';
        }, 200);
    });
}

// Zet autocomplete op beide invoervelden
setupAutocomplete('bewoner1', 'suggest1');
setupAutocomplete('bewoner2', 'suggest2');
</script>
</body>
</html>