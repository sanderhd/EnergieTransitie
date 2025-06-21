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

    if (count($bewoners) > 2) {
        return "Maximaal 2 bewoners toegestaan.";
    }

    if (count($bewoners) !== count(array_unique($bewoners))) {
        return "Bewoners mogen niet hetzelfde zijn.";
    }

    $bewonerIds = [];
    foreach ($bewoners as $b) {
        $id = getUserIdByUsername($b);
        if (!$id) {
            return "Gebruiker '$b' bestaat niet.";
        }
        $bewonerIds[] = $id;
    }

    while (count($bewonerIds) < 2) {
        $bewonerIds[] = null;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO huizen (bewoner1, bewoner2) VALUES (:b1, :b2)");
        $stmt->execute([
            ':b1' => $bewonerIds[0],
            ':b2' => $bewonerIds[1],
        ]);
        return true;
    } catch (PDOException $e) {
        return "Fout bij aanmaken huis: " . $e->getMessage();
    }
}

// === Verwerk formulier ===
$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bewoner1 = $_POST['bewoner1'] ?? '';
    $bewoner2 = $_POST['bewoner2'] ?? '';

    if ($bewoner1 === '' && $bewoner2 === '') {
        $errorMessage = <<<HTML
<div class="error-box">
    <div class="error-icon">❌</div>
    <h2>Fout!</h2>
    <p>Voer minstens één bewoner in of laat beide leeg voor een leeg huis.</p>
</div>
HTML;
    } else {
        $result = createHuis([$bewoner1, $bewoner2]);

        if ($result === true) {
            $successMessage = <<<HTML
<div class="success-box">
    <div class="success-icon">✅</div>
    <h2>Huis Aangemaakt!</h2>
    <p>Het huis is succesvol toegevoegd aan de database.</p>
    <a href="../admin_dashboard.php" class="button-link">↩ Terug naar Dashboard</a>
</div>
HTML;
        } else {
            $errorMessage = <<<HTML
<div class="error-box">
    <div class="error-icon">❌</div>
    <h2>Fout!</h2>
    <p>{$result}</p>
</div>
HTML;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Energie Transitie | Huis Aanmaken</title>
<link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
<link rel="stylesheet" href="../CSS/createhuis.css"/>
<link rel="stylesheet" href="../CSS/style.css"/>
</head>
<body>
    <header>
    <div class="logo">
        <a href="../index.php"><img src="../images/logo.png" alt="Energie logo" /></a>
        <span>Energie Transitie</span>
    </div>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $role = $_SESSION['role'];
                if ($role === 'klant') {
                    echo '<a href="../klant_dashboard.php">Dashboard</a>';
                } else {
                    echo '<a href="../admin_dashboard.php">Dashboard</a>';
                }
            ?>
            <a href="../logout.php">Uitloggen</a>
        <?php else: ?>
            <a href="../login.php">Inloggen</a>
            <a href="../register.php">Registreren</a>
        <?php endif; ?>
    </nav>
</header>

<?php if (isset($successMessage)) echo $successMessage; ?>
<?php if (isset($errorMessage)) echo $errorMessage; ?>

<main>
    <h1>Nieuw Huis Aanmaken</h1>
    
    <form action="create_huis.php" method="post" autocomplete="off">
        <div class="form-group">
            <label for="bewoner1">Bewoner 1 (gebruikersnaam):</label>
            <input id="bewoner1" type="text" name="bewoner1">
            <div id="suggest1" class="suggest-box" style="display:none;"></div>
        </div>

        <div class="form-group">
            <label for="bewoner2">Bewoner 2 (gebruikersnaam):</label>
            <input id="bewoner2" type="text" name="bewoner2">
            <div id="suggest2" class="suggest-box" style="display:none;"></div>
        </div>

        <button type="submit">Maak Huis Aan</button>
    </form>
</main>

<a id="terug-button" href="../admin_dashboard.php">&larr; Terug</a>

<script>
function setupAutocomplete(inputId, suggestId) {
    const input = document.getElementById(inputId);
    const suggestBox = document.getElementById(suggestId);

    input.addEventListener('input', async () => {
        const query = input.value.trim();
        if (query.length === 0) {
            suggestBox.style.display = 'none';
            suggestBox.innerHTML = '';
            return;
        }

        const response = await fetch('user_suggest.php?q=' + encodeURIComponent(query));
        const suggestions = await response.json();

        if (suggestions.length === 0) {
            suggestBox.style.display = 'none';
            suggestBox.innerHTML = '';
            return;
        }

        suggestBox.innerHTML = '';
        for (const name of suggestions) {
            const div = document.createElement('div');
            div.textContent = name;
            div.addEventListener('click', () => {
                input.value = name;
                suggestBox.style.display = 'none';
                suggestBox.innerHTML = '';
            });
            suggestBox.appendChild(div);
        }
        suggestBox.style.display = 'block';
    });

    input.addEventListener('blur', () => {
        setTimeout(() => {
            suggestBox.style.display = 'none';
        }, 200);
    });
}

setupAutocomplete('bewoner1', 'suggest1');
setupAutocomplete('bewoner2', 'suggest2');
</script>
</body>
</html>
