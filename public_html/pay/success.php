<?php
// success.php → 5 second mein auto redirect to wallet history
include("../serive/samparka.php");

// Enable error reporting for this file only
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

$trx     = $_GET['transactionId'] ?? 'N/A';
$amount  = $_GET['paymentAmount'] ?? '0';
$method  = $_GET['paymentMethod'] ?? 'Unknown';
$status  = $_GET['status'] ?? 'COMPLETED';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - janu88.com</title>
    <style>
        body {font-family: 'Segoe UI', Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 50px; margin: 0;}
        .box {background: rgba(0,0,0,0.4); padding: 40px; border-radius: 20px; display: inline-block; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);}
        h1 {font-size: 3.5em; margin: 0; color: #0f6;}
        .amt {font-size: 2.8em; font-weight: bold; margin: 20px 0; color: #ff0;}
        .info {background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin: 15px 0;}
        .loader {margin: 30px 0;}
        .loader span {font-size: 1.5em; animation: blink 1.5s infinite;}
        @keyframes blink {0%,100%{opacity:0.3} 50%{opacity:1}}
        button {padding: 15px 30px; font-size: 1.3em; background: #fff; color: #667eea; border: none; border-radius: 50px; cursor: pointer; margin-top: 20px;}
        button:hover {background: #0f6; color: white;}
    </style>
</head>
<body>
    <div class="box">
        <h1>Success</h1>
        <div class="amt">₹<?php echo $amount; ?></div>
        <div class="info">
            <p><strong>Transaction ID:</strong> <?php echo $trx; ?></p>
            <p><strong>Method:</strong> <?php echo $method; ?></p>
        </div>
        <div class="loader">
            <span>Redirecting to Recharge History in <strong>5</strong> seconds...</span>
        </div>
        <button onclick="window.location.href='https://Sol-0203.com/#/wallet/RechargeHistory'">Go Now</button>
    </div>

    <script>
        let seconds = 5;
        const timer = setInterval(() => {
            seconds--;
            document.querySelector('.loader strong').textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = "https://Sol-0203.com/#/wallet/RechargeHistory";
            }
        }, 1000);
    </script>
</body>
</html>