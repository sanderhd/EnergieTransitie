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

    // Redirect op basis van rol
    if ($user['role'] === 'admin') {
      header("Location: admindash.php");
    } elseif ($user['role'] === 'klant') {
      header("Location: dashboard.php");
    } else {
      header("Location: dashboard.php");
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
      <button class="theme-toggle" onclick="document.body.classList.toggle('dark')">🌙 Thema</button>
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
        <h2>Inloggen</h2>
        <?php if ($error): ?>
          <div class="error" style="color:red;">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        <form method="post" action="" onsubmit="return validateForm()">
          <label for="username">Gebruikersnaam:</label>
          <input type="text" id="username" name="username" required>

          <label for="password">Wachtwoord:</label>
          <input type="password" id="password" name="password" required>

          <div id="showPasswordLabel">
              <input type="checkbox" id="showPassword">
              <label for="showPassword">Wachtwoord tonen</label>
          </div>


          <a href="register.php" class="forgot">Wachtwoord vergeten?</a>

          <button type="submit">Inlog Knop</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    document.getElementById('showPassword').addEventListener('change', function () {
      const pw = document.getElementById('password');
      pw.type = this.checked ? 'text' : 'password';
    });

    function validateForm() {
      const user = document.getElementById('username').value.trim();
      const pw = document.getElementById('password').value.trim();
      if (user.length < 3) {
        alert('Gebruikersnaam moet minstens 3 tekens bevatten.');
        return false;
      }
      if (pw.length < 6) {
        alert('Wachtwoord moet minstens 6 tekens bevatten.');
        return false;
      }
      return true;
    }
  </script>
</body>
</html>

