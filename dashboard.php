<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Energie Transitie Dashboard</title>
  <link rel="stylesheet" href="CSS/dashboard.css" />
</head>
<body>
<header>
    <div class="logo">
      <a href="index.php"><img src="images/logo.png" alt="Energie logo" /></a>
      <span>Energie Transitie</span>
    </div>
    <nav>
      <a href="login.php">Inloggen</a>
      <a href="register.php">Registreren</a>
    </nav>
  </header>

  <main>
    <section class="top-panels">
      <div class="panel terug">
        <div class="value">4.31 kWh</div>
        <div class="label">Terug geleverd</div>
      </div>
      <div class="panel zon">
        <div class="value">36.469 %</div>
        <div class="label">Zelf opgewekte zonnen energie</div>
      </div>
      <div class="panel niet-fossiel">
        <div class="value">70.79 %</div>
        <div class="label">Niet-fossiele energie verbruikt</div>
      </div>
      <div class="panel tijdsverbruik">
        <div>
          <strong>Week</strong><br/><span>0 kWh</span>
        </div>
        <div class="divider"></div>
        <div>
          <strong>Maand</strong><br/><span>0.8 kWh</span>
        </div>
      </div>
    </section>

    <section class="middle">
      <div class="graph">
        <h2>Energie Verbruik</h2>
        <img src="graph-placeholder.png" alt="Graph" />
      </div>
      <div class="right-panels">
        <div class="storage">
          <h3>Opslag</h3>
          <p>Gas - xx<br/>Stroom - xx</p>
        </div>
        <div class="kosten">
          <h3>Totale Kosten</h3>
          <p>Gas - $$<br/>Stroom - $$</p>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
