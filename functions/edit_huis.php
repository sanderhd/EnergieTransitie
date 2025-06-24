<?php
// Haal het 'id' parameter uit de URL op en sla het op in een variabele
$id = isset($_GET['id']) ? $_GET['id'] : null;

session_start();

// DEBUG
// echo $id;

// Databaseverbinding via include
include '../db_conn.php';

try {
    // Query om alle data van het huis op te halen
    $stmt = $conn->prepare("SELECT * FROM energietransitie_data WHERE huis_id = ?");
    $stmt->execute([$id]);
    $huis_data = $stmt->fetchAll();
} catch (\PDOException $e) {
    echo "Database fout: " . $e->getMessage();
    $huis_data = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <h2>Huis Data - <?php echo $id; ?></h2>

    <a id="terug-button" href="../admin_dashboard.php">&larr; Terug</a> <br> <br>

    <button>
        <a href="upload_data.php?id=<?php echo $id; ?>">Upload Data</a>
    </button>
    
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
        </tr>
        <?php foreach ($huis_data as $row): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['Tijdstip']); ?></td>
            <td><?php echo htmlspecialchars($row['Zonnepaneelspanning_V']); ?></td>
            <td><?php echo htmlspecialchars($row['Zonnepaneelstroom_A']); ?></td>
            <td><?php echo htmlspecialchars($row['Waterstofproductie_Lu']); ?></td>
            <td><?php echo htmlspecialchars($row['Stroomverbruik_woning_kW']); ?></td>
            <td><?php echo htmlspecialchars($row['Waterstofverbruik_auto_Lu']); ?></td>
            <td><?php echo htmlspecialchars($row['Buitentemperatuur_C']); ?></td>
            <td><?php echo htmlspecialchars($row['Binnentemperatuur_C']); ?></td>
            <td><?php echo htmlspecialchars($row['Luchtdruk_hPa']); ?></td>
            <td><?php echo htmlspecialchars($row['Luchtvochtigheid_percent']); ?></td>
            <td><?php echo htmlspecialchars($row['Accuniveau_percent']); ?></td>
            <td><?php echo htmlspecialchars($row['CO2_concentratie_binnen_ppm']); ?></td>
            <td><?php echo htmlspecialchars($row['Waterstofopslag_woning_percent']); ?></td>
            <td><?php echo htmlspecialchars($row['Waterstofopslag_auto_percent']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    </main>

    
</body>
</html>