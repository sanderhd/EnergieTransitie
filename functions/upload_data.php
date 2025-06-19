<?php
require_once '../db_conn.php'; // Zorg dat deze het $conn (of $pdo) object bevat
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST["import"])) {
    if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {
        $fileName = $_FILES["file"]["tmp_name"];
        $file = fopen($fileName, "r");
        
        // Verwijder eerst alle bestaande data
        $conn->exec("TRUNCATE TABLE energietransitie_data");
        
        // Sla de header over (eerste regel)
        fgetcsv($file, 10000, ",");

        $sql = "INSERT INTO energietransitie_data (
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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $rowCount = 0;

        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            // Sla over als de rij niet genoeg kolommen heeft
            if (count($column) < 14) {
                continue;
            }

            // Optioneel: zet encoding goed
            $column = array_map(function($value) {
                return mb_convert_encoding(trim($value), 'UTF-8', 'auto');
            }, $column);

            // Converteer datum formaat van M/D/YYYY H:MM naar YYYY-MM-DD HH:MM:SS
            $dateTime = DateTime::createFromFormat('n/j/Y G:i', $column[0]);
            if ($dateTime === false) {
                continue;
            }
            $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

            try {
                $stmt->execute([
                    $formattedDateTime, $column[1], $column[2], $column[3], $column[4],
                    $column[5], $column[6], $column[7], $column[8], $column[9],
                    $column[10], $column[11], $column[12], $column[13]
                ]);
                $rowCount++;
            } catch (PDOException $e) {
                echo "Fout bij invoegen van rij: " . $e->getMessage();
                exit;
            }
        }
        fclose($file);
        echo "<div id='response' class='success'>CSV succesvol geïmporteerd! ($rowCount rijen toegevoegd)</div>";
    } else {
        echo '<div id="response" class="error">Selecteer een geldig CSV-bestand.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>CSV Uploaden</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; background: #e8f5e8; padding: 10px; margin: 10px 0; }
        .error { color: red; background: #f5e8e8; padding: 10px; margin: 10px 0; }
        form { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>CSV-bestand uploaden</h2>
    <form method="post" enctype="multipart/form-data">
        <label for="file">Kies CSV-bestand:</label>
        <input type="file" name="file" id="file" accept=".csv" required>
        <button type="submit" name="import">Uploaden</button>
    </form>
</body>
</html>