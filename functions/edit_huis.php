<?php
// Haal het 'id' parameter uit de URL op en sla het op in een variabele
$id = isset($_GET['id']) ? $_GET['id'] : null;

echo $id
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="upload_data.php?id=<?php echo $id; ?>">Upload Data</a>
</body>
</html>