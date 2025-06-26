<?php
session_start();

// Checken of de user ingelogt is en de role superadmin heeft
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../login.php');
    exit();
}

// database verbinding maken
include '../db_conn.php';

// userid ophalen uit de URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header('Location: ../superadmin_dashboard.php?error=invalid_user_id');
    exit();
}

// Zorgen dat de superadmin zichzelf niet kan verwijderen
if ($user_id == $_SESSION['user_id']) {
    header('Location: ../superadmin_dashboard.php?error=cannot_delete_self');
    exit();
}

// verwerken van het form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // transactie starten
        $conn->beginTransaction();
        
        // eerst de huizen van de gebruiker updaten
        $update_houses_query = "UPDATE huizen SET bewoner1 = NULL WHERE bewoner1 = ?";
        $update_stmt = $conn->prepare($update_houses_query);
        $update_stmt->execute([$user_id]);
        
        $update_houses_query2 = "UPDATE huizen SET bewoner2 = NULL WHERE bewoner2 = ?";
        $update_stmt2 = $conn->prepare($update_houses_query2);
        $update_stmt2->execute([$user_id]);
        
        // dan de gebruiker zelf verwijderen
        $delete_query = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->execute([$user_id]);
        
        // committen 
        $conn->commit();
        
        header('Location: ../superadmin_dashboard.php?success=user_deleted');
        exit();
    } catch (PDOException $e) {
        // rollback bij een fout
        $conn->rollback();
        $error = "Fout bij het verwijderen van de gebruiker: " . $e->getMessage();
    }
}

// userdata ophalen voor bevestiging
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

// chcken of de gebruiker aan huizen is gekoppeld
$associated_houses = [];
try {
    $houses_query = "SELECT id FROM huizen WHERE bewoner1 = ? OR bewoner2 = ?";
    $houses_stmt = $conn->prepare($houses_query);
    $houses_stmt->execute([$user_id, $user_id]);
    $associated_houses = $houses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Fout bij het ophalen van de huizen: " . $e->getMessage();
    $associated_houses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruiker Verwijderen - Energie Transitie</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .user-info h3 {
            margin-top: 0;
            color: #495057;
        }
        .user-info p {
            margin: 5px 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
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
        <div class="confirmation-container">
            <h2>Gebruiker Verwijderen</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="danger">
                <strong>Waarschuwing:</strong> Je staat op het punt een gebruiker permanent te verwijderen. Deze actie kan niet ongedaan worden gemaakt.
            </div>
            
            <div class="user-info">
                <h3>Gebruiker Informatie</h3>
                <p><strong>ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                <p><strong>Gebruikersnaam:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Rol:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                <p><strong>Huis ID:</strong> <?php echo htmlspecialchars($user['huis'] ?? 'Geen huis'); ?></p>
            </div>
            
            <?php if (!empty($associated_houses)): ?>
                <div class="warning">
                    <strong>Let op:</strong> Deze gebruiker is gekoppeld aan <?php echo count($associated_houses); ?> huis(zen). 
                    Bij het verwijderen van deze gebruiker wordt de koppeling met deze huizen automatisch verwijderd:
                    <ul>
                        <?php foreach ($associated_houses as $house): ?>
                            <li>Huis ID: <?php echo htmlspecialchars($house['id']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <p><strong>Weet je zeker dat je gebruiker "<?php echo htmlspecialchars($user['username']); ?>" wilt verwijderen?</strong></p>
                
                <button type="submit" name="confirm_delete" class="btn btn-danger" 
                        onclick="return confirm('Laatste bevestiging: Weet je ZEKER dat je deze gebruiker wilt verwijderen?');">
                    Ja, Verwijder Gebruiker
                </button>
                <a href="../superadmin_dashboard.php" class="btn btn-secondary">Annuleren</a>
            </form>
        </div>
    </main>
</body>
</html>
