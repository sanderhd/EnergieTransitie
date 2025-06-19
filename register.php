<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db_conn.php';

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check of gebruiker al bestaat
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() === 0) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, huis, role) VALUES (?, ?, 0, 'klant')");
        $stmt->execute([$username, $hashedPassword]);
    }

    // Haal gegevens van gebruiker op
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect op basis van rol
        if ($user['role'] === 'admin') {
            header("Location: admindash.php");
        } else if ($user['role'] === 'klant') {
            header("Location: dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Energie Transitie - Registreren</title>
  <link rel="stylesheet" href="CSS/login.css">
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
<button class="theme-toggle" onclick="document.body.classList.toggle('dark')">🌙 Thema</button>
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