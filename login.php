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
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === 'klant') {
            header("Location: klant_dashboard.php");
        } else {
            header("Location: algemene_dashboard.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energie Transitie</title>
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    <div class="login">
        <div class="login-left">
            <img src="images/logo.png" alt="Logo">
        </div>

        <div class="login-right">
            <h1>Login</h1>

            <?php if (!empty($error)): ?>
                <p style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="username">Gebruikersnaam:</label><br>
                <input type="text" name="username" id="username" placeholder="Gebruikersnaam" required><br>

                <label for="password">Wachtwoord:</label><br>
                <input type="password" name="password" id="password" placeholder="Wachtwoord" required><br>

                <a href="#">Wachtwoord vergeten?</a><br>

                <button type="submit">Inloggen</button>
            </form>
        </div>
    </div>
</body>
</html>
