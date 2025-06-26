<?php
session_start();

// checkn of de gebruiker is ingelogd en de rol superadmin heeft
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../login.php');
    exit();
}

// db connect
include '../db_conn.php';

//userid ophalen iot url
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header('Location: ../superadmin_dashboard.php?error=invalid_user_id');
    exit();
}

// form verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $huis = !empty($_POST['huis']) ? intval($_POST['huis']) : null;
    $new_password = trim($_POST['new_password']);
    
    // inpout valideren
    if (empty($username) || empty($role)) {
        $error = "Gebruikersnaam en rol zijn verplicht.";
    } else {
        try {
            // chcekcn of de username al bestaat
            $check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$username, $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                $error = "Deze gebruikersnaam bestaat al.";
            } else {
                // updaten
                if (!empty($new_password)) {
                    // Update met nieuw wachtwoord
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET username = ?, role = ?, huis = ?, password = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$username, $role, $huis, $hashed_password, $user_id]);
                } else {
                    // update zonder nieuw wachtwoord
                    $query = "UPDATE users SET username = ?, role = ?, huis = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$username, $role, $huis, $user_id]);
                }
                
                header('Location: ../superadmin_dashboard.php?success=user_updated');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database fout: " . $e->getMessage();
        }
    }
}

// userdata ophalen voor formulier
try {
    $query = "SELECT id, username, role, huis FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ../superadmin_dashboard.php?error=user_not_found');
        exit();
    }
} catch (PDOException $e) {
    header('Location: ../superadmin_dashboard.php?error=database_error');
    exit();
}

// Huis IDs ophalen voor de dropdown
try {
    $houses_query = "SELECT id FROM huizen ORDER BY id";
    $houses_stmt = $conn->prepare($houses_query);
    $houses_stmt->execute();
    $houses = $houses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $houses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruiker Bewerken - Energie Transitie</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="../index.php"><img src="../images/logo.png" alt="Energie logo" /></a>
            <span>Energie Transitie</span>
        </div>
        <div class="account-info">
            <a class="account" href="../account.php">👤</a>  
        </div>
        <nav>
            <a href="../superadmin_dashboard.php">Dashboard</a>
            <a href="../logout.php">Uitloggen</a>
        </nav>
    </header>
    
    <main>
        <div class="form-container">
            <h2>Gebruiker Bewerken</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Gebruikersnaam:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role" required>
                        <option value="klant" <?php echo $user['role'] === 'klant' ? 'selected' : ''; ?>>Klant</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="superadmin" <?php echo $user['role'] === 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="huis">Huis ID:</label>
                    <select id="huis" name="huis">
                        <option value="">Geen huis toegewezen</option>
                        <?php foreach ($houses as $house): ?>
                            <option value="<?php echo $house['id']; ?>" <?php echo $user['huis'] == $house['id'] ? 'selected' : ''; ?>>
                                Huis <?php echo $house['id']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nieuw Wachtwoord:</label>
                    <input type="password" id="new_password" name="new_password">
                    <div class="password-hint">Laat leeg om het huidige wachtwoord te behouden</div>
                </div>
                
                <button type="submit" class="btn">Opslaan</button>
                <a href="../superadmin_dashboard.php" class="btn btn-secondary">Annuleren</a>
            </form>
        </div>
    </main>
</body>
</html>
