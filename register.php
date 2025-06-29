<?php
session_start();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'db_conn.php';

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Check of gebruiker al bestaat
  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->execute([$username]);

  if ($stmt->rowCount() > 0) {
    $error_message = 'Deze gebruikersnaam bestaat al. Kies een andere gebruikersnaam.';
  } else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, huis, role) VALUES (?, ?, 0, 'klant')");
    $stmt->execute([$username, $hashedPassword]);

    // Haal gegevens van nieuwe gebruiker op
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      // Redirect op basis van rol
      if ($user['role'] === 'admin') {
        header("Location: admin_dashboard.php");
      } else {
        header("Location: klant_dashboard.php");
      }
      exit;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Energie Transitie</title>
  <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
  <link rel="stylesheet" href="CSS/login.css">
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

<?php if (isset($error_message)) echo $error_message; ?>

  <main>
    <div class="left">
      <img src="images/logo.png" alt="Zon, windmolens, zonnepanelen">
    </div>
    <div class="right">
      <div class="login-box">
        <h2>Registreren</h2>
        <form method="post" action="register.php">
          <label for="username">Gebruikersnaam:</label>
          <input type="text" id="username" name="username" required>
          <label for="password">Wachtwoord:</label>
          <input type="password" id="password" name="password" required>
          <a href="login.php" class="forgot">Heb je al account?</a>
          <button type="submit">Registreren</button>
        </form>
      </div>
    </div>
  </main>
</body>
</html>