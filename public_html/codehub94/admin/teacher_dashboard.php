<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// FUNCTIONS
function getCount($conn, $query) {
    $res = mysqli_query($conn, $query);
    $row = mysqli_fetch_row($res);
    return $row[0] ?? 0;
}

function getSum($conn, $query) {
    $res = mysqli_query($conn, $query);
    $row = mysqli_fetch_row($res);
    return $row[0] ?? 0;
}

// GET DATE FILTER
$dateFilter = '';
if (isset($_GET['date'])) {
    switch ($_GET['date']) {
        case 'today':
            $dateFilter = "AND DATE(dinankavannuracisi) = CURDATE()";
            break;
        case 'yesterday':
            $dateFilter = "AND DATE(dinankavannuracisi) = CURDATE() - INTERVAL 1 DAY";
            break;
        case 'thisweek':
            $dateFilter = "AND YEARWEEK(dinankavannuracisi,1) = YEARWEEK(CURDATE(),1)";
            break;
        case 'lastweek':
            $dateFilter = "AND YEARWEEK(dinankavannuracisi,1) = YEARWEEK(CURDATE(),1) -1";
            break;
    }
}

// GET SEARCH UID
$search_uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

// STEP 1: Get agent userids under teacher
$agent_userids = [];
$res1 = mysqli_query($conn, "SELECT userid FROM tb_agent WHERE teacherid='$teacher_id'");
while ($row = mysqli_fetch_assoc($res1)) {
    $agent_userids[] = $row['userid'];
}

// STEP 2: Get agent owncodes
$agent_owncodes = [];
if (!empty($agent_userids)) {
    $ids_str = implode(",", $agent_userids);
    $res2 = mysqli_query($conn, "SELECT owncode FROM shonu_subjects WHERE id IN ($ids_str)");
    while ($row = mysqli_fetch_assoc($res2)) {
        $agent_owncodes[] = "'" . $row['owncode'] . "'";
    }
}

$agent_count = count($agent_userids);
$agent_codes_str = implode(",", $agent_owncodes);

// STEP 3: Get downline userids
$downline_userids = [];
if (!empty($agent_owncodes)) {
    $res3 = mysqli_query($conn, "
        SELECT id FROM shonu_subjects 
        WHERE owncode IN ($agent_codes_str)
        OR code IN ($agent_codes_str)
        OR code1 IN ($agent_codes_str)
        OR code2 IN ($agent_codes_str)
        OR code3 IN ($agent_codes_str)
        OR code4 IN ($agent_codes_str)
        OR code5 IN ($agent_codes_str)
    ");
    while ($row = mysqli_fetch_assoc($res3)) {
        $downline_userids[] = $row['id'];
    }
}

if (!empty($downline_userids)) {
    $downline_ids_str = implode(",", $downline_userids);
} else {
    $downline_ids_str = '0';
}
$total_agent_users = count($downline_userids);

// If search UID is set → filter to only that UID if allowed
if ($search_uid > 0 && in_array($search_uid, $downline_userids)) {
    $downline_ids_str = $search_uid;
} elseif ($search_uid > 0) {
    echo "<script>alert('❌ Invalid UID for this teacher'); window.location.href='teacher_dashboard.php';</script>";
    exit;
}

// STEP 4: Get recharge
$total_recharge = getSum($conn, "
    SELECT SUM(motta) FROM thevani 
    WHERE sthiti=1 AND balakedara IN ($downline_ids_str) $dateFilter
");

// STEP 5: Get withdrawal (only Completed)
$total_withdrawal = getSum($conn, "
    SELECT SUM(motta) FROM hintegedukolli 
    WHERE tike='Completed' AND balakedara IN ($downline_ids_str) $dateFilter
");

// STEP 6: Get teacher mobile + balance
$teacher_profile = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id, total_balance FROM teacher_profile WHERE user_id='$teacher_id'"));
$teacher_balance = $teacher_profile['total_balance'] ?? 0;
$user_id = $teacher_profile['user_id'];

$teacher_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT mobile FROM shonu_subjects WHERE id='$user_id'"));
$teacher_mobile = $teacher_data['mobile'] ?? 'N/A';
?>

<?php include 'teacher_nav.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .summary-card {
            background: #001f3f;
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .summary-card h6 { font-size: 14px; margin-bottom: 5px; }
        .summary-card h3 { font-size: 22px; margin: 5px 0; }
        .summary-card .material-icons { font-size: 28px; position: absolute; bottom: 10px; right: 10px; opacity: 0.4; }
        footer { background: #f8f9fc; text-align: center; padding: 10px; font-size: 14px; margin-top: 30px; }
    </style>
</head>
<body class="bg-light">

<div class="container my-4">
    <h3 class="mb-4">👨‍🏫 Teacher Dashboard</h3>

    <form method="GET" class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <input type="text" name="uid" placeholder="Search UID" value="<?= htmlspecialchars($_GET['uid'] ?? '') ?>" class="form-control" style="max-width:200px;">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="teacher_dashboard.php" class="btn btn-secondary">Reset</a>

            <div class="btn-group ms-2" role="group">
                <a href="?date=today" class="btn btn-outline-primary <?= ($_GET['date'] ?? '') === 'today' ? 'active' : '' ?>">Today</a>
                <a href="?date=yesterday" class="btn btn-outline-primary <?= ($_GET['date'] ?? '') === 'yesterday' ? 'active' : '' ?>">Yesterday</a>
                <a href="?date=thisweek" class="btn btn-outline-primary <?= ($_GET['date'] ?? '') === 'thisweek' ? 'active' : '' ?>">This Week</a>
                <a href="?date=lastweek" class="btn btn-outline-primary <?= ($_GET['date'] ?? '') === 'lastweek' ? 'active' : '' ?>">Last Week</a>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-4"><div class="summary-card"><h6>Total Agents</h6><h3><?= $agent_count ?></h3><span class="material-icons">groups</span></div></div>
        <div class="col-md-4"><div class="summary-card"><h6>Total Agent Users</h6><h3><?= $total_agent_users ?></h3><span class="material-icons">person</span></div></div>
        <div class="col-md-4"><div class="summary-card"><h6>Total Agents Withdrawal</h6><h3>৳<?= number_format($total_withdrawal, 2) ?></h3><span class="material-icons">money_off</span></div></div>
        <div class="col-md-4"><div class="summary-card"><h6>Total Agents Recharge</h6><h3>৳<?= number_format($total_recharge, 2) ?></h3><span class="material-icons">payments</span></div></div>
        <div class="col-md-4"><div class="summary-card"><h6>Teacher Mobile ID</h6><h3><?= $teacher_mobile ?></h3><span class="material-icons">smartphone</span></div></div>
        <div class="col-md-4"><div class="summary-card"><h6>Teacher Balance</h6><h3>৳<?= number_format($teacher_balance, 2) ?></h3><span class="material-icons">account_balance_wallet</span></div></div>
        <div class="col-md-4"><div class="summary-card"><h6>Total Red Envelope</h6><h3>Coming Soon</h3><span class="material-icons">redeem</span></div></div>
    </div>
</div>

<footer>&copy; <?= date('Y') ?> Teacher Dashboard | Made with ❤️</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
