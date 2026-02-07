<?php
// By @akashkinha Developer


include("../serive/samparka.php");

$uid    = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;

if ($uid < 1 || $amount < 10) {
    die("Invalid request");
} 

$amount = number_format($amount, 2, '.', '');

// Yeh serial hi final ID rahega – DB mein bhi, verify mein bhi
$serial = "JAN" . date("YmdHis") . $uid . rand(10,99);

$createdate = date("Y-m-d H:i:s");

// DB mein pending entry
$conn->query("INSERT INTO thevani 
    (balakedara, motta, dharavahi, mula, ullekha, dinankavannuracisi, madari, pavatiaidi, sthiti)
    VALUES ('$uid', '$amount', '$serial', 'bKash', 'Pending', '$createdate', '1005', '', 'PENDING')");

// RupantorPay API
$apiKey = "lRwWJYi4w2u9ioPaN37h22skt7DaXXcYEtXYOIcri7AXjUIHW2";

$postData = json_encode([
    "fullname"    => "$serial",
    "email"       => "u$uid@janu88.com",
    "amount"      => $amount,
    "success_url" => "https://Sol-0203.com/pay/success.php",
    "cancel_url"  => "https://Sol-0203.com/pay/cancel.php",
    "webhook_url" => "https://Sol-0203.com/pay/webhook.php?i=$serial&type=bkash",
    "meta_data"   => [
        "serial" => $serial,    // ← Yeh hi ID sab jagah use hoga
        "uid"    => $uid
    ]
]);

$ch = curl_init("https://payment.rupantorpay.com/api/payment/checkout");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        "X-API-KEY: $apiKey",
        "Content-Type: application/json",
        "X-CLIENT: janu88.com"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if ($data && $data['status'] == 1 && !empty($data['payment_url'])) {
    header("Location: " . $data['payment_url']);
    exit;
} else {
    echo "Contact Website Support Please - Some Error Occured";
}
?>