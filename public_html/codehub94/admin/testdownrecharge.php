<?php
include("conn.php");
date_default_timezone_set("Asia/Dhaka");

$downlineData = [];
$agent_id = '';

if (isset($_POST['search'])) {
    $agent_id = intval($_POST['agent_id']);
    
    // Agent ka owncode uthao
    $agent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT owncode FROM shonu_subjects WHERE id='$agent_id'"));
    $owncode = $agent['owncode'] ?? '';

    if ($owncode) {
        // Downline users lao
        $downlines = mysqli_query($conn, "SELECT id, mobile FROM shonu_subjects WHERE code='$owncode'");
        
        while ($user = mysqli_fetch_assoc($downlines)) {
            $userid = $user['id'];
            $mobile = $user['mobile'];

            // Aaj ka successful recharge dekhna hai
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
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Today's Downline Recharge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <?php if (isset($_POST['search'])): ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
