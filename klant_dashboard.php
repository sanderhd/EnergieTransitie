<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Widget Dashboard</title>
  <link rel="stylesheet" href="CSS/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
</head>
<body>
  <header>
    <div class="logo">
      <a href="index.php"><img src="images/logo.png" alt="Energie logo" /></a>
      <span>Energie Transitie</span>
    </div>
    <nav>
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php
          $role = $_SESSION['role'];
          if ($role === 'klant') {
            echo '<a href="klant_dashboard.php">Dashboard</a>';
          } else {
            echo '<a href="admin_dashboard.php">Dashboard</a>';
          }
        ?>
        <a href="logout.php">Uitloggen</a>
      <?php else: ?>
        <a href="login.php">Inloggen</a>
        <a href="register.php">Registreren</a>
      <?php endif; ?>
    </nav>
  </header>

  <div id="dashboard-container">
    <div id="toolbar">
      <label>
        <input type="checkbox" id="editToggle"> Edit Mode
      </label>
    </div>
    <div id="dashboard"></div>
  </div>

  <div id="library">
    <h1>Widgets</h1>
    

    <h4>Stroom</h4>
    <div class="widget-item" draggable="true" data-widget="stroomverbruik">⚡ Stroomverbruik</div>

    <h4>Zonnepanelen</h4>
    <div class="widget-item" draggable="true" data-widget="zonnepaneel">🔆 Zonnepaneel</div>
    
    <h4>Waterstof</h4>
    <div class="widget-item" draggable="true" data-widget="waterstofopslag">🌦️ Waterstof Opslag</div>

    <h4>Co2</h4>
    <div class="widget-item" draggable="true" data-widget="co2">🟣 CO₂-concentratie</div>

    <h4>Lucht</h4>
    <div class="widget-item" draggable="true" data-widget="luchtdruk">☁️ Luchtdruk</div>
    <div class="widget-item" draggable="true" data-widget="luchtvochtigheid">💧 Luchtvochtigheid</div>

    <h4>Temperaturen</h4>
    <div class="widget-item" draggable="true" data-widget="binnentemperatuur_grafiek">🌡️ Binnentemperatuur Grafiek</div>
    <div class="widget-item" draggable="true" data-widget="binnentemperatuur_laatste">🌡️ Huidige Binnentemperatuur</div>
    <br>
    <div class="widget-item" draggable="true" data-widget="buitentemperatuur_grafiek">❄️ Buitentemperatuur Grafiek</div>
    <div class="widget-item" draggable="true" data-widget="buitentemperatuur_laatste">❄️ Huidige Buitentemperatuur</div>

    <h4>Accu Niveau</h4>
    <div class="widget-item" draggable="true" data-widget="accu_grafiek">🔋 Accu Niveau Grafiek</div>
    <div class="widget-item" draggable="true" data-widget="accu_procent">🔋 Accu Laatste</div>
    <div class="widget-item" draggable="true" data-widget="accu_circle">🔋 Accu Cirkel</div>
  </div>

  <script src="JS/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const editToggle = document.getElementById('editToggle');
      editToggle.addEventListener('change', toggleEditMode);
    });
    fetch('data_get.php/energietransitie_data')
  </script>
</body>
</html>