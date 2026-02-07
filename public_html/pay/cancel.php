<?php
// cancel.php → Payment Cancelled Page with Auto Redirect

// ERROR REPORTING ON (har file ka alag error.log)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - janu88.com</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            text-align: center;
            padding: 60px 20px;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .box {
            background: rgba(0,0,0,0.4);
            padding: 50px 40px;
            border-radius: 25px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        }
        h1 {
            font-size: 4em;
            margin: 0 0 20px 0;
            color: #ff4757;
            text-shadow: 0 4px 10px rgba(0,0,0,0.4);
        }
        p {
            font-size: 1.4em;
            margin: 20px 0;
            line-height: 1.6;
        }
        .info {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 15px;
            margin: 25px 0;
            font-size: 1.1em;
        }
        .timer {
            font-size: 1.5em;
            margin: 30px 0;
            font-weight: bold;
        }
        button {
            padding: 16px 40px;
            font-size: 1.3em;
            background: #fff;
            color: #e74c3c;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 15px;
            font-weight: bold;
            transition: all 0.3s;
        }
        button:hover {
            background: #ff4757;
            color: white;
            transform: scale(1.05);
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .icon {
            font-size: 6em;
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">✖</div>
        <h1>Cancelled</h1>
        <p>You have cancelled the payment.</p>
        <p>No amount has been deducted from your account.</p>
        
        <div class="info">
            You can try again or choose another payment method.
        </div>

        <div class="timer">
            Redirecting in <strong>5</strong> seconds...
        </div>

        <button onclick="window.location.href='https://Sol-0203.com/#/wallet/RechargeHistory'">
            Go to Recharge History
        </button>
        <br><br>
        <button onclick="history.back()" style="background:#34495e; color:white;">
            ← Go Back
        </button>
    </div>

    <script>
        let sec = 5;
        const timer = setInterval(() => {
            sec--;
            document.querySelector('.timer strong').textContent = sec;
            if (sec <= 0) {
                clearInterval(timer);
                window.location.href = "https://Sol-0203.com/#/wallet/RechargeHistory";
            }
        }, 1000);

        // Agar user jaldi jaana chahe toh click kar sake
        document.querySelectorAll('button')[0].addEventListener('click', () => clearInterval(timer));
    </script>
</body>
</html>