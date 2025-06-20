<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// // Database configuration
// $host = 'localhost';
// $dbname = 'EnergieTransitie';
// $username = 'root';
// $password = '';

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     http_response_code(500);
//     echo json_encode(['error' => 'Database connection failed']);
//     exit;
// }

require_once 'db_conn.php'; 

// Get the endpoint from URL
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$endpoint = basename($path);

switch ($endpoint) {
    case 'users':
        getUsers($conn);
        break;
    case 'huizen':
        getHuizen($conn);
        break;
    case 'energietransitie_data':
        getEnergieData($conn);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function getUsers($conn) {
    try {
        $stmt = $conn->query("SELECT id, username, role, huis FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch users']);
    }
}

function getHuizen($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM huizen");
        $huizen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($huizen);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch huizen']);
    }
}

function getEnergieData($conn) {
    try {
        $stmt = $conn->query("SELECT * FROM energietransitie_data ORDER BY Tijdstip DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch energie data']);
    }
}
?>