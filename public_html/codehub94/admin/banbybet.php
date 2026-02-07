<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

function checkIllegalBets($conn, $specific_user_id = null) {
    $whereClause = $specific_user_id ? "WHERE byabaharkarta = '$specific_user_id'" : "";

    $query = "
        SELECT byabaharkarta, kalaparichaya, COUNT(DISTINCT ojana) as bet_type_count
        FROM (
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_zehn
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_drei
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_funf
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_trx
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_trx3
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_trx5
            UNION ALL
            SELECT byabaharkarta, kalaparichaya, ojana FROM bajikattuttate_trx10
        ) AS all_bets
        $whereClause
        GROUP BY byabaharkarta, kalaparichaya
        HAVING bet_type_count > 1
    ";

    return $conn->query($query);
}

function banUser($conn, $userid, $reason) {
    $check_query = "SELECT id FROM banned_users WHERE user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert_query = "INSERT INTO banned_users (user_id, reason) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ss", $userid, $reason);
        $stmt->execute();
    }
}

// ✅ Fix starts here
$searched = false;
$searched_user_id = '';
$illegal_bets = false;

// Step 1: If POST submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched = true;
    $searched_user_id = trim($_POST['user_id']);

    // Store in session
    $_SESSION['searched_user_id'] = $searched_user_id;

    // Redirect to clear POST
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Step 2: If redirected
if (isset($_SESSION['searched_user_id'])) {
    $searched_user_id = $_SESSION['searched_user_id'];
    unset($_SESSION['searched_user_id']);
    $searched = true;
    $illegal_bets = checkIllegalBets($conn, $searched_user_id);
} else {
    $illegal_bets = checkIllegalBets($conn);
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Real-time Illegal Bets Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="vendors/chart.js/Chart.min.js"></script>
    <style>
        body { background-color: #f4f6fc; }
        h3 { font-weight: bold; color: #1a1a40; }
        .card { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .progress { height: 8px; }
        .chart-title { font-size: 16px; font-weight: 600; margin-bottom: 10px; }
        .filter-section { background-color: #edefff; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
    </style>
<?php include 'header.php'; ?>

<body>
<div class="container py-4">
    <h3 class="mb-4 text-center">Real-time Illegal Bets Dashboard</h3>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                <div class="chart-title">Illegal Bets by Game</div>
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="chart-title">Illegal Bets Distribution</div>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <div class="chart-title">Illegal Bets vs Users Scatter Plot</div>
                <canvas id="scatterChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section mb-4">
        <form method="post" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label>User ID</label>
                <input type="text" name="user_id" class="form-control" placeholder="Enter User ID" value="<?= htmlspecialchars($searched_user_id) ?>">
            </div>
            <div class="col-md-4">
                <label>Sort Order</label>
                <select class="form-select">
                    <option value="high">High to Low</option>
                    <option value="low">Low to High</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <?php
    $gameMap = [
        'bajikattuttate_zehn' => 'Wingo 30s',
        'bajikattuttate' => 'Wingo 1min',
        'bajikattuttate_drei' => 'Wingo 3min',
        'bajikattuttate_funf' => 'Wingo 5min',
        'bajikattuttate_trx' => 'TRX 1',
        'bajikattuttate_trx3' => 'TRX 3',
        'bajikattuttate_trx5' => 'TRX 5',
        'bajikattuttate_trx10' => 'TRX 10',
    ];

    if ($illegal_bets && $illegal_bets->num_rows > 0): ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>User ID</th>
                            <th>Total Illegal Count</th>
                            <th>Progress (Max: 10)</th>
                            <th>Games</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $users = [];
                    while ($row = $illegal_bets->fetch_assoc()) {
                        $uid = $row['byabaharkarta'];
                        $period = $row['kalaparichaya'];
                        $gamesUsed = [];
                        foreach ($gameMap as $table => $gameName) {
                            $q = "SELECT COUNT(*) as total FROM $table WHERE byabaharkarta = '$uid' AND kalaparichaya = '$period'";
                            $res = $conn->query($q);
                            if ($res) {
                                $rc = $res->fetch_assoc();
                                if ((int)$rc['total'] > 0) $gamesUsed[] = $gameName;
                            }
                        }
                        $users[$uid]['count'] = ($users[$uid]['count'] ?? 0) + 1;
                        $users[$uid]['games'][] = implode(', ', $gamesUsed);
                        $users[$uid]['last_updated'] = date('Y-m-d H:i');
                    }

                    foreach ($users as $uid => $data): ?>
                        <tr>
                            <td><?= $uid ?></td>
                            <td><?= $data['count'] ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?= min($data['count'] * 10, 100) ?>%;"></div>
                                </div>
                            </td>
                            <td><?= implode(' | ', array_unique($data['games'])) ?></td>
                            <td><?= $data['last_updated'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($searched): ?>
        <div class="alert alert-success mt-3">📅 No illegal bets found for User ID: <strong><?= htmlspecialchars($searched_user_id) ?></strong></div>
    <?php endif; ?>
</div>

<script>
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: ['Wingo 1min', 'Wingo 3min', 'Wingo 30s'],
        datasets: [{ label: 'illegalBets', data: [12, 8, 5], backgroundColor: '#8a76d3' }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ['Wingo'],
        datasets: [{ label: 'Illegal Distribution', data: [100], backgroundColor: ['#8a76d3'] }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('scatterChart'), {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Games',
            data: [ { x: 14, y: 4 } ],
            backgroundColor: '#8a76d3'
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: { title: { display: true, text: 'Illegal Bets' } },
            y: { title: { display: true, text: 'Users' } }
        }
    }
});
</script>
</body>
</html>