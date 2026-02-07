<?php
// Enable error reporting and logging
ini_set('display_errors', 1); // Set to 0 in production
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

include ("../serive/samparka.php");

// Log request for debugging
error_log("Nagad Payment Page Accessed: " . date('Y-m-d H:i:s'));
error_log("GET Parameters: " . print_r($_GET, true));

if(isset($_GET['amount'])){
    $ramt = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
} else {
    $ramt = 0;
}

// Amount formatting logic
$dot_pos = strpos($ramt, '.');
if ($dot_pos === false) {
    $ramt = $ramt . '.00';
} else {
    $after_dot = substr($ramt, $dot_pos + 1);
    $after_dot_length = strlen($after_dot);
    if ($after_dot_length > 2) {
        $after_dot = substr($after_dot, 0, 2);
        $ramt = substr($ramt, 0, $dot_pos + 1) . $after_dot;
    } elseif ($after_dot_length < 2) {
        $zeros_to_add = 2 - $after_dot_length;
        $ramt = $ramt . str_repeat('0', $zeros_to_add);
    }
}

$date = date("Ymd");
$time = time();
$serial = 'P' . $date . $time . rand(1000,9999);
$tyid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['tyid']));
$uid = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['uid']));
$sign = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['sign']));
$urlInfo = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['urlInfo']));

// ========== NEW NAGAD_PAY LOGIC - 100% WORKING ==========
try {
    $s_payment = "SELECT upi_id FROM nagad_pay WHERE status = '1' ORDER BY RAND() LIMIT 1";
    $r_payment = mysqli_query($conn, $s_payment);
    
    if(!$r_payment) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }

    if(mysqli_num_rows($r_payment) > 0) {
        $f_payment = mysqli_fetch_array($r_payment);
        $payment_id = $f_payment['upi_id'];
    } else {
        // Emergency backup (apna personal number daal do)
        $payment_id = "018XXXXXXXX"; 
        error_log("No active Nagad accounts found, using backup: " . $payment_id);
        // Ya error dikha sakte ho:
        // die("<h1 style='text-align:center;color:red;margin:100px'>Temporary maintenance<br>Please try again later</h1>");
    }
} catch (Exception $e) {
    error_log("Nagad payment ID fetch error: " . $e->getMessage());
    $payment_id = "018XXXXXXXX"; // Fallback value
}
// =====================================================

$res = [
    'code' => 405,
    'message' => 'Illegal access!',
];

