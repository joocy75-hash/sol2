<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'conn.php'; // âœ… Database connection

$transactions = [];

// âœ… **Recharge Transactions (Pending Only)**
$recharge_sql = "
    SELECT balakedara AS user_id, 'recharge' AS type, motta AS amount, dharavahi AS order_id, mula AS gateway, duravani AS mobile, dinankavannuracisi AS created_at
    FROM thevani 
    WHERE sthiti = 0
    ORDER BY order_id DESC 
    LIMIT 20";

$recharge_result = mysqli_query($conn, $recharge_sql);
if (!$recharge_result) {
    die(json_encode(["error" => "Recharge Query Failed: " . mysqli_error($conn)]));
}

while ($row = mysqli_fetch_assoc($recharge_result)) {
    $transactions[] = [
        "user_id" => $row["user_id"] ?? "N/A",
        "type" => $row["type"],
        "amount" => $row["amount"] ?? 0,
        "order_id" => $row["order_id"] ?? "N/A",
        "gateway" => $row["gateway"] ?? "N/A",
        "mobile" => $row["mobile"] ?? "N/A",
        "created_at" => $row["created_at"] ?? "Unknown",
        "status" => "Pending"
    ];
}

// âœ… **Withdrawal Transactions (Corrected - Only Pending `sthiti = 0`)**
$withdrawal_sql = "
    SELECT balakedara AS user_id, 'withdrawal' AS type, motta AS amount, dharavahi AS order_id, khateshonu AS bank_account, dharavahi AS mobile, dinankavannuracisi AS created_at
    FROM hintegedukolli 
    WHERE sthiti = 0  -- ðŸ”¥ Applied (Pending Only)
    ORDER BY order_id DESC 
    LIMIT 20";

$withdrawal_result = mysqli_query($conn, $withdrawal_sql);
if (!$withdrawal_result) {
    die(json_encode(["error" => "Withdrawal Query Failed: " . mysqli_error($conn)]));
}

while ($row = mysqli_fetch_assoc($withdrawal_result)) {
    $transactions[] = [
        "user_id" => $row["user_id"] ?? "N/A",
        "type" => $row["type"],
        "amount" => $row["amount"] ?? 0,
        "order_id" => $row["order_id"] ?? "N/A",
        "bank_account" => $row["bank_account"] ?? "N/A",
        "mobile" => $row["mobile"] ?? "N/A",
        "created_at" => $row["created_at"] ?? "Unknown",
        "status" => "Pending"
    ];
}

// âœ… Debugging: If transactions are still empty
if (empty($transactions)) {
    die(json_encode(["error" => "No Pending Transactions Found"]));
}

// âœ… JSON Encode Response
echo json_encode($transactions);
?>
