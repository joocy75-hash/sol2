<?php include ("../serive/samparka.php");?>
<?php
if(isset($_GET['amount'])){
    $ramt = htmlspecialchars(mysqli_real_escape_string($conn, $_GET['amount']));
} else {
    $ramt = 0;
}
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
?>
<?php
$s_upi = "SELECT maulya FROM deyyamrici WHERE sthiti='1'";
$r_upi = mysqli_query($conn, $s_upi);
if (!$r_upi) {
    die("UPI Query Failed: " . mysqli_error($conn));
}
$f_upi = mysqli_fetch_array($r_upi);
$upi_id = $f_upi['maulya'];
// Fetch the active image from images_usdt table
$active_image_query = mysqli_query($conn, "SELECT * FROM `images_usdt` WHERE status = 1 LIMIT 1");
if (!$active_image_query) {
    die("Image Query Failed: " . mysqli_error($conn));
}
$active_image = mysqli_fetch_array($active_image_query);
// Fallback: If no active image is found, fetch the first available image
if (!$active_image) {
    $fallback_image_query = mysqli_query($conn, "SELECT * FROM `images_usdt` LIMIT 1");
    if (!$fallback_image_query) {
        die("Fallback Image Query Failed: " . mysqli_error($conn));
    }
    $active_image = mysqli_fetch_array($fallback_image_query);
}
?>
<?php
$res = [
    'code' => 405,
    'message' => 'Illegal access!',
];
if (isset($_GET['tyid']) && isset($_GET['amount']) && isset($_GET['uid']) && isset($_GET['sign']) && isset($_GET['urlInfo'])) {
    $userId = $uid;
    $userPhoto = '1';
    $numquery = "SELECT mobile, codechorkamukala FROM shonu_subjects WHERE id = ".$userId;
    $numresult = $conn->query($numquery);
    $numarr = mysqli_fetch_array($numresult);
    $userName = '91'.$numarr['mobile'];
    $nickName = $numarr['codechorkamukala'];
    $creaquery = "SELECT createdate FROM shonu_subjects WHERE id = ".$userId;
    $crearesult = $conn->query($creaquery);
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
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1, user-scalable=0" name="viewport">
    <title>ইউএসডিটি জমা | মাজাউইন</title>
    <link rel="icon" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="assets/js/wepay/jquery-2.2.4.min.js"></script>
    <script src="assets/js/wepay/clipboard.min.js"></script>
    <script src="assets/js/wepay/layer.js"></script>
    <link rel="stylesheet" href="assets/css/wepay/layer.css" id="layuicss-layer">
    <style>
        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        #wraper_all {
            margin: 0 auto;
            position: relative;
            max-width: 750px;
            padding: 1.5rem;
        }
        .header {
            background: radial-gradient(circle at center top, #4caf50, #1b5e20);
            color: white;
            padding: 1.25rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header img {
            width: 60px;
            height: 60px;
        }
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .language-switcher select {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.5rem;
            font-size: 0.95rem;
            color: #1b5e20;
            transition: border-color 0.2s ease;
        }
        .language-switcher select:focus {
            border-color: #4caf50;
            outline: none;
        }
        .amount-section {
            text-align: center;
            margin: 2rem 0;
            background-color: #ffffff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .amount-section .amount {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1b5e20;
        }
        .payment-option {
            background-color: #ffffff;
            border-radius: 8px;
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
            color: #1b5e20;
            word-break: break-all;
            background-color: #e8f5e9;
            padding: 0.75rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background-color: #4caf50;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #388e3c;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .copy-btn:hover {
            background-color: #388e3c;
        }
        .input-container {
            position: relative;
            margin: 1.5rem 0;
        }
        .input-container input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 5rem;
            border: 2px solid #4caf50;
            border-radius: 8px;
            font-size: 1rem;
            color: #1b5e20;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .input-container input:focus {
            border-color: #388e3c;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
        }
        .input-container label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #666;
            transition: color 0.2s ease;
        }
        .instructions {
            background-color: #ffffff;
            border-radius: 8px;
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
            color: #d32f2f;
            font-weight: 600;
        }
        .submit-btn {
            width: 100%;
            background-color: #4caf50;
            color: white;
            padding: 0.875rem;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .submit-btn:hover {
            background-color: #388e3c;
            transform: translateY(-1px);
        }
        .loading2 {
            display: none;
            text-align: center;
            padding: 1.25rem;
            color: #1b5e20;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .weui-panel__hd {
            color: #1b5e20;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.5rem 0;
        }
        .weui-media-box__title {
            color: #1b5e20;
            font-weight: 500;
            margin: 0 0 10px 0;
            text-shadow: 1px 1px 0 #fff;
            background-color: #e8f5e9;
            text-align: center;
            padding: 10px 0;
            letter-spacing: 1px;
            border-radius: 8px;
        }
        .image-grid img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-style: outset;
        }
        .unit {
            font-size: 18px;
            color: #666;
            font-weight: 700;
        }
        .amount-tip {
            margin: 5px 20px 0;
            border-radius: 8px;
            font-size: 10px;
            color: #d32f2f;
            font-weight: bold;
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            padding: 3px 0;
        }
        .order_no {
            font-size: 12px;
            color: #555;
            margin: 15px 0 25px;
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
            .image-grid img {
                width: 100%;
            }
        }
    </style>
