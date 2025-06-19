<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<<<<<<< Updated upstream
    <h1>Admin Dashboard</h1>
=======
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
    <button id="create_huis" onclick="window.location.href='functions/create_huis.php'">Maak nieuw huis</button>
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
>>>>>>> Stashed changes
</body>
</html>