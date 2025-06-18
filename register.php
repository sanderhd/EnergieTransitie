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
            header("Location: admin_dashboard.php");
        } else if ($user['role'] === 'klant') {
            header("Location: klant_dashboard.php");
        } else {
            header("Location: algemene_dashboard.php");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Energie Transitie - Registratie</title>
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="CSS/style.css" />
</head>
<body>
    <div class="register">
        <div class="register-left">
            <img src="images/logo.png" alt="Logo" />
        </div>

        <div class="register-right">
            <h1>Registreren</h1>

            <form method="POST" action="">
                <label for="username">Gebruikersnaam:</label> <br />
                <input type="text" name="username" id="username" placeholder="Gebruikersnaam" required /> <br />

                <label for="password">Wachtwoord:</label> <br />
                <input type="password" name="password" id="password" placeholder="Wachtwoord" required /> <br />

                <button type="submit">Registreren</button>
            </form>
        </div>
    </div>
</body>
</html>
