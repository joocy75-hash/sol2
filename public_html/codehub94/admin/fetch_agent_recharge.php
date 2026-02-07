<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';
date_default_timezone_set("Asia/Dhaka");

$downlineData = [];
$agent_id = '';
$totalRechargeAmount = 0;
$totalRechargeUsers = 0;

// If form is submitted, redirect to GET
if (isset($_POST['search'])) {
    $agent_id = intval($_POST['agent_id']);
    header("Location: ".$_SERVER['PHP_SELF']."?agent_id=".$agent_id);
    exit;
}

// If redirected or loaded with GET
if (isset($_GET['agent_id'])) {
    $agent_id = intval($_GET['agent_id']);

    // Get agent's owncode
    $agent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT owncode FROM shonu_subjects WHERE id='$agent_id'"));
    $owncode = $agent['owncode'] ?? '';

    if ($owncode) {
        // Get downline users
        $downlines = mysqli_query($conn, "SELECT id, mobile FROM shonu_subjects WHERE code='$owncode'");
        
        while ($user = mysqli_fetch_assoc($downlines)) {
            $userid = $user['id'];
            $mobile = $user['mobile'];

            // Check today's successful recharge
            $recharge = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT SUM(motta) AS today_recharge 
                FROM thevani 
                WHERE balakedara='$userid' 
                AND sthiti='1' 
                AND DATE(dinankavannuracisi)=CURDATE()
            "));

            $today_recharge = $recharge['today_recharge'] ?? 0;

            if ($today_recharge > 0) {
                $downlineData[] = [
                    'userid' => $userid,
                    'mobile' => $mobile,
                    'today_recharge' => $today_recharge
                ];
                $totalRechargeAmount += $today_recharge;
                $totalRechargeUsers++;
            }
        }
    }
}
?>



<?php include 'header.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Today's Downline Recharge</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <h3 class="mb-4">🔍 Downline Today's Recharge Search</h3>

    <form method="POST" class="mb-4">
        <div class="input-group">
            <input type="number" name="agent_id" class="form-control" placeholder="Enter Agent ID" value="<?= htmlspecialchars($agent_id) ?>" required>
            <button class="btn btn-primary" type="submit" name="search">Search</button>
        </div>
    </form>

    <?php if (!empty($agent_id)): ?>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <span class="material-icons text-success" style="font-size: 40px;">payments</span>
                    <h6 class="mt-2">Today's Total Recharge</h6>
                    <h4 class="text-primary">৳<?= number_format($totalRechargeAmount) ?></h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <span class="material-icons text-info" style="font-size: 40px;">group</span>
                    <h6 class="mt-2">Total Users Recharge</h6>
                    <h4 class="text-primary"><?= $totalRechargeUsers ?></h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 text-center p-3">
                    <span class="material-icons text-warning" style="font-size: 40px;">badge</span>
                    <h6 class="mt-2">Agent ID</h6>
                    <h4 class="text-primary"><?= htmlspecialchars($agent_id) ?></h4>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <?php if (count($downlineData) > 0): ?>
            <div class="card shadow-sm p-4 mb-4">
                <canvas id="rechargeChart" height="100"></canvas>
            </div>
        <?php endif; ?>

        <!-- Table -->
        <?php if (count($downlineData) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Mobile</th>
                            <th>Today's Recharge (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($downlineData as $data): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $data['userid'] ?></td>
                                <td><?= $data['mobile'] ?></td>
                                <td>৳<?= number_format($data['today_recharge']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No downline recharges found for today.</div>
        <?php endif; ?>

    <?php endif; ?>
</div>


<?php if (count($downlineData) > 0): ?>
<script>
const ctx = document.getElementById('rechargeChart').getContext('2d');
const rechargeChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($downlineData, 'userid')) ?>,
        datasets: [{
            label: 'Today\'s Recharge ৳',
            data: <?= json_encode(array_column($downlineData, 'today_recharge')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
<?php endif; ?>

</body>
</html>
