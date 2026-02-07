<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$agent_id = $_GET['agent_id'] ?? '';
$error = '';

$member_count = 0;
$recharge_summary = [
    'pending' => ['amount' => 0, 'count' => 0, 'people' => 0],
    'failed' => ['amount' => 0, 'count' => 0, 'people' => 0],
    'success' => ['amount' => 0, 'count' => 0, 'people' => 0]
];

$deposit_summary = [
    'first' => ['people' => 0, 'amount' => 0],
    'second' => ['people' => 0, 'amount' => 0],
    'third' => ['people' => 0, 'amount' => 0],
    'first_withdrawal' => ['people' => 0, 'amount' => 0],
    'direct_first' => ['people' => 0, 'amount' => 0],
    'direct_second' => ['people' => 0, 'amount' => 0]
];

if ($agent_id) {
    $check_agent = mysqli_query($conn, "SELECT * FROM tb_agent WHERE userid='$agent_id' AND teacherid='$teacher_id'");
    if (mysqli_num_rows($check_agent)) {
        $codes = [$agent_id];
        $result = mysqli_query($conn, "SELECT id FROM shonu_subjects 
            WHERE code='$agent_id' OR code1='$agent_id' OR code2='$agent_id' 
            OR code3='$agent_id' OR code4='$agent_id' OR code5='$agent_id'");
        while ($row = mysqli_fetch_assoc($result)) {
            $codes[] = $row['id'];
        }
        $member_count = count($codes);

        $code_list = implode("','", $codes);

        // 🔶 Recharge Summary
        $query = mysqli_query($conn, "SELECT sthiti, SUM(motta) AS total_amount, COUNT(*) AS total_count, COUNT(DISTINCT balakedara) AS people 
                                      FROM thevani WHERE balakedara IN ('$code_list') GROUP BY sthiti");
        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['sthiti'] == 0) $recharge_summary['pending'] = ['amount' => $row['total_amount'], 'count' => $row['total_count'], 'people' => $row['people']];
            elseif ($row['sthiti'] == 2) $recharge_summary['failed'] = ['amount' => $row['total_amount'], 'count' => $row['total_count'], 'people' => $row['people']];
            elseif ($row['sthiti'] == 1) $recharge_summary['success'] = ['amount' => $row['total_amount'], 'count' => $row['total_count'], 'people' => $row['people']];
        }

        // 🔶 First, Second, Third Recharge
        $recharge_q = mysqli_query($conn, "SELECT balakedara, COUNT(*) AS cnt, SUM(motta) AS amt 
                                           FROM thevani WHERE balakedara IN ('$code_list') AND sthiti=1 
                                           GROUP BY balakedara");
        while ($r = mysqli_fetch_assoc($recharge_q)) {
            $cnt = $r['cnt'];
            if ($cnt >= 1) $deposit_summary['first']['people']++;
            if ($cnt >= 2) $deposit_summary['second']['people']++;
            if ($cnt >= 3) $deposit_summary['third']['people']++;
        }
        $first_q = mysqli_query($conn, "SELECT SUM(motta) AS amt FROM thevani WHERE balakedara IN ('$code_list') AND sthiti=1");
        $first_row = mysqli_fetch_assoc($first_q);
        $deposit_summary['first']['amount'] = $first_row['amt'] ?? 0;

        // 🔶 First Withdrawal
        $withdraw_q = mysqli_query($conn, "SELECT COUNT(DISTINCT balakedara) AS people, SUM(motta) AS amount 
                                           FROM hintegedukolli WHERE balakedara IN ('$code_list') AND sthiti=1");
        $withdraw_row = mysqli_fetch_assoc($withdraw_q);
        $deposit_summary['first_withdrawal']['people'] = $withdraw_row['people'] ?? 0;
        $deposit_summary['first_withdrawal']['amount'] = $withdraw_row['amount'] ?? 0;

        // 🔶 Direct Charges (placeholder)
        $deposit_summary['direct_first']['people'] = 0;
        $deposit_summary['direct_first']['amount'] = 0;
        $deposit_summary['direct_second']['people'] = 0;
        $deposit_summary['direct_second']['amount'] = 0;
    } else {
        $error = "❌ Agent not found under your line.";
    }
}

// Add this section below your previous deposit_summary and recharge_summary setup

$withdrawal_summary = [
    'pending' => ['amount' => 0, 'times' => 0, 'people' => 0],
    'audit' => ['amount' => 0, 'times' => 0, 'people' => 0],
    'failed' => ['amount' => 0, 'times' => 0, 'people' => 0],
    'approved' => ['amount' => 0, 'times' => 0, 'people' => 0]
];

if ($agent_id && !$error) {
    $withdrawal_q = mysqli_query($conn, "
        SELECT sthiti, SUM(motta) AS total_amount, COUNT(*) AS total_times, COUNT(DISTINCT balakedara) AS total_people
        FROM hintegedukolli WHERE balakedara IN ('$code_list') GROUP BY sthiti
    ");

    while ($row = mysqli_fetch_assoc($withdrawal_q)) {
        if ($row['sthiti'] == 2) { // pending
            $withdrawal_summary['pending'] = [
                'amount' => $row['total_amount'],
                'times' => $row['total_times'],
                'people' => $row['total_people']
            ];
        } elseif ($row['sthiti'] == 3) { // audit
            $withdrawal_summary['audit'] = [
                'amount' => $row['total_amount'],
                'times' => $row['total_times'],
                'people' => $row['total_people']
            ];
        } elseif ($row['sthiti'] == 4) { // failed
            $withdrawal_summary['failed'] = [
                'amount' => $row['total_amount'],
                'times' => $row['total_times'],
                'people' => $row['total_people']
            ];
        } elseif ($row['sthiti'] == 1) { // approved
            $withdrawal_summary['approved'] = [
                'amount' => $row['total_amount'],
                'times' => $row['total_times'],
                'people' => $row['total_people']
            ];
        }
    }
}







$equipment_summary = [
    'registered' => ['ios' => 0, 'android' => 0, 'pc' => 0, 'unknown' => 0],
    'loggedin' => ['ios' => 0, 'android' => 0, 'pc' => 0, 'unknown' => 0]
];

if ($agent_id && !$error) {
    $codes = [$agent_id];
    $result = mysqli_query($conn, "SELECT id FROM shonu_subjects WHERE code='$agent_id' OR code1='$agent_id' OR code2='$agent_id' OR code3='$agent_id' OR code4='$agent_id' OR code5='$agent_id'");
    while ($row = mysqli_fetch_assoc($result)) {
        $codes[] = $row['id'];
    }
    $code_list = implode("','", $codes);

    $query = mysqli_query($conn, "SELECT tnegaresunohs, ishonup FROM shonu_subjects WHERE id IN ('$code_list')");
    while ($row = mysqli_fetch_assoc($query)) {
        $ua = strtolower($row['tnegaresunohs']);
        $ishonup = $row['ishonup'];

        $platform = 'unknown';
        if (strpos($ua, 'iphone') !== false || strpos($ua, 'ios') !== false) $platform = 'ios';
        elseif (strpos($ua, 'android') !== false) $platform = 'android';
        elseif (strpos($ua, 'windows') !== false || strpos($ua, 'mac') !== false) $platform = 'pc';

        $equipment_summary['registered'][$platform]++;
        if ($ishonup == 1) $equipment_summary['loggedin'][$platform]++;
    }
}




?>

<?php include 'teacher_nav.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .summary-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .details-link {
            font-size: 0.9em;
            text-decoration: underline;
            cursor: pointer;
        }
        .summary-value {
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        
        .dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
    vertical-align: middle;
}

.dot-ios { background-color: blue; }
.dot-android { background-color: #20c997; } /* teal */
.dot-pc { background-color: orange; }
.dot-unknown { background-color: red; }

    </style>
</head>
<body class="bg-light">
<div class="container my-4">
    <h3 class="mb-4">Agent Summary Panel</h3>

    <form class="mb-4" method="GET">
        <div class="input-group">
            <input type="text" name="agent_id" class="form-control" placeholder="Enter Agent ID" value="<?= htmlspecialchars($agent_id) ?>" required>
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Member Card -->
<div class="col-md-4">
    <div class="summary-card">
        <div class="summary-title">👥 Member 
            <button class="btn btn-sm btn-primary float-end">Recalculate</button>
        </div>
        <ul class="list-unstyled">
            <li>
                1. Current Member Count: 
                <span class="text-primary fw-bold"><?= $agent_id && !$error ? $member_count : '0' ?></span> 
                <a href="#" class="details-link ms-2">Details</a>
            </li>
            <li class="mt-2">
                2. New Member Count: 
                <span class="text-primary fw-bold">0</span> 
                <a href="#" class="details-link ms-2">Details</a>
            </li>
        </ul>
    </div>
</div>


        <!-- Recharge Status Card -->
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-title">💸 Recharge Status</div>
                <button class="btn btn-sm btn-primary mb-2">🔄 Recalculate</button>
                <ul class="list-unstyled">
                    <li>🟠 Pending: <span class="text-danger">৳<?= $recharge_summary['pending']['amount'] ?></span>, <?= $recharge_summary['pending']['count'] ?> Times, <?= $recharge_summary['pending']['people'] ?> People <span class="details-link">Details</span></li>
                    <li>🔴 Failed: <span class="text-danger">৳<?= $recharge_summary['failed']['amount'] ?></span>, <?= $recharge_summary['failed']['count'] ?> Times, <?= $recharge_summary['failed']['people'] ?> People <span class="details-link">Details</span></li>
                    <li>🟢 Success: <span class="text-danger">৳<?= $recharge_summary['success']['amount'] ?></span>, <?= $recharge_summary['success']['count'] ?> Times, <?= $recharge_summary['success']['people'] ?> People <span class="details-link">Details</span></li>
                </ul>
            </div>
        </div>

        <!-- First Deposit & Withdrawal Card -->
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-title">💰 First Deposit & First Withdrawal</div>
                <button class="btn btn-sm btn-primary mb-2">🔄 Recalculate</button>
                <ul class="list-unstyled">
                    <li>First Recharge: <?= $deposit_summary['first']['people'] ?> People, <span class="text-danger">৳<?= $deposit_summary['first']['amount'] ?></span> <span class="details-link">Details</span></li>
                    <li>Second Recharge: <?= $deposit_summary['second']['people'] ?> People, <span class="text-danger">৳0.00</span> <span class="details-link">Details</span></li>
                    <li>Third Recharge: <?= $deposit_summary['third']['people'] ?> People, <span class="text-danger">৳0.00</span> <span class="details-link">Details</span></li>
                    <li>First Withdrawal: <?= $deposit_summary['first_withdrawal']['people'] ?> People, <span class="text-danger">৳<?= $deposit_summary['first_withdrawal']['amount'] ?></span> <span class="details-link">Details</span></li>
                    <li>Direct First Charge: <?= $deposit_summary['direct_first']['people'] ?> People, <span class="text-danger">৳<?= $deposit_summary['direct_first']['amount'] ?></span> <span class="details-link">Details</span></li>
                    <li>Direct Second Charge: <?= $deposit_summary['direct_second']['people'] ?> People, <span class="text-danger">৳<?= $deposit_summary['direct_second']['amount'] ?></span> <span class="details-link">Details</span></li>
                </ul>
            </div>
        </div>




<div class="row">
    <!-- Withdrawal Status Section -->
    <div class="col-md-6 col-lg-4">
        <div class="summary-card">
            <div class="summary-title">💸 Withdrawal Status</div>
            <button class="btn btn-sm btn-primary mb-2">🔄 Recalculate</button>
            <ul class="list-unstyled">
                <li>🟠 Pending Review: 
                    <span class="text-danger">৳<?= $withdrawal_summary['pending']['amount'] ?></span>, 
                    <span class="text-danger"><?= $withdrawal_summary['pending']['times'] ?></span> Times, 
                    <span class="text-danger"><?= $withdrawal_summary['pending']['people'] ?></span> People 
                    <span class="details-link">Details</span>
                </li>
                <li>🔴 Third-Party Audit: 
                    <span class="text-danger">৳<?= $withdrawal_summary['audit']['amount'] ?></span>, 
                    <span class="text-danger"><?= $withdrawal_summary['audit']['times'] ?></span> Times, 
                    <span class="text-danger"><?= $withdrawal_summary['audit']['people'] ?></span> People 
                    <span class="details-link">Details</span>
                </li>
                <li>🔴 Withdrawal Failed: 
                    <span class="text-danger">৳<?= $withdrawal_summary['failed']['amount'] ?></span>, 
                    <span class="text-danger"><?= $withdrawal_summary['failed']['times'] ?></span> Times, 
                    <span class="text-danger"><?= $withdrawal_summary['failed']['people'] ?></span> People 
                    <span class="details-link">Details</span>
                </li>
                <li>🔵 Approved: 
                    <span class="text-danger">৳<?= $withdrawal_summary['approved']['amount'] ?></span>, 
                    <span class="text-danger"><?= $withdrawal_summary['approved']['times'] ?></span> Times, 
                    <span class="text-danger"><?= $withdrawal_summary['approved']['people'] ?></span> People 
                    <span class="details-link">Details</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Equipment Section -->
    <div class="col-md-6 col-lg-4">
        <div class="summary-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5>Equipment</h5>
                <button class="btn btn-sm btn-primary">Recalculate</button>
            </div>
            <p class="fw-bold mb-1">1. Registered Users</p>
            <ul class="list-unstyled">
                <li><span class="dot dot-ios"></span> IOS: <?= $equipment_summary['registered']['ios'] ?> People, <span class="details-link">Details</span></li>
                <li><span class="dot dot-android"></span> Android: <?= $equipment_summary['registered']['android'] ?> People, <span class="details-link">Details</span></li>
                <li><span class="dot dot-pc"></span> PC: <?= $equipment_summary['registered']['pc'] ?> People, <span class="details-link">Details</span></li>
                <li><span class="dot dot-unknown"></span> Unknown: <?= $equipment_summary['registered']['unknown'] ?> People, <span class="details-link">Details</span></li>
            </ul>
            <p class="fw-bold mb-1 mt-3">2. Logged-In Users</p>
            <ul class="list-unstyled">
                <li><span class="dot dot-ios"></span> IOS: <?= $equipment_summary['loggedin']['ios'] ?> People, <span class="details-link">Details</span></li>
                <li><span class="dot dot-android"></span> Android: <?= $equipment_summary['loggedin']['android'] ?> People, <span class="details-link">Details</span></li>
                <li><span class="dot dot-pc"></span> PC: <?= $equipment_summary['loggedin']['pc'] ?> People, <span class="details-link">Details</span></li>
                <li><span class="dot dot-unknown"></span> Unknown: <?= $equipment_summary['loggedin']['unknown'] ?> People, <span class="details-link">Details</span></li>
            </ul>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
