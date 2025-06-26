<?php
// Start sessie
session_start();

// Haal het 'id' parameter uit de URL op
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Databaseverbinding
include '../db_conn.php';

// Verwerk het formulier als er data is verstuurd
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'huis_data') {
        // Verwerking voor huisdata
        try {
            $sql = "UPDATE energietransitie_data SET 
                    Zonnepaneelspanning_V = ?,
                    Zonnepaneelstroom_A = ?,
                    Waterstofproductie_Lu = ?,
                    Stroomverbruik_woning_kW = ?,
                    Waterstofverbruik_auto_Lu = ?,
                    Buitentemperatuur_C = ?,
                    Binnentemperatuur_C = ?,
                    Luchtdruk_hPa = ?,
                    Luchtvochtigheid_percent = ?,
                    Accuniveau_percent = ?,
                    CO2_concentratie_binnen_ppm = ?,
                    Waterstofopslag_woning_percent = ?,
                    Waterstofopslag_auto_percent = ?
                    WHERE huis_id = ? AND Tijdstip = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $_POST['Zonnepaneelspanning_V'],
                $_POST['Zonnepaneelstroom_A'],
                $_POST['Waterstofproductie_Lu'],
                $_POST['Stroomverbruik_woning_kW'],
                $_POST['Waterstofverbruik_auto_Lu'],
                $_POST['Buitentemperatuur_C'],
                $_POST['Binnentemperatuur_C'],
                $_POST['Luchtdruk_hPa'],
                $_POST['Luchtvochtigheid_percent'],
                $_POST['Accuniveau_percent'],
                $_POST['CO2_concentratie_binnen_ppm'],
                $_POST['Waterstofopslag_woning_percent'],
                $_POST['Waterstofopslag_auto_percent'],
                $id,
                $_POST['Tijdstip']
            ]);

            header("Location: edit_huis.php?id=" . $id . "&success=1");
            exit();
        } catch (\PDOException $e) {
            echo "Database fout bij updaten: " . $e->getMessage();
        }
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'bewoners') {
        // Verwerking voor bewoners
        try {
            $stmt1 = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt1->execute([$_POST['bewoner1']]);
            $bewoner1_id = $stmt1->fetchColumn();

            $stmt2 = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt2->execute([$_POST['bewoner2']]);
            $bewoner2_id = $stmt2->fetchColumn();

            if (!$bewoner1_id || !$bewoner2_id) {
                throw new Exception("Een of beide bewoners konden niet gevonden worden.");
            }

            $stmt = $conn->prepare("UPDATE huizen SET bewoner1 = ?, bewoner2 = ? WHERE id = ?");
            $stmt->execute([$bewoner1_id, $bewoner2_id, $id]);

            header("Location: edit_huis.php?id=" . $id . "&success=1");
            exit();
        } catch (\PDOException $e) {
            echo "Database fout bij bewoners aanpassen: " . $e->getMessage();
        } catch (Exception $e) {
            echo "Fout: " . $e->getMessage();
        }
    }
}

// Huis en bewoners ophalen
try {
    $stmt = $conn->prepare("SELECT * FROM huizen WHERE id = ?");
    $stmt->execute([$id]);
    $huis = $stmt->fetch();

    $stmtUsers = $conn->query("SELECT id, username FROM users WHERE role = 'klant'");
    $users = $stmtUsers->fetchAll();
} catch (\PDOException $e) {
    echo "Database fout: " . $e->getMessage();
}

// Usernames ophalen van huidige bewoners
$bewoner1_username = '';
$bewoner2_username = '';

if (!empty($huis['bewoner1'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$huis['bewoner1']]);
    $bewoner1_username = $stmt->fetchColumn() ?: '';
}

if (!empty($huis['bewoner2'])) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$huis['bewoner2']]);
    $bewoner2_username = $stmt->fetchColumn() ?: '';
}

