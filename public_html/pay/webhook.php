<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/webhook.log');
include("../serive/samparka.php");

$log = date('Y-m-d H:i:s') . " → Webhook has been Hitted \n";
$log .= "GET: " . print_r($_REQUEST, true) . "\n";
file_put_contents(__DIR__.'/webhook.log', $log . "---\n", FILE_APPEND);

// Internal transaction ID (jo success page pe aata hai)
$internal_txn = $_REQUEST['transactionId'] ?? '';
if (empty($internal_txn)) die("OK");

// Verify API call – yahan se apna serial wapas milega
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://payment.rupantorPay.com/api/payment/verify-payment",
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode(["transaction_id" => $internal_txn]),
    CURLOPT_HTTPHEADER => [
        "X-API-KEY: lRwWJYi4w2u9ioPaN37h22skt7DaXXcYEtXYOIcri7AXjUIHW2",
        "Content-Type: application/json"
    ],
    CURLOPT_TIMEOUT => 15
]);
$response = curl_exec($ch);
curl_close($ch);

$verify = json_decode($response, true);
file_put_contents(__DIR__.'/webhook.log', "Verify Response: " . print_r($verify, true) . "\n", FILE_APPEND);

if (!$verify || ($verify['status'] ?? '') !== 'COMPLETED') {
    echo "OK"; exit;
}



$amount   = (float)($verify['amount'] ?? 0);
$real_trx = $verify['trx_id'] ?? $internal_txn; 
$fullname = $verify['fullname']; 

 
if (!$fullname || $amount <= 0) {
    echo "OK"; exit;
}

$check = $conn->query("SELECT balakedara FROM thevani WHERE dharavahi = '$fullname' AND sthiti = '1' ");

if ($check->num_rows > 0) {
    file_put_contents(__DIR__.'/webhook.log', "Already credited: $fullname\n", FILE_APPEND);
    echo "OK "; 
    exit;
}


// ReFetch
$check_data = $conn->query("SELECT balakedara FROM thevani WHERE dharavahi = '$fullname'  ");

$row_data = $check_data->fetch_assoc();
$balakendra = $row_data['balakedara'];

// Final Credit
$conn->autocommit(false);
try {
    // First query: Update thevani table
    $stmt = $conn->prepare("UPDATE thevani SET sthiti = '1' WHERE dharavahi = ?");
    $stmt->bind_param("s", $fullname); // 's' is for string
    $stmt->execute();
    
    // Second query: Update shonu_kaichila table
    $stmt2 = $conn->prepare("UPDATE shonu_kaichila SET motta = motta + ? WHERE balakedara = ?");
    $stmt2->bind_param("ii", $amount, $balakendra); // 'i' for integer
    $stmt2->execute();
    
    $conn->commit();
    file_put_contents(__DIR__.'/webhook.log', "SUCCESS → ₹$amount | UID:$balakendra | Serial:$fullname | RealTrx:$real_trx\n", FILE_APPEND);

    
} catch (Exception $e) {
    $conn->rollback();
    file_put_contents(__DIR__.'/webhook.log', "FAILED: " . $e->getMessage() . "\n", FILE_APPEND);
}

$conn->autocommit(true);
echo "OK";
?>