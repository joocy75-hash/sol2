<?php include ("../serive/samparka.php"); ?>

<?php
$amt = isset($_GET['amt']) ? htmlspecialchars($_GET['amt']) : 0;
$refnum = isset($_GET['refnum']) ? htmlspecialchars($_GET['refnum']) : 'N/A';
$srl = isset($_GET['srl']) ? htmlspecialchars($_GET['srl']) : 'N/A';

if (!isset($_GET['userId']) || !isset($_GET['token'])) {
    die(json_encode(['code'=>405,'message'=>'Illegal access!']));
}

$userId = mysqli_real_escape_string($conn, $_GET['userId']);
$token = mysqli_real_escape_string($conn, $_GET['token']);

$numquery = "SELECT mobile, codechorkamukala FROM shonu_subjects WHERE id = '$userId'";
$numresult = $conn->query($numquery);
if (!$numresult || mysqli_num_rows($numresult) == 0) die(json_encode(['code'=>405,'message'=>'User not found']));

$numarr = mysqli_fetch_array($numresult);
$userName = '91'.$numarr['mobile'];
$nickName = $numarr['codechorkamukala'];

$creaquery = "SELECT createdate FROM shonu_subjects WHERE id = '$userId'";
$crearesult = $conn->query($creaquery);
$creaarr = mysqli_fetch_array($crearesult);

$knbdstr = '{"userId":'.$userId.',"userPhoto":"1","userName":'.$userName.',"nickName":"'.$nickName.'","createdate":"'.$creaarr['createdate'].'"}';
$shonusign = strtoupper(hash('sha256', $knbdstr));

if ($shonusign !== $token) {
    die(json_encode(['code'=>10000,'message'=>'Invalid token']));
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ডিপোজিট রিকোয়েস্ট জমা হয়েছে</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        :root {
            --blue: #1e40af;
            --green: #059669;
            --gray: #f8fafc;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Hind Siliguri', sans-serif;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .card {
            background: white;
            width: 100%;
            max-width: 420px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(30, 64, 175, 0.25);
        }
        .header {
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
            color: white;
            padding: 45px 30px;
            text-align: center;
        }
        .icon {
            width: 96px;
            height: 96px;
            background: rgba(255,255,255,0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }
        .icon i { font-size: 48px; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
            70% { box-shadow: 0 0 0 25px rgba(255,255,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
        }
        .header h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .amount {
            font-size: 48px;
            font-weight: 700;
            margin: 15px 0;
            direction: ltr;
        }
        .body {
            padding: 32px;
            text-align: center;
        }
        .success-msg {
            background: #ecfdf5;
            color: var(--green);
            padding: 20px;
            border-radius: 16px;
            border: 1.5px solid #a7f3d0;
            margin: 20px 0;
            font-weight: 600;
            font-size: 17px;
        }
        .info {
            color: #475569;
            line-height: 1.8;
            margin: 20px 0;
            font-size: 16px;
        }
        .details {
            background: var(--gray);
            padding: 22px;
            border-radius: 16px;
            margin: 20px 0;
            text-align: left;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin: 14px 0;
            font-size: 15px;
        }
        .label { color: #64748b; }
        .value { font-weight: 600; color: #1e293b; font-family: monospace; }
        .redirect {
            margin-top: 30px;
            padding: 18px;
            background: var(--blue);
            color: white;
            border-radius: 16px;
            font-weight: 600;
            font-size: 18px;
        }
        .secure {
            margin-top: 25px;
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon">
                <i class="fas fa-check"></i>
            </div>
            <h1>ডিপোজিট রিকোয়েস্ট জমা হয়েছে</h1>
            <div class="amount">৳<?php echo number_format($amt, 2); ?></div>
        </div>

        <div class="body">
            <div class="success-msg">
                আপনার ডিপোজিট যাচাই-বাছাই চলছে
            </div>

            <p class="info">
                সফল যাচাইয়ের পর <strong>মাত্র ১০ মিনিটের</strong> মধ্যে টাকা আপনার ওয়ালেটে যোগ হয়ে যাবে।<br>
                আপনি Recharge History থেকে স্ট্যাটাস চেক করতে পারবেন।
            </p>

            <div class="details">
                <div class="row">
                    <span class="label">UTR / Ref No.</span>
                    <span class="value"><?php echo $refnum; ?></span>
                </div>
                <div class="row">
                    <span class="label">Transaction ID</span>
                    <span class="value"><?php echo $srl; ?></span>
                </div>
                <div class="row">
                    <span class="label">জমা দেওয়ার সময়</span>
                    <span class="value"><?php echo date('d M, Y - h:i A'); ?></span>
                </div>
            </div>

            <div class="redirect">
                ১০ সেকেন্ড পর স্বয়ংক্রিয়ভাবে রিডাইরেক্ট হচ্ছে... <span id="count">10</span>
            </div>

            <div class="secure">
                256-bit SSL এনক্রিপশন • PCI DSS সার্টিফাইড
            </div>
        </div>
    </div>

    <script>
        let sec = 10;
        const counter = document.getElementById('count');
        const timer = setInterval(() => {
            sec--;
            counter.textContent = sec;
            if (sec <= 0) {
                clearInterval(timer);
                window.location.href = "https://Sol-0203.com/#/wallet/RechargeHistory";
            }
        }, 1000);
    </script>
</body>
</html>