// Huisdata ophalen
try {
    $stmt = $conn->prepare("SELECT * FROM energietransitie_data WHERE huis_id = ?");
    $stmt->execute([$id]);
    $huis_data = $stmt->fetchAll();
} catch (\PDOException $e) {
    echo "Database fout bij data ophalen: " . $e->getMessage();
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Energie Transitie</title>
    <link rel="stylesheet" href="../CSS/admindash.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="../index.php"><img src="../images/logo.png" alt="Energie logo" /></a>
        <span>Energie Transitie</span>
    </div>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php echo $_SESSION['role'] === 'klant' ? '<a href="../klant_dashboard.php">Dashboard</a>' : '<a href="../admin_dashboard.php">Dashboard</a>'; ?>
            <a href="../logout.php">Uitloggen</a>
        <?php else: ?>
            <a href="../login.php">Inloggen</a>
            <a href="../register.php">Registreren</a>
        <?php endif; ?>
    </nav>
</header>

<?php if (isset($_GET['success'])): ?>
<script>
    alert("Gegevens succesvol opgeslagen!");
</script>
<?php endif; ?>


<main>
    <h2>Huis Data - <?php echo $id; ?></h2>
    <a id="terug-button" href="../admin_dashboard.php">&larr; Terug</a><br><br>

    <button><a href="upload_data.php?id=<?php echo $id; ?>">Upload Data</a></button>

    <form method="POST" style="margin-bottom:2rem;">
        <input type="hidden" name="form_type" value="bewoners">
        <div class="form-group">
            <label for="bewoner1">Bewoner 1:</label>
            <input id="bewoner1" type="text" name="bewoner1" value="<?php echo htmlspecialchars($bewoner1_username); ?>">
            <div id="suggest1" class="suggest-box" style="display:none;"></div>
        </div>

        <div class="form-group">
            <label for="bewoner2">Bewoner 2:</label>
            <input id="bewoner2" type="text" name="bewoner2" value="<?php echo htmlspecialchars($bewoner2_username); ?>">
            <div id="suggest2" class="suggest-box" style="display:none;"></div>
        </div><br>
        <button type="submit">Bewoners opslaan</button>
    </form>

    <table>
        <tr>
            <th>Tijdstip</th>
            <th>Zonnepaneelspanning (V)</th>
            <th>Zonnepaneelstroom (A)</th>
            <th>Waterstofproductie (Lu)</th>
            <th>Stroomverbruik woning (kW)</th>
            <th>Waterstofverbruik auto (Lu)</th>
            <th>Buitentemperatuur (°C)</th>
            <th>Binnentemperatuur (°C)</th>
            <th>Luchtdruk (hPa)</th>
            <th>Luchtvochtigheid (%)</th>
            <th>Accuniveau (%)</th>
            <th>CO2 binnen (ppm)</th>
            <th>Waterstofopslag woning (%)</th>
            <th>Waterstofopslag auto (%)</th>
            <th>Acties</th>
        </tr>
        <?php foreach ($huis_data as $row): ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="form_type" value="huis_data">
                <input type="hidden" name="Tijdstip" value="<?php echo htmlspecialchars($row['Tijdstip']); ?>">
                <?php foreach ([
                    'Zonnepaneelspanning_V', 'Zonnepaneelstroom_A', 'Waterstofproductie_Lu',
                    'Stroomverbruik_woning_kW', 'Waterstofverbruik_auto_Lu',
                    'Buitentemperatuur_C', 'Binnentemperatuur_C', 'Luchtdruk_hPa',
                    'Luchtvochtigheid_percent', 'Accuniveau_percent', 'CO2_concentratie_binnen_ppm',
                    'Waterstofopslag_woning_percent', 'Waterstofopslag_auto_percent'
                ] as $veld): ?>
                    <td><input type="number" step="0.01" name="<?php echo $veld; ?>" value="<?php echo htmlspecialchars($row[$veld]); ?>"></td>
                <?php endforeach; ?>
                <td><button type="submit">Opslaan</button></td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</main>

<script>
const setupAutocomplete = (inputId, suggestId) => {
    const input = document.getElementById(inputId);
    const suggestBox = document.getElementById(suggestId);

    input.addEventListener('input', async () => {
        const q = input.value.trim();
        if (q.length < 2) {
            suggestBox.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`../functions/user_suggest.php?q=${encodeURIComponent(q)}`);
            const suggestions = await response.json();
            
            suggestBox.innerHTML = '';
            suggestions.forEach(name => {
                const div = document.createElement('div');
                div.textContent = name;
                div.addEventListener('click', () => {
                    input.value = name;
                    suggestBox.style.display = 'none';
                });
                suggestBox.appendChild(div);
            });
            suggestBox.style.display = 'block';
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    });
};

setupAutocomplete('bewoner1', 'suggest1');
setupAutocomplete('bewoner2', 'suggest2');
</script>

</body>
</html>