</head>
<body data-gr-ext-installed="" data-new-gr-c-s-check-loaded="14.1012.0" ontouchstart="">
<div id="wraper_all">
    <div class="header">
        <img src="../assets/png/usdt-40311708.png" alt="USDT Logo" title="Tether USDT (TRC20)" width="60" height="60">
        <h1 id="header-title">ইউএসডিটি জমা | মাজাউইন</h1>
        <div class="language-switcher ml-auto">
            <select id="language" onchange="switchLanguage()">
                <option value="bn" selected>বাংলা</option>
                <option value="en">English</option>
            </select>
        </div>
    </div>
    <div class="amount-section">
        <p class="text-lg" id="transfer-amount-label">জমার পরিমাণ</p>
        <p class="amount">$<?php echo $ramt; ?> <span class="unit">USDT-TRC20</span></p>
        <p class="amount-tip" id="amount-tip">The amount received will be subject to the actual transfer amount. Not less than $<?php echo $ramt; ?></p>
        <p class="order_no">NO. <?php echo $serial; ?></p>
    </div>
    <div class="payment-option">
        <h2 class="text-lg font-semibold mb-3" id="step1-label">ধাপ ১: পেমেন্ট করুন</h2>
        <p class="mb-3" id="send-amount-label">নিম্নলিখিত ইউএসডিটি ঠিকানায় <span class="font-bold">$<?php echo $ramt; ?></span> পাঠান:</p>
        <div class="image-grid">
    <?php if (!$active_image) { ?>
        <p id="no-images">কোনো ইমেজ পাওয়া যায়নি।</p>
    <?php } else { ?>
        <div>
            <img src="<?php echo '../images_usdt/'.htmlspecialchars($active_image['filename']); ?>" alt="USDT Image" onerror="this.src='../images_usdt/default.jpg';">
        </div>
    <?php } ?>
