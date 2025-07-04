<?php
session_start();
require_once 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $currentUsername = $_SESSION['username'];

    $newUsername = trim($_POST['newename']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // check of e wachtworden t zelfde zijn
    if ($password !== $confirmPassword) {
        echo "<script>alert('Wachtwoorden komen niet overeen.'); window.location.href='account.php';</script>";
        exit();
    }

    // ww hashen
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // nieuwe username controle
    if (!empty($newUsername) && $newUsername !== $currentUsername) {
        // check of die al bestaat
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $checkStmt->execute(['username' => $newUsername, 'id' => $userId]);

        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('Gebruikersnaam bestaat al.'); window.location.href='account.php';</script>";
            exit();
        }

        // update pass en username
        $stmt = $conn->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
        $stmt->execute([
            'username' => $newUsername,
            'password' => $hashedPassword,
            'id' => $userId
        ]);

        $_SESSION['username'] = $newUsername;
    } else {
        // alleen pass updaten
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $userId
        ]);
    }

    echo "<script>alert('Account succesvol bijgewerkt.'); window.location.href='account.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Transitie</title>
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="CSS/account.css">
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
      <section class="form-box">
     <div>
        <h2>Account</h2>
        <p>Hier kunt u uw accountgegevens bewerken.</p>
      </div>
   
      <h2>Accountgegevens</h2>
      <form action="account.php" method="post">
        <div class="form-group">
          <label for="username">Gebruikersnaam:</label>
          <input id="username" type="text" name="username" value="<?php echo $_SESSION['username'];?>" readonly>
        </div>
        <br>
            <div class="form-group">
          <label for="newname">nieuwe gebruikersnaam:</label>
          <input id="newname" type="username" name="newename">
        </div>
        <br>
        <div class="form-group">
          <label for="password">Wachtwoord:</label>
          <input id="password" type="password" name="password" required>
        </div>
        <br>
        <div class="form-group">
          <label for="confirm_password">Bevestig wachtwoord:</label>
          <input id="confirm_password" type="password" name="confirm_password" required>
        </div>
        <br>
        <button type="submit">Opslaan</button>
        <a href="functions/delete_account.php?id=<?php echo $_SESSION['user_id'];?>" onclick="return confirm('Weet je zeker dat je je account wilt verwijderen?')">Verwijder account</a>
      </div>
    </section> 
</form>
    </section>
  </main>
</div>
</body>
</html>
