<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';

// Step 1: Get total deposits from 'thevani'
$totalDepositsResult = mysqli_query($conn, "SELECT SUM(motta) as totalDeposits FROM thevani");
$totalDepositsRow = mysqli_fetch_assoc($totalDepositsResult);
$totalDeposits = $totalDepositsRow['totalDeposits'] ?? 0;

// Step 2: Get total bets count from all betting tables
$bettingTables = [
    'bajikattuttate', 'bajikattuttate_aidudi', 'bajikattuttate_aidudi_drei', 'bajikattuttate_aidudi_funf', 'bajikattuttate_aidudi_zehn',
    'bajikattuttate_drei', 'bajikattuttate_funf', 'bajikattuttate_kemuru', 'bajikattuttate_kemuru_drei', 'bajikattuttate_kemuru_funf',
    'bajikattuttate_kemuru_zehn', 'bajikattuttate_trx', 'bajikattuttate_trx3', 'bajikattuttate_trx5', 'bajikattuttate_trx10', 'bajikattuttate_zehn',
];

$totalBets = 0;
$allWins = [];

foreach ($bettingTables as $table) {
    // Count total bets
    $countResult = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM $table");
    $countRow = mysqli_fetch_assoc($countResult);
    $totalBets += $countRow['cnt'] ?? 0;

    // Count wins
    $winsResult = mysqli_query($conn, "SELECT byabaharkarta AS user_id FROM $table WHERE phalaphala = 'gagner'");
    while ($row = mysqli_fetch_assoc($winsResult)) {
        $uid = $row['user_id'];
        if (!isset($allWins[$uid])) {
            $allWins[$uid] = 0;
        }
        $allWins[$uid]++;
    }
}
arsort($allWins);
?>

<?php include 'header.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Performers</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: #f5f6fa; }
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-deposit { background: linear-gradient(135deg, #00b894, #00cec9); }
        .stat-roi     { background: linear-gradient(135deg, #0984e3, #74b9ff); }
        .stat-bets    { background: linear-gradient(135deg, #e17055, #fd9644); }
        .stat-winrate { background: linear-gradient(135deg, #d63031, #ff7675); }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .performer-card {
            border-radius: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 20px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .performer-card:hover {
            transform: translateY(-5px);
        }
        .rank-badge {
            background: #d1e7dd;
            padding: 5px 15px;
            border-radius: 30px;
            display: inline-block;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .circle-progress {
            width: 60px;
            height: 60px;
            border: 5px solid #198754;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #198754;
            margin-left: auto;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="mb-4 fw-bold">🏆 Top Performers</h2>

    <!-- Top Stats Section -->
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="stat-card stat-deposit">
                <i class="bi bi-wallet2"></i>
                <h5>Total Deposits</h5>
                <h3>৳<?php echo number_format($totalDeposits, 2); ?></h3>
                <p class="small">Across all performers</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-roi">
                <i class="bi bi-graph-up-arrow"></i>
                <h5>Average ROI</h5>
                <h3>0.00%</h3>
                <p class="small">Return on investment</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-bets">
                <i class="bi bi-cash-coin"></i>
                <h5>Total Bets</h5>
                <h3><?php echo $totalBets; ?></h3>
                <p class="small">Combined activity</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-winrate">
                <i class="bi bi-hand-thumbs-up"></i>
                <h5>Avg Win Rate</h5>
                <h3>0.00%</h3>
                <p class="small">Success rate</p>
            </div>
        </div>
    </div>

    <!-- Top 3 Performers -->
<div class="row g-4">
<?php
$rank = 1;
foreach ($allWins as $user_id => $total_wins) {
    if ($rank > 3) break; // Only show top 3 users

    // Fetch user info
    $uinfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT codechorkamukala, mobile FROM shonu_subjects WHERE id = '$user_id'"));
    $wallet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT motta FROM shonu_kaichila WHERE balakedara = '$user_id'"));

    if (!$uinfo) continue;

    $fake_percentage = rand(65, 100); // Fake progress % for design
?>
    <div class="col-md-4">
        <div class="performer-card">
            <div class="d-flex align-items-center justify-content-between">
                <span class="rank-badge">
                    <i class="bi bi-trophy-fill"></i> Rank #<?php echo $rank; ?> Elite
                </span>
                <div class="circle-progress"><?php echo $fake_percentage; ?>%</div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-3">
    <div>
        <i class="bi bi-person-circle"></i>
        <a href="?user=<?= $user_id; ?>" class="text-decoration-none text-dark fw-bold">
            <?= htmlspecialchars($uinfo['codechorkamukala']); ?>
        </a>
        <div class="small text-muted">User ID: <?= $user_id; ?></div>
    </div>
    <a href="user-details.php?user=<?= $user_id; ?>" class="btn btn-sm btn-outline-primary" title="View Full Details">
        <i class="bi bi-info-circle"></i>
    </a>
</div>




            <p class="mb-1">
                <i class="bi bi-telephone-fill"></i>
                <?php echo $uinfo['mobile']; ?>
            </p>

            <p class="mb-1 text-success fw-bold">
                <i class="bi bi-dot"></i> Active
            </p>

            <hr>

            <p class="mb-1">
                <i class="bi bi-award-fill"></i>
                <strong>Total Wins:</strong> <?php echo $total_wins; ?>
            </p>

            <p class="mb-1">
                <i class="bi bi-wallet2"></i>
                <strong>Wallet:</strong> ৳<?php echo number_format($wallet['motta'], 2); ?>
            </p>
        </div>
    </div>
<?php $rank++; } ?>
</div>
</div>

</body>
</html>
