<?php
require_once '../db_conn.php';
session_start();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$huis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($huis_id <= 0) {
    die('Geen geldig huis ID opgegeven in de URL.');
}

if (isset($_POST["import"])) {
    if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {
        $fileName = $_FILES["file"]["tmp_name"];
        $file = fopen($fileName, "r");

        $deleteStmt = $conn->prepare("DELETE FROM energietransitie_data WHERE huis_id = ?");
        $deleteStmt->execute([$huis_id]);

        fgetcsv($file, 10000, ",");

        $sql = "INSERT INTO energietransitie_data (
            huis_id,
            Tijdstip,
            Zonnepaneelspanning_V,
            Zonnepaneelstroom_A,
            Waterstofproductie_Lu,
            Stroomverbruik_woning_kW,
            Waterstofverbruik_auto_Lu,
            Buitentemperatuur_C,
            Binnentemperatuur_C,
            Luchtdruk_hPa,
            Luchtvochtigheid_percent,
            Accuniveau_percent,
            CO2_concentratie_binnen_ppm,
            Waterstofopslag_woning_percent,
            Waterstofopslag_auto_percent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $rowCount = 0;
        $errorCount = 0;

        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            if (count($column) < 14) {
                $errorCount++;
                continue;
            }

            $column = array_map(function($value) {
                return mb_convert_encoding(trim($value), 'UTF-8', 'auto');
            }, $column);

            $dateTime = DateTime::createFromFormat('n/j/Y G:i', $column[0]);
            if ($dateTime === false) {
                $dateTime = DateTime::createFromFormat('m/d/Y H:i', $column[0]);
                if ($dateTime === false) {
                    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $column[0]);
                }
            }

            if ($dateTime === false) {
                $errorCount++;
                continue;
            }

            $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

            try {
                $stmt->execute([
                    $huis_id,
                    $formattedDateTime, $column[1], $column[2], $column[3], $column[4],
                    $column[5], $column[6], $column[7], $column[8], $column[9],
                    $column[10], $column[11], $column[12], $column[13]
                ]);
                $rowCount++;
            } catch (PDOException $e) {
                $errorCount++;
                error_log("CSV import fout: " . $e->getMessage());
            }
        }
        fclose($file);

        $message = "CSV succesvol geïmporteerd voor huis ID $huis_id! ($rowCount rijen toegevoegd";
        if ($errorCount > 0) {
            $message .= ", $errorCount rijen overgeslagen wegens fouten";
        }
        $message .= ")";

        echo $message;
    } else {
        echo 'Selecteer een geldig CSV-bestand.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Energie Transitie - Huis <?php echo htmlspecialchars($huis_id); ?></title>
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/createhuis.css">
    <link rel="stylesheet" href="../css/style.css">
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

    <main>
        <h1>CSV-bestand uploaden voor Huis <?php echo htmlspecialchars($huis_id); ?></h2>
        <p><strong>Let op:</strong> Het uploaden van een nieuw CSV-bestand zal alle bestaande data voor dit huis vervangen.</p>
        <form method="post" enctype="multipart/form-data">
            <label for="file">Kies CSV-bestand:</label>
            <input type="file" name="file" id="file" accept=".csv" required>
            <button type="submit" name="import">CSV Uploaden</button>
        </form>
    </main>
    
</body>
</html>