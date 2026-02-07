<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php';

$salary_status = '';
if (isset($_POST['start_salary'])) {
    include("../dailysalary.php");
    $_SESSION['salary_status'] = "✅ Salary calculation run successfully based on tb_agent settings.";
    header("Location: agents_management.php");
    exit;
}

if (isset($_SESSION['salary_status'])) {
    $salary_status = $_SESSION['salary_status'];
    unset($_SESSION['salary_status']);
}

$total_agents = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT userid FROM dailysalary"));
$total_succ_rech = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM thevani WHERE sthiti = 1"))['cnt'];
$total_fail_rech = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM thevani WHERE sthiti = 0"))['cnt'];
$lastRun = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(createdate) as lastRun FROM dailysalary"))['lastRun'];

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$valid_limits = [10, 50, 100, 200];
if (!in_array($limit, $valid_limits)) $limit = 50;

$results = mysqli_query($conn, "SELECT * FROM dailysalary ORDER BY createdate DESC LIMIT $limit");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agent Salary Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .card-icon { font-size: 2rem; margin-right: 10px; }
    .accordion-body pre {
      font-size: 13px;
      white-space: pre-wrap;
      background-color: #f8f9fa;
      padding: 10px;
      border-radius: 5px;
    }
    .badge { font-size: 13px; }
    .dashboard-title { font-size: 1.8rem; font-weight: 600; }
    .summary-card h6 { font-size: 0.9rem; color: #6c757d; }
    .summary-card h5 { font-size: 1.5rem; font-weight: 600; }
    .details-box {
      background: #f8f9fa;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
    }
  </style>
<?php include 'header.php'; ?>




<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="dashboard-title">📊 Agent Daily Salary Dashboard</div>
    <div class="text-muted">🕒 <?= date("d M Y H:i") ?></div>
  </div>

  <div class="card shadow-sm border-0 p-3 mb-4">
    <form method="POST">
      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
          <h6 class="mb-1">⚙️ Daily Salary Control</h6>
          <small class="text-muted">Last Salary Run: <?= $lastRun ? date("d M Y H:i", strtotime($lastRun)) : 'Never' ?></small>
        </div>
        <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
          <button type="submit" name="start_salary" class="btn btn-outline-primary btn-sm">▶️ Run Salary</button>
        </div>
      </div>
    </form>
    <?php if ($salary_status): ?>
      <div class="alert alert-success mt-3 mb-0">✅ <?= $salary_status ?></div>
    <?php endif; ?>
  </div>

  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm summary-card">
        <div class="card-body d-flex align-items-center">
          <span class="material-icons card-icon text-primary">group</span>
          <div>
            <h6>Total Agents</h6>
            <h5><?= $total_agents ?></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm summary-card">
        <div class="card-body d-flex align-items-center">
          <span class="material-icons card-icon text-success">check_circle</span>
          <div>
            <h6>Successful Recharges</h6>
            <h5><?= $total_succ_rech ?></h5>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm summary-card">
        <div class="card-body d-flex align-items-center">
          <span class="material-icons card-icon text-danger">highlight_off</span>
          <div>
            <h6>Failed Recharges</h6>
            <h5><?= $total_fail_rech ?></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  
  
  <div class="card shadow-sm mb-4">
  <div class="card-header bg-primary text-white">
    📝 Daily Salary Eligibility Rules
  </div>
  <div class="card-body">
    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        👥 <strong>Minimum Referrals:</strong> Agent must have at least <strong>3 active referrals</strong>.
      </li>
      <li class="list-group-item">
        💰 <strong>Recharge Condition:</strong> At least <strong>1 referred user</strong> should have successfully recharged.
      </li>
      <li class="list-group-item">
        🎯 <strong>Betting Condition:</strong> A minimum of <strong>৳300 betting</strong> from referred users is required.
      </li>
      <li class="list-group-item">
        📆 <strong>Eligibility Period:</strong> Referrals' activity must be recorded on <strong>yesterday's date</strong>.
      </li>
      <li class="list-group-item">
        📈 <strong>Salary Formula:</strong> <code>(Total Recharge × Salary %)</code> as per <strong>tb_agent settings</strong>.
      </li>
      <li class="list-group-item">
        ❌ <strong>No Salary:</strong> If either recharge or betting threshold is not met, <strong>salary = ৳0</strong>.
      </li>
    </ul>
  </div>
</div>


  <div class="d-flex justify-content-end mb-3">
    <form method="GET" class="d-inline-flex align-items-center">
      <label class="me-2">Show logs:</label>
      <select name="limit" onchange="this.form.submit()" class="form-select form-select-sm w-auto">
        <?php foreach ([10, 50, 100, 200] as $opt): ?>
          <option value="<?= $opt ?>" <?= $limit === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <h5 class="mb-3">📟 Salary Distribution Logs (<?= $limit ?>)</h5>
  <div class="accordion" id="accordionSalary">
    <?php $i = 1; while($row = mysqli_fetch_assoc($results)) {
      $uid = $row['userid'];
      $salary = number_format($row['salary']);
      $created = date("d M Y H:i", strtotime($row['createdate']));
      $tsruser = json_decode($row['tsruser'] ?? '[]');
      $tfruser = json_decode($row['tfruser'] ?? '[]');
      $tsbuser = json_decode($row['tsbuser'] ?? '[]');
      $tfbuser = json_decode($row['tfbuser'] ?? '[]');

      $succRechAmt = number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total FROM thevani WHERE sthiti = 1 AND balakedara IN (" . implode(',', $tsruser ?: [0]) . ")"))['total'] ?? 0);
      $failRechAmt = number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total FROM thevani WHERE sthiti = 0 AND balakedara IN (" . implode(',', $tfruser ?: [0]) . ")"))['total'] ?? 0);

      $successList = "";
      foreach ($tsruser as $suid) {
        $mobile = mysqli_fetch_assoc(mysqli_query($conn, "SELECT mobile FROM shonu_subjects WHERE id = '$suid'"))['mobile'] ?? 'N/A';
        $amount = number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total_rech FROM thevani WHERE balakedara = '$suid' AND sthiti = 1"))['total_rech'] ?? 0);
        $successList .= "🟢 ID: $suid | 📱 $mobile | ৳$amount\n";
      }

      $failList = "";
      foreach ($tfruser as $fuid) {
        $mobile = mysqli_fetch_assoc(mysqli_query($conn, "SELECT mobile FROM shonu_subjects WHERE id = '$fuid'"))['mobile'] ?? 'N/A';
        $amount = number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(motta) as total_fail FROM thevani WHERE balakedara = '$fuid' AND sthiti = 0"))['total_fail'] ?? 0);
        $failList .= "❌ ID: $fuid | 📱 $mobile | ৳$amount\n";
      }

      $tsb = implode(', ', $tsbuser);
      $tfb = implode(', ', $tfbuser);
    ?>
    <div class="accordion-item mb-2 border shadow-sm">
      <div class="accordion-header d-flex align-items-center p-3">
        <span class="material-icons text-secondary me-2">person</span> <strong>Agent ID:</strong> <span class="text-primary ms-1 me-3"> <?= $uid ?> </span>
        <span class="badge bg-warning text-dark">Salary: ৳<?= $salary ?></span>
        <span class="text-muted small ms-3">(<?= $created ?>)</span>
        <button class="btn btn-sm btn-outline-primary ms-auto" data-bs-toggle="modal" data-bs-target="#agentDetailModal"
          onclick='showAgentModal("<?= $uid ?>", "<?= $salary ?>", "<?= $created ?>", "<?= $succRechAmt ?>", "<?= $failRechAmt ?>", `<?= trim($successList) ?>`, `<?= trim($failList) ?>`, `<?= $tsb ?>`, `<?= $tfb ?>`)'>
          <span class="material-icons">visibility</span>
        </button>
      </div>
    </div>
    <?php $i++; } ?>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="agentDetailModal" tabindex="-1" aria-labelledby="agentDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="agentDetailLabel">👤 Agent Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="agentDetailBody"></div>
    </div>
  </div>
</div>

<script>
function showAgentModal(uid, salary, created, successAmt, failAmt, successList, failList, sbids, fbids) {
  const html = `
    <p><span class="material-icons text-primary">badge</span> <strong>Agent ID:</strong> ${uid}</p>
    <p><span class="material-icons text-success">payments</span> <strong>Salary:</strong> ৳${salary}</p>
    <p><span class="material-icons text-info">event</span> <strong>Date:</strong> ${created}</p>
    <div class="details-box">
      <span class="material-icons text-success">credit_score</span> <strong>Total Successful Recharge Amount:</strong> ৳${successAmt}
    </div>
    <div class="details-box">
      <span class="material-icons text-warning">warning</span> <strong>Total Failed Recharge Amount:</strong> ৳${failAmt}
    </div>
    <div class="details-box">
      <span class="material-icons text-success">done</span> <strong>✅ Successful Recharge Details:</strong>
      <pre>${successList}</pre>
    </div>
    <div class="details-box">
      <span class="material-icons text-danger">close</span> <strong>❌ Failed Recharge Details:</strong>
      <pre>${failList}</pre>
    </div>
    <div class="details-box">
      <span class="material-icons text-success">check_circle</span> <strong>Successful Bet User IDs:</strong>
      <pre>${sbids}</pre>
    </div>
    <div class="details-box">
      <span class="material-icons text-danger">cancel</span> <strong>Failed Bet User IDs:</strong>
      <pre>${fbids}</pre>
    </div>
  `;
  document.getElementById("agentDetailBody").innerHTML = html;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
