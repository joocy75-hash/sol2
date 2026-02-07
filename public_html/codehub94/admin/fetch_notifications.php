<?php
include("conn.php");

// Pending Deposits
$deposits = mysqli_query($conn, "SELECT shonu, balakedara, motta FROM thevani WHERE sthiti='0' ORDER BY shonu DESC LIMIT 5");
$depositData = [];
while ($row = mysqli_fetch_assoc($deposits)) {
    $depositData[] = $row;
}

// Pending Withdrawals
$withdrawals = mysqli_query($conn, "SELECT shonu, balakedara, motta FROM hintegedukolli WHERE sthiti='0' ORDER BY shonu DESC LIMIT 5");
$withdrawalData = [];
while ($row = mysqli_fetch_assoc($withdrawals)) {
    $withdrawalData[] = $row;
}

// Total Count
$totalCount = count($depositData) + count($withdrawalData);

echo json_encode([
    "count" => $totalCount,
    "deposits" => $depositData,
    "withdrawals" => $withdrawalData
]);
?>
