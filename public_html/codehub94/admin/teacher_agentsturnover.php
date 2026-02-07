<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");
include 'conn.php';

$teacher_id = $_SESSION['teacher_id'];
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;

// Step 1: Get agent user IDs under teacher
$agent_userids = [];
$res1 = mysqli_query($conn, "SELECT userid FROM tb_agent WHERE teacherid='$teacher_id'");
while ($row = mysqli_fetch_assoc($res1)) {
    $agent_userids[] = $row['userid'];
}

// Step 2: Get owncodes of those agents
$agent_owncodes = [];
if (!empty($agent_userids)) {
    $ids_str = implode(",", $agent_userids);
    $res2 = mysqli_query($conn, "SELECT owncode FROM shonu_subjects WHERE id IN ($ids_str)");
    while ($row = mysqli_fetch_assoc($res2)) {
        $agent_owncodes[] = "'" . $row['owncode'] . "'";
    }
}

// Step 3: Get downline user IDs (including agents)
$allowed_uids = [];
if (!empty($agent_owncodes)) {
    $owncodes_str = implode(",", $agent_owncodes);
    $res3 = mysqli_query($conn, "
        SELECT id FROM shonu_subjects 
        WHERE owncode IN ($owncodes_str)
        OR code IN ($owncodes_str) 
        OR code1 IN ($owncodes_str)
        OR code2 IN ($owncodes_str)
        OR code3 IN ($owncodes_str)
        OR code4 IN ($owncodes_str)
        OR code5 IN ($owncodes_str)
    ");
    while ($row = mysqli_fetch_assoc($res3)) {
        $allowed_uids[] = $row['id'];
    }
}

// Step 4: Build WHERE condition → only search if UID is valid
$uid_condition = "0";
$show_data = false;
if (!empty($allowed_uids) && $uid > 0) {
    if (in_array($uid, $allowed_uids)) {
        $uid_condition = "user_id = '$uid'";
        $show_data = true;
    }
}

// Step 5: Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_uid'])) {
    $adjust_uid = intval($_POST['adjust_uid']);
    if (in_array($adjust_uid, $allowed_uids)) {
        $amount_of_code = floatval($_POST['amount_of_code']);
        $can_withdraw_amount = floatval($_POST['can_withdraw_amount']);
        $need_to_bet = floatval($_POST['need_to_bet']);
        $withdraw_start_time = $_POST['withdraw_start_time'];
        $withdraw_end_time = $_POST['withdraw_end_time'];
        $withdraw_remaining_times = intval($_POST['withdraw_remaining_times']);
        $manual_override = intval($_POST['manual_override']);

        mysqli_query($conn, "UPDATE user_withdraw_summary SET 
            amount_of_code = '$amount_of_code',
            can_withdraw_amount = '$can_withdraw_amount',
            need_to_bet = '$need_to_bet',
            withdraw_start_time = '$withdraw_start_time',
            withdraw_end_time = '$withdraw_end_time',
            withdraw_remaining_times = '$withdraw_remaining_times',
            manual_override = '$manual_override',
            manual_updated_at = NOW()
            WHERE user_id = '$adjust_uid'");
    }
}

// Step 6: Fetch user summary only if allowed
$data = null;
if ($show_data) {
    $res = mysqli_query($conn, "SELECT * FROM user_withdraw_summary WHERE $uid_condition LIMIT 1");
    $data = mysqli_fetch_assoc($res);
}

// Labels + chart data
$labels = ['Total Recharge', 'Total Bet', 'Wallet Balance', 'Can Withdraw', 'Need to Bet'];
$values = [
    floatval($data['total_recharge'] ?? 0),
    floatval($data['total_bet'] ?? 0),
    floatval($data['wallet'] ?? 0),
    floatval($data['can_withdraw_amount'] ?? 0),
    floatval($data['need_to_bet'] ?? 0)
];

// Send alert if UID is invalid
if ($uid > 0 && !$show_data) {
    echo "<script>alert('❌ Invalid UID or not under your team!');</script>";
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Turnover Manager</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f7f9fc; }
    .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .search-box input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
    .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
    .search-box button { background: #001d6e; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
    .chart-section { display: flex; gap: 20px; flex-wrap: wrap; }
    .chart-box { flex: 1; min-width: 300px; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .edit-section { margin-top: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { padding: 10px; text-align: center; border: 1px solid #ccc; }
    th { background: #001d6e; color: #fff; }
    input[type='number'], input[type='text'] { width: 80px; padding: 5px; border-radius: 5px; border: 1px solid #bbb; }
    .material-btn { background: #001d6e; color: white; padding: 6px 12px; border: none; border-radius: 6px; display: flex; align-items: center; gap: 4px; cursor: pointer; }
  </style>
  
  

<?php include 'teacher_nav.php'; ?>



 
 <div style="max-width: 90%; width: 90vw; overflow-x: hidden; margin: 0 auto;">

  <!-- CHARTS CARD -->
  <div class="card">
    <h2>Agents Underline Users Turnover Chart</h2>
    <div style="background: #e8f0fe; border-left: 4px solid #1a73e8; padding: 12px 15px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; color: #202124;">
  <strong>How to use:</strong>
  <ul style="padding-left: 18px; margin-top: 5px;">
    <li>🔍 Enter the UID and click <b>Search</b> to load user data.</li>
    <li>📊 View recharge, bet, and withdraw details in the charts.</li>
    <li>✍️ Use the table below to edit withdraw conditions.</li>
    <li>💾 Click the <b>Save</b> button to update the user’s summary.</li>
    <li>♻️ Click <b>Clear</b> to reset the page and search again.</li>
  </ul>
</div>

    <form class="search-box" method="GET" style="display: flex; gap: 10px; margin-bottom: 15px;">
      <input type="text" name="uid" placeholder="Search by UID" value="<?= htmlspecialchars($uid) ?>" style="padding: 6px; flex: 1;">
      <button type="submit" style="padding: 6px 10px;">Search</button>
      <a href="?" style="text-decoration: none;"><button type="button" style="padding: 6px 10px;">Clear</button></a>
    </form>

    <div class="chart-section" style="display: flex; flex-wrap: wrap; gap: 30px;">
      <div class="chart-box" style="flex: 1; min-width: 280px;">
        <h4>Bar: User Turnover</h4>
        <canvas id="barChart"></canvas>
      </div>
      <div class="chart-box" style="flex: 1; min-width: 280px;">
        <h4>Pie: Amount Distribution</h4>
        <canvas id="pieChart"></canvas>
      </div>
      <div class="chart-box" style="flex: 1; min-width: 280px;">
        <h4>Scatter: Index vs Amount</h4>
        <canvas id="scatterChart"></canvas>
      </div>
    </div>
  </div>

  <!-- EDIT FORM CARD -->
  <?php if ($data): ?>
  <div class="card">
    <h3>✏️ Edit Withdrawal Summary</h3>
    <form method="POST">
      <input type="hidden" name="adjust_uid" value="<?= $data['user_id'] ?>">
      <div style="overflow-x: auto;">
        <table style="width: 100%; min-width: 900px; border-collapse: collapse; font-size: 13px; text-align: center;">
          <thead>
            <tr style="background: #001d6e; color: white;">
              <th>UID</th>
              <th>Wallet</th>
              <th>Recharge</th>
              <th>Purchase</th>
              <th>Total Bet</th>
              <th>Min Withdraw</th>
              <th>Can Withdraw</th>
              <th>Code</th>
              <th>Need Bet</th>
              <th>Start</th>
              <th>End</th>
              <th>Remain</th>
              <th>Manual</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= $data['user_id'] ?></td>
              <td><?= $data['wallet'] ?></td>
              <td><?= $data['total_recharge'] ?></td>
              <td><?= $data['total_purchase'] ?></td>
              <td><?= $data['total_bet'] ?></td>
              <td><?= $data['min_withdraw'] ?></td>
              <td><input type="number" step="0.01" name="can_withdraw_amount" value="<?= $data['can_withdraw_amount'] ?>" style="width: 70px;"></td>
              <td><input type="number" step="0.01" name="amount_of_code" value="<?= $data['amount_of_code'] ?>" style="width: 70px;"></td>
              <td><input type="number" step="0.01" name="need_to_bet" value="<?= $data['need_to_bet'] ?>" style="width: 70px;"></td>
              <td><input type="text" name="withdraw_start_time" value="<?= $data['withdraw_start_time'] ?>" style="width: 70px;"></td>
              <td><input type="text" name="withdraw_end_time" value="<?= $data['withdraw_end_time'] ?>" style="width: 70px;"></td>
              <td><input type="number" name="withdraw_remaining_times" value="<?= $data['withdraw_remaining_times'] ?>" style="width: 60px;"></td>
              <td><input type="number" name="manual_override" value="<?= $data['manual_override'] ?>" style="width: 60px;"></td>
              <td>
                <button style="background: #001d6e; color: white; padding: 6px 10px; border: none; border-radius: 4px;">
                  <span class="material-icons" style="vertical-align: middle;">save</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </form>
  </div>
  <?php endif; ?>

</div>

<script>
const labels = <?= json_encode($labels) ?>;
const values = <?= json_encode($values) ?>;

new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: { labels: labels, datasets: [{ label: 'Amount', data: values, backgroundColor: '#5a69f2' }] },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('pieChart'), {
  type: 'pie',
  data: {
    labels: labels,
    datasets: [{ data: values, backgroundColor: ['#5a69f2', '#769ef1', '#a8bcf2', '#d2dbf9', '#edf0fc'] }]
  },
  options: { responsive: true }
});

new Chart(document.getElementById('scatterChart'), {
  type: 'scatter',
  data: {
    datasets: [{ label: 'Amount', data: values.map((y, x) => ({ x: x + 1, y })), backgroundColor: '#5a69f2' }]
  },
  options: {
    responsive: true,
    scales: {
      x: { title: { display: true, text: 'Index' } },
      y: { title: { display: true, text: 'Amount' } }
    }
  }
});
</script>
</body>
</html>