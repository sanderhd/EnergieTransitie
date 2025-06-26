<?php
session_start();

// Check if user is logged in and has superadmin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link rel="stylesheet" href="CSS/superadmin.css">
    <!-- <link rel="stylesheet" href="CSS/style.css">
    <style>
        .section {
            margin: 20px 0;
        }
        .section h2 {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .add-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
            display: inline-block;
        }
    </style> -->
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="images/logo.png" alt="Energie logo" /></a>
            <span>Energie Transitie</span>
        </div>
        <div class="account-info">
            <a class="account" href="account.php">👤</a>  
        </div>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="superadmin_dashboard.php">Dashboard</a>
                <a href="logout.php">Uitloggen</a>
            <?php else: ?>
                <a href="login.php">Inloggen</a>
                <a href="register.php">Registreren</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <main>
        <!-- Users Section -->
        <div class="section">
            <h2>Gebruikers Beheer</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Gebruikersnaam</th>
                        <th>Rol</th>
                        <th>Huis ID</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Include database connection
                    include 'db_conn.php';
                    
                    // Fetch all users from the database
                    $query = "SELECT id, username, role, huis FROM users ORDER BY id";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['huis'] ?? 'Geen huis') . "</td>";
                        echo "<td>
                                <a href='functions/edit_user.php?id=" . htmlspecialchars($user['id']) . "'>Bewerken</a> |
                                <a href='functions/delete_user.php?id=" . htmlspecialchars($user['id']) . "' onclick=\"return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?');\">Verwijderen</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Houses Section -->
        <div class="section">
            <h2>Huizen Beheer</h2> <a href="functions/create_huis.php">Nieuw Huis Toevoegen</a>
            
            <table>
                <thead>
                    <tr>
                        <th>Huis ID</th>
                        <th>Bewoner 1</th>
                        <th>Bewoner 2</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all houses from the database
                    $query = "SELECT h.id, h.bewoner1, h.bewoner2,
                                     u1.username as bewoner1_naam, u2.username as bewoner2_naam
                              FROM huizen h
                              LEFT JOIN users u1 ON h.bewoner1 = u1.id
                              LEFT JOIN users u2 ON h.bewoner2 = u2.id
                              ORDER BY h.id";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($houses as $house) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($house['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($house['bewoner1_naam'] ?? 'Onbekend') . "</td>";
                        echo "<td>" . htmlspecialchars($house['bewoner2_naam'] ?? 'Onbekend') . "</td>";
                        echo "<td>
                                <a href='functions/edit_huis.php?id=" . htmlspecialchars($house['id']) . "'>Bewerken</a> |
                                <a href='functions/delete_huis.php?id=" . htmlspecialchars($house['id']) . "' onclick=\"return confirm('Weet je zeker dat je dit huis wilt verwijderen?');\">Verwijderen</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>