if (isset($_GET['tyid']) && isset($_GET['amount']) && isset($_GET['uid']) && isset($_GET['sign']) && isset($_GET['urlInfo'])) {
    $userId = $uid;
    $userPhoto = '1';
    
    try {
        $numquery = "SELECT mobile, codechorkamukala FROM shonu_subjects WHERE id = ".$userId;
        $numresult = $conn->query($numquery);
        if (!$numresult) {
            throw new Exception("User query failed: " . $conn->error);
        }
        $numarr = mysqli_fetch_array($numresult);
        $userName = '91'.$numarr['mobile'];
        $nickName = $numarr['codechorkamukala'];
        
        $creaquery = "SELECT createdate FROM shonu_subjects WHERE id = ".$userId;
        $crearesult = $conn->query($creaquery);
        if (!$crearesult) {
            throw new Exception("Creation date query failed: " . $conn->error);
        }
        $creaarr = mysqli_fetch_array($crearesult);
        $knbdstr = '{"userId":'.$userId.',"userPhoto":"'.$userPhoto.'","userName":'.$userName.',"nickName":"'.$nickName.'","createdate":"'.$creaarr['createdate'].'"}';
        $shonusign = strtoupper(hash('sha256', $knbdstr));
        
        $urlarr = explode (",", $urlInfo);
        $theirurl = $urlarr[0];
        $myurl = 'https://Sol-0203.com';
        
        if($shonusign == $sign && $theirurl == $myurl){
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <title>Nagad | Janu88</title>
    <link rel="icon" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="assets/js/wepay/jquery-2.2.4.min.js"></script>
    <script src="assets/js/wepay/clipboard.min.js"></script>
    <script src="assets/js/wepay/layer.js"></script>
    <link rel="stylesheet" href="assets/css/wepay/layer.css" id="layuicss-layer">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f9fafb;
            line-height: 1.6;
        }
        #wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        .header {
            background: linear-gradient(to right, #db2777, #e879f9);
            color: white;
            padding: 1.25rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header img {
            height: 2.5rem;
            width: auto;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .language-switcher select {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem;
            font-size: 0.95rem;
            color: #1f2937;
            transition: border-color 0.2s ease;
        }
        .language-switcher select:focus {
            border-color: #db2777;
            outline: none;
        }
        .amount-section {
            text-align: center;
            margin: 2rem 0;
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .amount-section .amount {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        .payment-option {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .payment-option:hover {
            transform: translateY(-2px);
        }
        .payment-id {
            font-size: 1rem;
            color: #1f2937;
            word-break: break-all;
            background-color: #f3f4f6;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background-color: #db2777;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .copy-btn:hover {
            background-color: #be185d;
        }
        .input-container {
            position: relative;
            margin: 1.5rem 0;
        }
        .input-container input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            color: #1f2937;
            background-color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .input-container input:focus {
            border-color: #db2777;
            box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.1);
            outline: none;
        }
        .input-container label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #6b7280;
            transition: color 0.2s ease;
        }
        .instructions {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .instructions ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
        }
        .instructions li {
            margin-bottom: 0.75rem;
            color: #374151;
            font-size: 0.95rem;
        }
        .highlight {
            color: #dc2626;
            font-weight: 600;
        }
        .submit-btn {
            width: 100%;
            background-color: #db2777;
            color: white;
            padding: 0.875rem;
            border-radius: 0.5rem;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .submit-btn:hover {
            background-color: #be185d;
            transform: translateY(-1px);
        }
        .loading2 {
            display: none;
            text-align: center;
            padding: 1.25rem;
            color: #db2777;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .input-hint {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        .digit-counter {
            font-size: 0.75rem;
            color: #9ca3af;
            text-align: right;
            margin-top: 0.25rem;
        }
        @media (max-width: 640px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .header h1 {
                font-size: 1.25rem;
            }
            .amount-section .amount {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <div class="header">
        <img src="https://Sol-0203.com/pay/assets/img/nagad.png" alt="Nagad Logo">
        <h1 id="header-title">নগদ | মাজাউইন</h1>
        <div class="language-switcher ml-auto">
            <select id="language" onchange="switchLanguage()">
                <option value="bn" selected>বাংলা</option>
                <option value="en">English</option>
            </select>
        </div>
    </div>
    <div class="amount-section">
        <p class="text-lg" id="transfer-amount-label">ডিপোজিটের পরিমাণ</p>
        <p class="amount">৳<?php echo $ramt; ?></p>
    </div>
    <div class="payment-option">
        <h2 class="text-lg font-semibold mb-3" id="step1-label">ধাপ ১: পেমেন্ট করুন</h2>
        <p class="mb-3" id="send-amount-label">নিম্নলিখিত নগদ আইডিতে <span class="font-bold">৳<?php echo $ramt; ?></span> পাঠান:</p>
        <div class="flex justify-between items-center payment-id">
            <span id="payment_id"><?php echo $payment_id;?></span>
            <button class="copy-btn" id="btncopy">কপি আইডি</button>
        </div>
    </div>
    <div class="payment-option">
        <h2 class="text-lg font-semibold mb-3" id="step2-label">ধাপ ২: ট্রান্সঅ্যাকশন আইডি জমা দিন</h2>
        <div class="input-container">
            <input type="text" id="refno" maxlength="8" placeholder="Transaction ID">
            <label>TrxID:</label>
        </div>
        <div class="digit-counter" id="digit-counter">
            <span id="current-digits">0</span> digits entered
        </div>
        <p class="input-hint" id="trxid-hint">যেকোনো সংখ্যার ট্রান্সঅ্যাকশন আইডি লিখুন (৮ সংখ্যা হলে অটো সাবমিট)</p>
        <p class="text-sm text-gray-600 mt-2" id="confirmation-label">আপনার পেমেন্ট ১০ মিনিটের মধ্যে নিশ্চিত হবে।</p>
    </div>
    <div class="instructions">
        <h2 class="text-lg font-semibold mb-3" id="instructions-label">পেমেন্ট নির্দেশাবলী</h2>
        <ol>
            <li id="instruction1">ডিপোজিট পেমেন্টের জন্য নগদ আইডি কপি করুন। তহবিল হারানো এড়াতে শুধুমাত্র একবার ব্যবহার করুন।</li>
            <li id="instruction2">পেমেন্ট ব্যর্থতা এড়াতে জমার পরিমাণ <span class="highlight">৳<?php echo $ramt; ?></span> এর সাথে মিলে যায় তা নিশ্চিত করুন।</li>
            <li id="instruction3">পেমেন্ট করতে নগদ অ্যাপ ব্যবহার করুন।</li>
            <li id="instruction4"><span class="highlight">৫ মিনিটের</span> মধ্যে পেমেন্ট সম্পন্ন করুন, অন্যথায় তহবিল <span class="highlight">হারিয়ে যেতে পারে</span>।</li>
            <li id="instruction5">২৪ ঘণ্টার মধ্যে পাঁচটি ব্যর্থ পেমেন্ট প্রচেষ্টা আপনার আইডি ২৪ ঘণ্টার জন্য স্থগিত করবে।</li>
            <li id="instruction6">নির্দেশাবলী অনুসরণ না করলে কোনো ক্ষতির জন্য আমরা দায়ী থাকব না।</li>
        </ol>
    </div>
    <button class="submit-btn" id="savebtn">ট্রান্সঅ্যাকশন আইডি জমা দিন</button>
    <div class="loading2" data-text="Confirming, please wait" id="loading-label">নিশ্চিত হচ্ছে...</div>
</div>
<script>
var ramt = <?php echo $ramt; ?>;
var serial = '<?php echo $serial; ?>';
var payment_id = document.getElementById("payment_id").innerHTML;
var userId = <?php echo $userId; ?>;
var token = '<?php echo $shonusign; ?>';
var translations = {
    bn: {
        'header-title': 'নগদ | মাজাউইন',
        'transfer-amount-label': 'ডিপোজিটের পরিমাণ',
        'step1-label': 'ধাপ ১: পেমেন্ট করুন',
        'send-amount-label': 'নিম্নলিখিত নগদ আইডিতে <span class="font-bold">৳<?php echo $ramt; ?></span> পাঠান:',
        'btncopy': 'কপি আইডি',
        'step2-label': 'ধাপ ২: ট্রান্সঅ্যাকশন আইডি জমা দিন',
        'refno-placeholder': 'Transaction ID',
        'trxid-hint': 'যেকোনো সংখ্যার ট্রান্সঅ্যাকশন আইডি লিখুন (৮ সংখ্যা হলে অটো সাবমিট)',
        'confirmation-label': 'আপনার পেমেন্ট ১০ মিনিটের মধ্যে নিশ্চিত হবে।',
        'instructions-label': 'পেমেন্ট নির্দেশাবলী',
        'instruction1': 'ডিপোজিট পেমেন্টের জন্য নগদ আইডি কপি করুন। তহবিল হারানো এড়াতে শুধুমাত্র একবার ব্যবহার করুন।',
        'instruction2': 'পেমেন্ট ব্যর্থতা এড়াতে জমার পরিমাণ <span class="highlight">৳<?php echo $ramt; ?></span> এর সাথে মিলে যায় তা নিশ্চিত করুন।',
        'instruction3': 'পেমেন্ট করতে নগদ অ্যাপ ব্যবহার করুন।',
        'instruction4': '<span class="highlight">৫ মিনিটের</span> মধ্যে পেমেন্ট সম্পন্ন করুন, অন্যথায় তহবিল <span class="highlight">হারিয়ে যেতে পারে</span>।',
        'instruction5': '২৪ ঘণ্টার মধ্যে পাঁচটি ব্যর্থ পেমেন্ট প্রচেষ্টা আপনার আইডি ২৪ ঘণ্টার জন্য স্থগিত করবে।',
        'instruction6': 'নির্দেশাবলী অনুসরণ না করলে কোনো ক্ষতির জন্য আমরা দায়ী থাকব না।',
        'savebtn': 'ট্রান্সঅ্যাকশন আইডি জমা দিন',
        'loading-label': 'নিশ্চিত হচ্ছে...',
        'copy-success': 'পেমেন্ট আইডি সফলভাবে কপি হয়েছে',
        'copy-error': 'কপি ব্যর্থ, দয়া করে ম্যানুয়ালি ইনপুট করুন',
        'invalid-ref': 'অবৈধ ট্রান্সঅ্যাকশন আইডি (ন্যূনতম ১ সংখ্যা প্রয়োজন)',
        'security-title': 'নিরাপত্তা',
        'confirm-message': '<span style="color:#f80">বিস্তারিত সাবধানে নিশ্চিত করুন</span><br><br>পেমেন্ট আইডি: <code style="color:#db2777">' + payment_id + '</code><br>পরিমাণ: <code style="color:#db2777">' + ramt + '</code><br>ট্রান্সঅ্যাকশন আইডি: <code style="color:#db2777">{refNo}</code>',
        'alert-message': 'পেমেন্ট সফল হওয়ার পর, আপনার ডিপোজিট সক্রিয় করতে এখানে ট্রান্সঅ্যাকশন আইডি জমা দিন। ৮ সংখ্যা লিখলে অটোমেটিক সাবমিট হবে।',
        'digit-counter': 'সংখ্যা প্রবেশ করানো হয়েছে',
        'auto-submit': '৮ সংখ্যা শনাক্ত করা হয়েছে, অটো সাবমিট হচ্ছে...'
    },
    en: {
        'header-title': 'Nagad | Janu88',
        'transfer-amount-label': 'Deposit Amount',
        'step1-label': 'Step 1: Make Payment',
        'send-amount-label': 'Send <span class="font-bold">৳<?php echo $ramt; ?></span> to the following Nagad ID:',
        'btncopy': 'Copy ID',
        'step2-label': 'Step 2: Submit Transaction ID',
        'refno-placeholder': 'Transaction ID',
        'trxid-hint': 'Enter any digit Transaction ID (auto-submits at 8 digits)',
        'confirmation-label': 'Your payment will be confirmed within 10 minutes.',
        'instructions-label': 'Payment Instructions',
        'instruction1': 'Copy the Nagad ID for deposit payment. Use it only once to avoid fund loss.',
        'instruction2': 'Ensure the deposited amount matches <span class="highlight">৳<?php echo $ramt; ?></span> to avoid payment failure.',
        'instruction3': 'Use Nagad app to make the payment.',
        'instruction4': 'Complete the payment within <span class="highlight">5 minutes</span> to avoid potential loss.',
        'instruction5': 'Five failed payment attempts within 24 hours will suspend your ID for 24 hours.',
        'instruction6': 'We are not responsible for losses if guidelines are not followed.',
        'savebtn': 'Submit Transaction ID',
        'loading-label': 'Confirming...',
        'copy-success': 'Payment ID copied successfully',
        'copy-error': 'Copy failed, please input manually',
        'invalid-ref': 'Invalid Transaction ID (minimum 1 digit required)',
        'security-title': 'Security',
        'confirm-message': '<span style="color:#f80">Confirm the details carefully</span><br><br>Payment ID: <code style="color:#db2777">' + payment_id + '</code><br>Amount: <code style="color:#db2777">' + ramt + '</code><br>Transaction ID: <code style="color:#db2777">{refNo}</code>',
        'alert-message': 'After successful payment, submit the Transaction ID here to activate your deposit. Will auto-submit at 8 digits.',
        'digit-counter': 'digits entered',
        'auto-submit': '8 digits detected, auto-submitting...'
    }
};

function switchLanguage() {
    var lang = document.getElementById("language").value;
    document.documentElement.lang = lang;
    document.getElementById("header-title").innerHTML = translations[lang]['header-title'];
    document.getElementById("transfer-amount-label").innerHTML = translations[lang]['transfer-amount-label'];
    document.getElementById("step1-label").innerHTML = translations[lang]['step1-label'];
    document.getElementById("send-amount-label").innerHTML = translations[lang]['send-amount-label'];
    document.getElementById("btncopy").innerHTML = translations[lang]['btncopy'];
    document.getElementById("step2-label").innerHTML = translations[lang]['step2-label'];
    document.getElementById("refno").placeholder = translations[lang]['refno-placeholder'];
    document.getElementById("trxid-hint").innerHTML = translations[lang]['trxid-hint'];
    document.getElementById("confirmation-label").innerHTML = translations[lang]['confirmation-label'];
    document.getElementById("instructions-label").innerHTML = translations[lang]['instructions-label'];
    document.getElementById("instruction1").innerHTML = translations[lang]['instruction1'];
    document.getElementById("instruction2").innerHTML = translations[lang]['instruction2'];
    document.getElementById("instruction3").innerHTML = translations[lang]['instruction3'];
    document.getElementById("instruction4").innerHTML = translations[lang]['instruction4'];
    document.getElementById("instruction5").innerHTML = translations[lang]['instruction5'];
    document.getElementById("instruction6").innerHTML = translations[lang]['instruction6'];
    document.getElementById("savebtn").innerHTML = translations[lang]['savebtn'];
    document.getElementById("loading-label").innerHTML = translations[lang]['loading-label'];
    document.getElementById("digit-counter").innerHTML = '<span id="current-digits">0</span> ' + translations[lang]['digit-counter'];
}

function updateDigitCounter() {
    var refNo = $("#refno").val();
    var digitCount = refNo.replace(/\D/g, '').length;
    $("#current-digits").text(digitCount);
    
    // Change color based on digit count
    if (digitCount >= 8) {
        $("#digit-counter").css('color', '#10b981'); // Green
    } else if (digitCount >= 1) {
        $("#digit-counter").css('color', '#f59e0b'); // Yellow
    } else {
        $("#digit-counter").css('color', '#9ca3af'); // Gray
    }
}

var clipboard = new ClipboardJS("#btncopy", {
    text: function() {
        return $("#payment_id").html();
    }
});

clipboard.on("success", function() {
    var lang = document.getElementById("language").value;
    layer.msg(translations[lang]['copy-success']);
});

clipboard.on("error", function() {
    var lang = document.getElementById("language").value;
    layer.msg(translations[lang]['copy-error']);
});

$(function() {
    // Update digit counter on input
    $('#refno').on('input propertychange', function() {
        var v = $("#refno").val();
        updateDigitCounter();
        
        // Auto-submit when 8 digits are entered (Nagad specific)
        var digitCount = v.replace(/\D/g, '').length;
        if (digitCount >= 8) {
            var lang = document.getElementById("language").value;
            layer.msg(translations[lang]['auto-submit'], {time: 1000});
            setTimeout(function() {
                $("#savebtn").click();
            }, 500);
        }
    });
    
    $("#savebtn").on("click", function() {
        var refNo = $("#refno").val();
        var lang = document.getElementById("language").value;
        
        // Updated validation - accept any digit transaction ID (minimum 1 digit)
        var digitCount = refNo.replace(/\D/g, '').length;
        if (digitCount < 1) {
            layer.msg(translations[lang]['invalid-ref']);
            return false;
        }
        
        layer.confirm(
            translations[lang]['confirm-message'].replace('{refNo}', refNo), 
            {
                title: translations[lang]['security-title'],
                btn: ["Confirm", "Cancel"]
            }, 
            function() {
                layer.closeAll();
                adddep(ramt, refNo, serial, payment_id, userId, token);
            }, 
            function() {}
        );
    });
});

function adddep(amt, refnum, srl, payment_id, userId, token) {
    $.ajax({
        type: "POST",
        data: "amt=" + amt + "&refnum=" + refnum + "&srl=" + srl + "&source=Nagad&payment_id=" + payment_id + "&userId=" + userId + "&token=" + token,
        url: "adddeposit.php",
        success: function(html) {
            try {
                var arr = html.split('~');
                if (arr[0] == 1) {
                    showLoading();
                    setTimeout(function() { 
                        depconfirm(refnum); 
                    }, 1900);
                } else if (arr[0] == 0) {
                    alert("Error processing payment");
                } else if (arr[0] == 2) {
                    alert("Duplicate Transaction ID");
                } else if (arr[0] == 3) {
                    alert("Please Wait For 1 Minute");
                } else if (arr[0] == 4) {
                    alert("Your recharge option is suspended\nContact Customer Support");
                } else {
                    alert("Unexpected response: " + html);
                }
            } catch (e) {
                console.error("Error processing response:", e);
                alert("Error processing payment response");
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("Network error. Please try again.");
        }
    });
}

function depconfirm(refnum) {
    window.location.href = 'depositconfirm.php?amt=' + ramt + '&refnum=' + refnum + '&srl=' + serial + "&userId=" + userId + "&token=" + token;
}

function showLoading() {
    $(".loading2").show();
}

function closeLoading() {
    $(".loading2").hide();
}

$(document).ready(function() {
    var lang = document.getElementById("language").value;
    layer.alert(translations[lang]['alert-message'], {
        title: translations[lang]['security-title'],
        icon: 0,
        btn: ["OK"]
    });
    
    // Initialize digit counter
    updateDigitCounter();
});
</script>
</body>
</html>
<?php
        } else {
            error_log("Security validation failed: Signature or URL mismatch");
            $res['code'] = 10000;
            $res['success'] = 'false';
            $res['message'] = 'Sorry, The system is busy, please try again later!';
            header('Content-Type: text/html; charset=utf-8');
            http_response_code(200);
            echo json_encode($res);
        }
    } catch (Exception $e) {
        error_log("User data processing error: " . $e->getMessage());
        $res['code'] = 500;
        $res['message'] = 'Internal server error';
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
    }
} else {
    error_log("Missing required parameters");
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode($res);
}
?>