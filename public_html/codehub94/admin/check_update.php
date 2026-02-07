<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'u209477126_sol0203', 'UP209477126_sol0203', 'u209477126_sol0203');
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Fetch the latest update status
$sql = "SELECT version, file_url FROM updates ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $update = $result->fetch_assoc();
    echo json_encode(['update_available' => true, 'version' => $update['version'], 'file_url' => $update['file_url']]);
} else {
    echo json_encode(['update_available' => false]);
}

$conn->close();
?>
