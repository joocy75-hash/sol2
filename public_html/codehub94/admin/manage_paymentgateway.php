<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit();
}

include "conn.php"; 
include "header.php"; // header include

// ---------- UPDATE LOGIC ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id        = intval($_POST['id']);
    $payName   = mysqli_real_escape_string($conn, $_POST['payName']);
    $miniPrice = intval($_POST['miniPrice']);
    $maxPrice  = intval($_POST['maxPrice']);
    $bonus     = floatval($_POST['bonus']); // rechargeRifts renamed to bonus %

    // Handle Recharge + Gift amounts
    $recharges = array_filter(array_map('trim', explode("\n", $_POST['rechargeAmounts'])));
    $gifts     = array_filter(array_map('trim', explode("\n", $_POST['giftAmounts'])));

    $amountObjects = [];
    $count = max(count($recharges), count($gifts));
    for ($i = 0; $i < $count; $i++) {
        $recharge = isset($recharges[$i]) ? (float)$recharges[$i] : 0;
        $gift     = isset($gifts[$i]) ? (float)$gifts[$i] : 0;
        if ($recharge > 0) {
            $amountObjects[] = [
                "rechargeAmount" => $recharge,
                "giftAmount"     => $gift
            ];
        }
    }
    $quickConfigList = mysqli_real_escape_string($conn, json_encode($amountObjects));

    $sql = "UPDATE tbl_recharge_types SET 
        payName='$payName',
        miniPrice='$miniPrice',
        maxPrice='$maxPrice',
        rechargeRifts='$bonus',
        quickConfigList='$quickConfigList'
        WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        $msg = "Record ID $id updated successfully!";
    } else {
        $msg = "Error: " . mysqli_error($conn);
    }
}

// ---------- FETCH DATA ----------
$result = mysqli_query($conn, "SELECT * FROM tbl_recharge_types ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recharge Types Editor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin:0; background:#f4f6f9; color:#333; }
        .container { max-width:1200px; margin:20px auto; padding:10px; }
        .msg { margin:15px auto; text-align:center; color:green; font-weight:bold; }

        .card {
            background:#fff;
            border-radius:12px;
            padding:20px;
            margin-bottom:20px;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
        }
        .card h3 {
            margin-bottom:15px;
            font-size:20px;
            color:#444;
            display:flex;
            align-items:center;
            gap:8px;
        }

        .form-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap:20px;
        }
        label {
            font-weight:600;
            display:block;
            margin-bottom:5px;
            color:#555;
        }
        input[type="text"], input[type="number"], textarea {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:14px;
        }
        textarea { resize:vertical; min-height:120px; }

        .btn {
            padding:10px 20px;
            background:#28a745;
            color:#fff;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:15px;
            transition:0.3s;
        }
        .btn:hover { background:#218838; }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <?php while($row = mysqli_fetch_assoc($result)) { 
        // prepare recharge + gift values separately
        $recharges = [];
        $gifts = [];
        if (!empty($row['quickConfigList'])) {
            $decoded = json_decode($row['quickConfigList'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $item) {
                    $recharges[] = $item['rechargeAmount'] ?? 0;
                    $gifts[]     = $item['giftAmount'] ?? 0;
                }
            }
        }
        $rechargeText = implode("\n", $recharges);
        $giftText = implode("\n", $gifts);
    ?>
    <div class="card">
        <form method="post">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            
            <div class="form-grid">
                <div>
                    <h3><i class="fa-solid fa-wallet"></i> Pay Name</h3>
                    <input type="text" name="payName" value="<?= $row['payName'] ?>">
                </div>
                <div>
                    <h3><i class="fa-solid fa-arrow-down-short-wide"></i> Min Price</h3>
                    <input type="number" name="miniPrice" value="<?= $row['miniPrice'] ?>">
                </div>
                <div>
                    <h3><i class="fa-solid fa-arrow-up-wide-short"></i> Max Price</h3>
                    <input type="number" name="maxPrice" value="<?= $row['maxPrice'] ?>">
                </div>
                <div>
                    <h3><i class="fa-solid fa-percent"></i> Bonus %</h3>
                    <input type="text" name="bonus" value="<?= $row['rechargeRifts'] ?>">
                </div>
            </div>

            <div class="form-grid" style="margin-top:20px;">
                <div>
                    <h3><i class="fa-solid fa-bolt"></i> Recharge Amounts</h3>
                    <textarea name="rechargeAmounts" placeholder="One per line"><?= htmlspecialchars($rechargeText) ?></textarea>
                </div>
                <div>
                    <h3><i class="fa-solid fa-gift"></i> Gift Amounts</h3>
                    <textarea name="giftAmounts" placeholder="One per line"><?= htmlspecialchars($giftText) ?></textarea>
                </div>
            </div>

            <div style="margin-top:20px; text-align:right;">
                <button type="submit" name="update" class="btn"><i class="fa-solid fa-save"></i> Save</button>
            </div>
        </form>
    </div>
    <?php } ?>
</div>
</body>
</html>
