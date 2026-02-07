<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_url'])) {
    $file_url = $_POST['file_url'];
    $destination = 'public_html/Sol-0203.com/updates/latest_update.zip';

    // Download the update file
    file_put_contents($destination, file_get_contents($file_url));

    // Extract the update file
    $zip = new ZipArchive;
    if ($zip->open($destination) === TRUE) {
        $zip->extractTo('.');
        $zip->close();

        // ✅ Database se update ko "completed" mark karna
        $conn = new mysqli('localhost', 'u209477126_sol0203', 'UP209477126_sol0203', 'u209477126_sol0203');
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }

        // ✅ Update flag reset karna
        $conn->query("DELETE FROM updates"); // Purane updates delete kar do
        $conn->close();

        echo json_encode(['success' => true, 'message' => 'Update applied successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to apply update.']);
    }
}
?>
