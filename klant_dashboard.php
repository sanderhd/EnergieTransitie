<?php
include_once 'db_conn.php';
session_start();

// Haal huis_id op uit de session
$huis_id = $_SESSION['huis_id'] ?? null;

if (!$huis_id) {
  die('Je bent nog niet aan een huis gekoppeld. Contacteer onze admins.');
}

// Haal zonnenpaneelspanning data op uit de database
$spanning_data = [];
$labels = [];
$sql = "SELECT Tijdstip, Zonnepaneelspanning_V FROM energietransitie_data WHERE huis_id = :huis_id ORDER BY Tijdstip ASC";
$stmt = $conn->prepare($sql);
$stmt->execute(['huis_id' => $huis_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $labels[] = $row['Tijdstip'];
  $spanning_data[] = floatval($row['Zonnepaneelspanning_V']); // Zorg dat het een getal is
}

$stroomdata = [];
$labels_stroom = [];
$sql_stroom = "SELECT Tijdstip, Zonnepaneelstroom_A FROM energietransitie_data WHERE huis_id = :huis_id ORDER BY Tijdstip ASC";
$stmt_stroom = $conn->prepare($sql_stroom);
$stmt_stroom->execute(['huis_id' => $huis_id]);
while ($row_stroom = $stmt_stroom->fetch(PDO::FETCH_ASSOC)) {
  $labels_stroom[] = $row_stroom['Tijdstip'];
  $stroomdata[] = floatval($row_stroom['Zonnepaneelstroom_A']); // Zorg dat het een getal is
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Energie Transitie Dashboard</title>
  <link rel="stylesheet" href="CSS/dashboard.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
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
  <div class="dashboard-container">
    <h1>Zonnepaneel Spanning Dashboard</h1>
    <div class="chart-container">
      <canvas id="Zonnepaneelspanning"></canvas>
    </div>
  </div>

  <div class="dashboard-container">
    <h1>Zonnepaneelstroom</h1>
      <div class="chart-container">
        <canvas id="Zonnepaneelstroom"></canvas>
      </div>
  </div>
</main>

<script>
// Zorg ervoor dat de data correct is
const labels = <?php echo json_encode($labels); ?>;
const spanningData = <?php echo json_encode($spanning_data); ?>;

const stroomData = <?php echo json_encode($stroomdata); ?>;
const labelsStroom = <?php echo json_encode($labels_stroom); ?>;

// DEBUG
// console.log('Labels:', labels);
// console.log('Spanning Data:', spanningData);

const ctx = document.getElementById('Zonnepaneelspanning').getContext('2d');
const zonnepaneelspanningChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      label: 'Zonnepaneel Spanning (V)',
      data: spanningData,
      borderColor: 'rgba(255, 206, 86, 1)',
      backgroundColor: 'rgba(255, 206, 86, 0.2)',
      borderWidth: 2,
      fill: true,
      tension: 0.4, // Maakt de lijn wat gladder
      pointRadius: 3,
      pointHoverRadius: 6,
      pointBackgroundColor: 'rgba(255, 206, 86, 1)',
      pointBorderColor: '#fff',
      pointBorderWidth: 2
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      title: {
        display: true,
        text: 'Zonnepaneel Spanning Over Tijd',
        font: {
          size: 16,
          weight: 'bold'
        }
      },
      legend: {
        display: true,
        position: 'top'
      }
    },
    scales: {
      x: {
        type: 'time',
        time: {
          unit: 'minute',
          displayFormats: {
            minute: 'HH:mm',
            hour: 'HH:mm',
            day: 'DD-MM'
          }
        },
        title: {
          display: true,
          text: 'Tijd'
        },
        grid: {
          display: true,
          color: 'rgba(0, 0, 0, 0.1)'
        }
      },
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: 'Spanning (V)'
        },
        grid: {
          display: true,
          color: 'rgba(0, 0, 0, 0.1)'
        }
      }
    },
    interaction: {
      intersect: false,
      mode: 'index'
    },
    hover: {
      animationDuration: 300
    },
    animation: {
      duration: 1000,
      easing: 'easeInOutQuart'
    }
  }
});

const ctxStroom = document.getElementById('Zonnepaneelstroom').getContext('2d');
const zonnepaneelstroomChart = new Chart(ctxStroom, {
  type: 'line',
  data: {
    labels: labelsStroom,
    datasets: [{
      label: 'Zonnepaneel Stroom (A)',
      data: stroomData,
      borderColor: 'rgba(54, 162, 235, 1)',
      backgroundColor: 'rgba(54, 162, 235, 0.2)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointRadius: 3,
      pointHoverRadius: 6,
      pointBackgroundColor: 'rgba(54, 162, 235, 1)',
      pointBorderColor: '#fff',
      pointBorderWidth: 2
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      title: {
        display: true,
        text: 'Zonnepaneel Stroom Over Tijd',
        font: {
          size: 16,
          weight: 'bold'
        }
      },
      legend: {
        display: true,
        position: 'top'
      }
    },
    scales: {
      x: {
        type: 'time',
        time: {
          unit: 'minute',
          displayFormats: {
            minute: 'HH:mm',
            hour: 'HH:mm',
            day: 'DD-MM'
          }
        },
        title: {
          display: true,
          text: 'Tijd'
        },
        grid: {
          display: true,
          color: 'rgba(0, 0, 0, 0.1)'
        }
      },
      y: {
        beginAtZero: true,
        title: {
          display: true,
          text: 'Stroom (A)'
        },
        grid: {
          display: true,
          color: 'rgba(0, 0, 0, 0.1)'
        }
      }
    },
    interaction: {
      intersect: false,
      mode: 'index'
    },
    hover: {
      animationDuration: 300
    },
    animation: {
      duration: 1000,
      easing: 'easeInOutQuart'
    }
  }
})

// DEBUG HUIS ID
const huisId = <?php echo json_encode($huis_id); ?>;
console.log('Huis ID:', huisId);
</script>

<style>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.chart-container {
  position: relative;
  height: 400px;
  width: 100%;
  margin: 20px 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

h1 {
  color: #333;
  text-align: center;
  margin-bottom: 30px;
}

@media (max-width: 768px) {
  .chart-container {
    height: 300px;
    padding: 10px;
  }
}
</style>

</body>
</html>