</div>
        <div class="flex justify-between items-center payment-id">
            <span id="upi"><?php echo htmlspecialchars($upi_id);?></span>
            <button class="copy-btn" id="btncopy">ইউএসডিটি ঠিকানা কপি করুন</button>
        </div>
    </div>
    <div class="payment-option">
        <h2 class="text-lg font-semibold mb-3" id="step2-label">ধাপ ২: ট্রান্সঅ্যাকশন আইডি জমা দিন</h2>
        <div class="input-container">
            <input type="text" id="refno" placeholder="ট্রান্সঅ্যাকশন আইডি লিখুন">
            <label>Ref No:</label>
        </div>
        <p class="text-sm text-gray-600" id="confirmation-label">আপনার জমা ১০ মিনিটের মধ্যে নিশ্চিত হবে।</p>
    </div>
    <div class="instructions">
        <h2 class="text-lg font-semibold mb-3" id="instructions-label">পেমেন্ট নির্দেশাবলী</h2>
        <ol>
            <li id="instruction1">জমার পেমেন্টের জন্য ইউএসডিটি ঠিকানা কপি করুন। তহবিল হারানো এড়াতে শুধুমাত্র একবার ব্যবহার করুন।</li>
            <li id="instruction2">পেমেন্ট ব্যর্থতা এড়াতে জমার পরিমাণ <span class="highlight">$<?php echo $ramt; ?></span> এর সাথে মিলে যায় তা নিশ্চিত করুন।</li>
            <li id="instruction3">পেমেন্ট করতে ইউএসডিটি-টিআরসি২০ সামঞ্জস্যপূর্ণ ওয়ালেট ব্যবহার করুন।</li>
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
var upi = document.getElementById("upi").innerHTML;
var userId = <?php echo $userId; ?>;
var token = '<?php echo $shonusign; ?>';
var translations = {
    bn: {
        'header-title': 'ইউএসডিটি জমা | মাজাউইন',
        'transfer-amount-label': 'জমার পরিমাণ',
        'step1-label': 'ধাপ ১: পেমেন্ট করুন',
        'send-amount-label': 'নিম্নলিখিত ইউএসডিটি ঠিকানায় <span class="font-bold">$<?php echo $ramt; ?></span> পাঠান:',
        'btncopy': 'ইউএসডিটি ঠিকানা কপি করুন',
        'step2-label': 'ধাপ ২: ট্রান্সঅ্যাকশন আইডি জমা দিন',
        'refno-placeholder': 'ট্রান্সঅ্যাকশন আইডি লিখুন',
        'confirmation-label': 'আপনার জমা ১০ মিনিটের মধ্যে নিশ্চিত হবে।',
        'instructions-label': 'পেমেন্ট নির্দেশাবলী',
        'instruction1': 'জমার পেমেন্টের জন্য ইউএসডিটি ঠিকানা কপি করুন। তহবিল হারানো এড়াতে শুধুমাত্র একবার ব্যবহার করুন।',
        'instruction2': 'পেমেন্ট ব্যর্থতা এড়াতে জমার পরিমাণ <span class="highlight">$<?php echo $ramt; ?></span> এর সাথে মিলে যায় তা নিশ্চিত করুন।',
        'instruction3': 'পেমেন্ট করতে ইউএসডিটি-টিআরসি২০ সামঞ্জস্যপূর্ণ ওয়ালেট ব্যবহার করুন।',
        'instruction4': '<span class="highlight">৫ মিনিটের</span> মধ্যে পেমেন্ট সম্পন্ন করুন, অন্যথায় তহবিল <span class="highlight">হারিয়ে যেতে পারে</span>।',
        'instruction5': '২৪ ঘণ্টার মধ্যে পাঁচটি ব্যর্থ পেমেন্ট প্রচেষ্টা আপনার আইডি ২৪ ঘণ্টার জন্য স্থগিত করবে।',
        'instruction6': 'নির্দেশাবলী অনুসরণ না করলে কোনো ক্ষতির জন্য আমরা দায়ী থাকব না।',
        'savebtn': 'ট্রান্সঅ্যাকশন আইডি জমা দিন',
        'loading-label': 'নিশ্চিত হচ্ছে...',
        'copy-success': 'ইউএসডিটি ঠিকানা সফলভাবে কপি হয়েছে',
        'copy-error': 'কপি ব্যর্থ, দয়া করে ম্যানুয়ালি ইনপুট করুন',
        'invalid-ref': 'অবৈধ ট্রান্সঅ্যাকশন আইডি',
        'security-title': 'নিরাপত্তা',
        'confirm-message': '<span style="color:#d32f2f">বিস্তারিত সাবধানে নিশ্চিত করুন</span><br><br>ইউএসডিটি ঠিকানা: <code style="color:#388e3c">' + upi + '</code><br>পরিমাণ: <code style="color:#388e3c">' + ramt + '</code><br>ট্রান্সঅ্যাকশন আইডি: <code style="color:#388e3c">{refNo}</code>',
        'alert-message': 'পেমেন্ট সফল হওয়ার পর, আপনার জমা সক্রিয় করতে এখানে ট্রান্সঅ্যাকশন আইডি জমা দিন।',
        'no-images': 'কোনো ইমেজ পাওয়া যায়নি।',
        'amount-tip': 'প্রকৃত স্থানান্তর পরিমাণের উপর ভিত্তি করে প্রাপ্ত পরিমাণ নির্ধারিত হবে। এর চেয়ে কম নয় $<?php echo $ramt; ?>।'
    },
    en: {
        'header-title': 'USDT Deposit | Janu88',
        'transfer-amount-label': 'Deposit Amount',
        'step1-label': 'Step 1: Make Payment',
        'send-amount-label': 'Send <span class="font-bold">$<?php echo $ramt; ?></span> to the following USDT address:',
        'btncopy': 'Copy USDT Address',
        'step2-label': 'Step 2: Submit Transaction ID',
        'refno-placeholder': 'Enter Transaction ID',
        'confirmation-label': 'Your deposit will be confirmed within 10 minutes.',
        'instructions-label': 'Payment Instructions',
        'instruction1': 'Copy the USDT address for deposit payment. Use it only once to avoid fund loss.',
        'instruction2': 'Ensure the deposited amount matches <span class="highlight">$<?php echo $ramt; ?></span> to avoid payment failure.',
        'instruction3': 'Use a USDT-TRC20 compatible wallet to make the payment.',
        'instruction4': 'Complete the payment within <span class="highlight">5 minutes</span> to avoid potential loss.',
        'instruction5': 'Five failed payment attempts within 24 hours will suspend your ID for 24 hours.',
        'instruction6': 'We are not responsible for losses if guidelines are not followed.',
        'savebtn': 'Submit Transaction ID',
        'loading-label': 'Confirming...',
        'copy-success': 'USDT address copied successfully',
        'copy-error': 'Copy failed, please input manually',
        'invalid-ref': 'Invalid Transaction ID',
        'security-title': 'Security',
        'confirm-message': '<span style="color:#d32f2f">Confirm the details carefully</span><br><br>USDT Address: <code style="color:#388e3c">' + upi + '</code><br>Amount: <code style="color:#388e3c">' + ramt + '</code><br>Transaction ID: <code style="color:#388e3c">{refNo}</code>',
        'alert-message': 'After successful payment, submit the Transaction ID here to activate your deposit.',
        'no-images': 'No images found.',
        'amount-tip': 'The amount received will be subject to the actual transfer amount. Not less than $<?php echo $ramt; ?>.'
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
    document.getElementById("no-images").innerHTML = translations[lang]['no-images'];
    document.getElementById("amount-tip").innerHTML = translations[lang]['amount-tip'];
}
var copyAmount = new ClipboardJS("#copyAmount", {
    text: function() {
        return ramt;
    }
});
copyAmount.on("success", function() {
    var lang = document.getElementById("language").value;
    layer.msg(translations[lang]['copy-success']);
});
copyAmount.on("error", function() {
    var lang = document.getElementById("language").value;
    layer.msg(translations[lang]['copy-error']);
});
var clipboard = new ClipboardJS("#btncopy", {
    text: function() {
        return $("#upi").html();
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
    $('#refno').bind('input propertychange', function() {
        var v = $("#refno").val();
        if (v.length >= 12) {
            $("#savebtn").click();
        }
    });
    $("#savebtn").on("click", function() {
        var refNo = $("#refno").val();
        var lang = document.getElementById("language").value;
        if (refNo.length != 12) {
            layer.msg(translations[lang]['invalid-ref']);
            return false;
        }
        layer.confirm(translations[lang]['confirm-message'].replace('{refNo}', refNo), {
            title: translations[lang]['security-title'],
            btn: ["Confirm", "Cancel"]
        }, function() {
            layer.closeAll();
            adddep(ramt, refNo, serial, upi, userId, token);
        }, function() {});
    });
});
function adddep(amt, refnum, srl, upi, userId, token) {
    $.ajax({
        type: "POST",
        data: "amt=" + amt + "&refnum=" + refnum + "&srl=" + srl + "&source=usdt&upi=" + upi + "&userId=" + userId + "&token=" + token,
        url: "adddeposit.php",
        success: function(html) {
            var arr = html.split('~');
            if (arr[0] == 1) {
                showLoading();
                setTimeout(function() { depconfirm(refnum); }, 1900);
            } else if (arr[0] == 0) {
                alert("Error");
            } else if (arr[0] == 2) {
                alert("Duplicate Transaction ID");
            } else if (arr[0] == 3) {
                alert("Please Wait For 1 Minute");
            } else if (arr[0] == 4) {
                alert("Your deposit option is suspended\nContact Customer Support");
            }
        },
        error: function(e) {}
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
});
</script>
</body>
</html>
<?php
    } else {
        $res['code'] = 10000;
        $res['success'] = 'false';
        $res['message'] = 'Sorry, The system is busy, please try again later!';
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(200);
        echo json_encode($res);
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(200);
    echo json_encode($res);
}
?>