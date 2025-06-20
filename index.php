<?php
// Session starten voor de navbar
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Transitie</title>
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="CSS/style.css">
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
        <h2>Energie Transitie</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor</p>
      </div>
    </section>
  </main>
</div>
</body>
</html>
