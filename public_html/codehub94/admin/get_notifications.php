<?php
include("conn.php");
date_default_timezone_set('Asia/Kolkata');

$type = $_GET['type'] ?? 'success';

$notifications = [];

if ($type === 'success') {
    // System alerts (example: approved deposits)
    $sql = "SELECT CONCAT('User ', balakedara, ' deposited ৳', motta) as message 
            FROM thevani WHERE sthiti='1' ORDER BY dinankavannuracisi DESC LIMIT 5";
} else {
    // Customer support = pending deposit requests
    $sql = "SELECT CONCAT('Pending deposit from ', balakedara, ' - ৳', motta, ' [Ref: ', ullekha, ']') as message 
            FROM thevani WHERE sthiti='0' ORDER BY dinankavannuracisi DESC LIMIT 5";
}

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row['message'];
}

header('Content-Type: application/json');
echo json_encode($notifications);
