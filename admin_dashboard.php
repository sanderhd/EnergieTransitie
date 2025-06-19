<?php
session_start(); 
// Database verbinding importeren
require_once 'db_conn.php';

// Huizen en bewoners ophalen uit de database
$query = "SELECT h.id, u1.username as bewoner1, u2.username as bewoner2 
          FROM huizen h 
          JOIN users u1 ON h.bewoner1 = u1.id 
          JOIN users u2 ON h.bewoner2 = u2.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$huizen = $stmt->fetchAll(PDO::FETCH_ASSOC); // Alle huizen in een array opslaan
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Energie Transitie</title>
    <link rel="stylesheet" href="CSS/admindash.css">
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
                <?php // Voor elk huis een rij aanmaken in de tabel
                foreach ($huizen as $huis): ?>
                <tr>
                    <td><?php echo htmlspecialchars($huis['id']); // Huis ID weergeven ?></td>
                    <td><?php echo htmlspecialchars($huis['bewoner1']); // Eerste bewoner weergeven ?></td>
                    <td><?php echo htmlspecialchars($huis['bewoner2']); // Tweede bewoner weergeven ?></td>
                    <td>
                        <a href="functions/edit_huis.php?id=<?php echo $huis['id']; ?>">Edit</a> | 
                        <a href="functions/delete_huis.php?id=<?php echo $huis['id']; ?>" onclick="return confirm('Weet je zeker dat je dit huis wilt verwijderen?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>