<?php
session_start();
require_once 'db_conn.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Haal gebruiker op uit database
  $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['password'])) {
    // Inloggen geslaagd
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Haal gekoppeld huis op
    $stmtHuis = $conn->prepare("SELECT id FROM huizen WHERE bewoner1 = ? OR bewoner2 = ?");
    $stmtHuis->execute([$user['id'], $user['id']]);
    $huis = $stmtHuis->fetch(PDO::FETCH_ASSOC);

    if ($huis) {
      $_SESSION['huis_id'] = $huis['id'];
    } else {
      $_SESSION['huis_id'] = null;
    }

    // Redirect op basis van rol
    if ($user['role'] === 'admin') {
      header("Location: admin_dashboard.php");
    } elseif ($user['role'] === 'klant') {
      header("Location: klant_dashboard.php");
    } else {
      header("Location: superadmin_dashboard.php");
    }
    exit;
  } else {
    $error = 'Ongeldige gebruikersnaam of wachtwoord.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Energie Transitie - Inloggen</title>
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

  <main>
  <div class="left">
    <img src="images/logo.png" alt="Zon, windmolens, zonnepanelen">
  </div>
  <div class="right">
    <div class="login-box">
    <h2>Inloggen</h2>
    <?php if ($error): ?>
      <div class="error" style="color:red;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label for="username">Gebruikersnaam:</label>
      <input type="text" id="username" name="username" required>
      <label for="password">Wachtwoord:</label>
      <input type="password" id="password" name="password" required>
      <a href="register.php" class="forgot">Wachtwoord vergeten?</a>
      <button type="submit">Inloggen</button>
    </form>
    </div>
  </div>
  </main>
</body>
</html>