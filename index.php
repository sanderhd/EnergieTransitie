<?php
// Session starten voor de navbar
session_start();

// Database verbinding importeren
require_once 'db_conn.php';

// Aantal tevreden klanten ophalen
$query = "SELECT COUNT(*) AS count FROM users WHERE role = 'klant'";
$stmt = $conn->prepare($query);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);
$tevredenKlanten = $result['count'] ?? 0; // Aantal klanten, default naar 0 als er geen klanten zijn

$ervaring = floor((time() - strtotime('2025-06-17')) / (60 * 60 * 24));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Transitie</title>
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="JS/scroll-animate.js" defer></script>
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php"><img src="images/logo.png" alt="Energie logo" /></a>
        <span>Energie Transitie</span>
    </div>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?> <!-- Session inkijken of er userid is -->
            <?php
                $role = $_SESSION['role']; 
                if ($role === 'klant') { // Als role === klant dan klant_dashboard als link doen
                    echo '<a href="klant_dashboard.php">Dashboard</a>';
                } else { // anders admin dashboard
                    echo '<a href="admin_dashboard.php">Dashboard</a>';
                }
            ?>
            <a href="logout.php">Uitloggen</a> <!-- Uitloggen -->
        <?php else: ?> 
            <a href="login.php">Inloggen</a>
            <a href="register.php">Registreren</a>
        <?php endif; ?>
    </nav>
</header>

<div class="banner">
   <main>
    <section class="info-box">
      <div class="icon">
        <img src="images/logo.png" alt="Energie icon" />
      </div>
      <div class="text">
        <h1>Energie Transitie</h1>
        <p>Met ons energie-dashboard krijg je realtime inzicht in jouw verbruik, opwekking en besparing. Zo zet je eenvoudig stappen richting een duurzamere toekomst.</p>
      </div>
    </section>
  </main>
  
  <section class="over-ons scroll-animate">
    <h2><img src="images/icons/advies.png" alt="Over Ons" class="header-icon" />Over Ons</h2>
    <p>Wij zijn een team van energie-experts die zich inzetten voor een duurzame toekomst. Met onze tools en kennis helpen we jou om bewuste keuzes te maken. We geloven in een transparante en eerlijke aanpak, zodat jij de regie hebt over jouw energieverbruik.</p>
  </section>

  <section class="diensten scroll-animate">
    <h2><img src="images/icons/energy.png" alt="Diensten" class="header-icon" />Onze Diensten</h2>
    <div class="dienst">
      <h3><img src="images/icons/energy.png" alt="Energie Monitoring" class="header-icon" />Energie Monitoring</h3>
      <p>Realtime inzicht in jouw energieverbruik en opwekking.</p>
    </div>
    <div class="dienst">
      <h3><img src="images/icons/advies.png" alt="Advies" class="header-icon" />Advies</h3>
      <p>Persoonlijk advies voor een duurzamer energiegebruik.</p>
    </div>
    <div class="dienst">
      <h3><img src="images/icons/products.png" alt="Duurzame Producten" class="header-icon" />Duurzame Producten</h3>
      <p>Een breed assortiment aan duurzame energieproducten.</p>
    </div>
  </div>

  <section class="ervaring scroll-animate">
    <h2><img src="images/icons/products.png" alt="Ervaring" class="header-icon" />Onze Ervaring</h2>
    <p>Met jarenlange ervaring in de energiebranche, bieden wij oplossingen die zijn afgestemd op jouw behoeften. Samen werken we aan een duurzame toekomst.</p>
    <div class="ervaring-circles">
      <div class="ervaring-circle scroll-animate">
        <strong><?= htmlspecialchars($tevredenKlanten) ?></strong>
        <span>tevreden klanten</span>
      </div>
      <div class="ervaring-circle scroll-animate">
        <strong><?= htmlspecialchars($ervaring) ?></strong>
        <span>dagen ervaring</span>
      </div>
    </div>
  </section>

  <section class="contact scroll-animate">
    <h2>Contact</h2>
    <p>Heb je vragen of wil je meer weten? Neem contact met ons op via <a href="mailto:info@energietransitie.nl">info@energietransitie.nl</a>.</p>
  </section>

  <footer>
    <p>&copy; <?= date("Y") ?> Energie Transitie. Alle rechten voorbehouden.</p>
  </footer>
</body>
</html>
