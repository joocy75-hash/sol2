<?php
session_start();
include("conn.php");

// 🔐 Secure session
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}

// ➕ Add Agent Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serial'])) {
    $userid = mysqli_real_escape_string($conn, $_POST['serial']);
    $salarypercent = floatval($_POST['salarypercent'] ?? 3);
    $salary = floatval($_POST['salary'] ?? 0);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $minrecharge = intval($_POST['minrecharge'] ?? 300);
    $minbet = intval($_POST['minbet'] ?? 3);
    $minreferrals = intval($_POST['minreferrals'] ?? 3);
    $createdate = date("Y-m-d H:i:s");

    $check = mysqli_query($conn, "SELECT mobile FROM shonu_subjects WHERE id='$userid'");
    if (mysqli_num_rows($check) === 1) {
        $user = mysqli_fetch_assoc($check);
        $mobile = $user['mobile'];
        $exists = mysqli_query($conn, "SELECT id FROM tb_agent WHERE userid='$userid' AND status='1'");

        if (mysqli_num_rows($exists) === 0) {
            $insert = mysqli_query($conn, "
                INSERT INTO tb_agent (userid, mobile, salary, salarypercent, type, minrecharge, minbet, minreferrals, createdate, status)
                VALUES ('$userid', '$mobile', '$salary', '$salarypercent', '$type', '$minrecharge', '$minbet', '$minreferrals', '$createdate', 1)
            ");
            $_SESSION['msg'] = $insert ? "✅ Agent Added Successfully." : "❌ Failed to add agent.";
        } else {
            $_SESSION['msg'] = "⚠️ Agent already exists.";
        }
    } else {
        $_SESSION['msg'] = "❌ Invalid UID.";
    }
    header("Location: agents_management.php");
    exit;
}

// 📊 Fetch Stats
$total_agents = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_agent"));
$inactive_agents = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_agent WHERE status != 1"));
$total_salary = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(salary) AS total FROM dailysalary"))['total'] ?? 0;
$all_agents = mysqli_query($conn, "SELECT * FROM tb_agent ORDER BY id DESC");

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// ❌ Delete Agent
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM tb_agent WHERE id = $delId");
    $_SESSION['msg'] = "❌ Agent deleted successfully.";
    header("Location: agents_management.php");
    exit;
}

// ✏️ Update Agent via Modal
if (isset($_POST['update_id'])) {
    $updateId = intval($_POST['update_id']);
    $salary = floatval($_POST['edit_salary']);
    $percent = floatval($_POST['edit_percent']);
    $type = mysqli_real_escape_string($conn, $_POST['edit_type']);
    $minrecharge = intval($_POST['edit_minrecharge']);
    $minbet = intval($_POST['edit_minbet']);
    $minreferrals = intval($_POST['edit_minreferrals']);

    $update = mysqli_query($conn, "
        UPDATE tb_agent 
        SET salary='$salary', salarypercent='$percent', type='$type', minrecharge='$minrecharge', minbet='$minbet', minreferrals='$minreferrals' 
        WHERE id='$updateId'
    ");

    $_SESSION['msg'] = $update ? "✅ Agent updated successfully." : "❌ Failed to update agent.";
    header("Location: agents_management.php");
    exit;
}
?>


<?php include 'header.php'; ?>





<body>
<div class="container py-4">
  <h3 class="mb-4">🧑‍💼 Agent Management Panel</h3>

  <?php if ($msg): ?>
    <div class="alert alert-info shadow-sm"><?= $msg ?></div>
  <?php endif; ?>

  <!-- 🔷 Top Summary -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card bg-primary text-white shadow-sm">
        <div class="card-body summary-card">
          <div><i class="bi bi-people-fill card-icon"></i> Total Agents</div>
          <div class="fs-4"><?= $total_agents ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-danger text-white shadow-sm">
        <div class="card-body summary-card">
          <div><i class="bi bi-person-x card-icon"></i> Inactive Agents</div>
          <div class="fs-4"><?= $inactive_agents ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-success text-white shadow-sm">
        <div class="card-body summary-card">
          <div><i class="bi bi-cash-coin card-icon"></i> Total Salary</div>
          <div class="fs-4">৳<?= number_format($total_salary) ?></div>
        </div>
      </div>
    </div>
  </div>
  
  
  
 <!-- 📜 Agent Salary Rules -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="mb-3">📜 Agent Salary Eligibility Rules</h5>

    <ul class="list-group list-group-flush">

      <li class="list-group-item">
        👥 <b>Minimum Referrals:</b> Agent must have at least <b>Min Referrals</b> active users linked under them.
      </li>

      <li class="list-group-item">
        💰 <b>Salary % (Commission):</b> Salary is calculated as per <b>Salary %</b> setting from database (e.g., 3%, 5%, etc.).
      </li>

      <li class="list-group-item">
        🪙 <b>Fixed Salary ৳:</b> If a fixed <b>Salary ৳</b> amount is set, agent will get that amount additionally, irrespective of referrals' performance. (Example: ৳100/day fixed)
      </li>

      <li class="list-group-item">
        🔋 <b>Recharge Condition:</b> Referred users must do at least one successful recharge to count.
      </li>

      <li class="list-group-item">
        🎯 <b>Betting Condition:</b> Each referred user must complete betting of at least <b>৳300</b> (or as configured).
      </li>

      <li class="list-group-item">
        📆 <b>Activity Validity:</b> Only <b>yesterday's</b> recharge and betting activities are counted.
      </li>

      <li class="list-group-item">
        ❌ <b>No Salary (Commission Part Only):</b> If minimum referrals, recharge, or betting condition is not met, <b>only fixed Salary ৳</b> will be given (if set), otherwise <b>৳0</b>.
      </li>

    </ul>

    <div class="mt-4">
      <h6>🔹 Example 1 (Only % Commission):</h6>
      <p class="mb-1">
        Suppose an agent has <b>Min Referrals = 3</b>, <b>Salary % = 5%</b>, and <b>Min Recharge = ৳300</b>.
      </p>
      <p class="mb-3">
        If 3 referred users recharge and bet successfully, and their total recharge is ৳10,000,<br>
        then salary = (৳10,000 × 5%) = <b>৳500</b>.
      </p>

      <h6>🔹 Example 2 (Fixed Salary + Commission):</h6>
      <p class="mb-1">
        Suppose an agent has <b>Fixed Salary ৳ = 100</b> and <b>Salary % = 5%</b>.
      </p>
      <p class="mb-0">
        Even if no referrals perform, agent still gets ৳100 fixed.<br>
        If referrals perform well, they will earn extra commission on top.
      </p>
    </div>

  </div>
</div>



 <!-- 🧾 Add Agent Form -->
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="mb-3">➕ Add New Agent</h5>
    <form method="POST" class="row g-3">

      <div class="col-md-2">
        <label class="form-label">User ID</label>
        <input type="number" name="serial" class="form-control" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Percent (%)</label>
        <input type="number" name="salarypercent" class="form-control" value="3" min="1" max="100" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Salary ৳</label>
        <input type="number" name="salary" class="form-control" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Salary Type</label>
        <select name="type" class="form-select" required>
          <option value="" disabled selected>Select</option>
          <option value="day">Daily</option>
          <option value="week">Weekly</option>
          <option value="month">Monthly</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Min Recharge ৳</label>
        <input type="number" name="minrecharge" class="form-control" value="300" min="0" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Min Bet Users</label>
        <input type="number" name="minbet" class="form-control" value="3" min="0" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Min Referrals</label>
        <input type="number" name="minreferrals" class="form-control" value="3" min="0" required>
      </div>

      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100" type="submit">Add Agent</button>
      </div>

    </form>
  </div>
</div>




<!-- 📋 Agent Records Table -->
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3">📄 Agent Records</h5>

    <div class="table-responsive">
      <table class="table table-bordered table-hover table-striped table-sm text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>#ID</th>
            <th>User ID</th>
            <th>Mobile</th>
            <th>Salary ৳</th>
            <th>%</th>
            <th>Min Referrals</th> <!-- ✅ New Column -->
            <th>Type</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($all_agents) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($all_agents)): ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['userid'] ?></td>
                <td><?= $row['mobile'] ?></td>
                <td>৳<?= number_format($row['salary']) ?></td>
                <td><span class="badge bg-info text-dark"><?= $row['salarypercent'] ?>%</span></td>
                <td>
                  <span class="badge bg-warning text-dark"><?= $row['minreferrals'] ?></span> <!-- ✅ Highlight min referrals -->
                </td>
                <td><?= ucfirst($row['type']) ?></td>
                <td>
                  <?php if ($row['status'] == '1'): ?>
                    <span class="badge bg-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
                <td><?= date("d M Y, H:i", strtotime($row['createdate'])) ?></td>
                <td>
                  <!-- 🗑 Delete Button -->
                  <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this agent?');" class="btn btn-sm btn-danger">
                    <i class="mdi mdi-delete"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-muted">No agents found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>
<style>

 .card.bg-primary.text-white.shadow-sm {
    background-color: #000000 !important; /* pure black background */
    color: #ffffff !important;            /* white text */
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1) !important; /* subtle light shadow */
    border-radius: 0.5rem;                /* thoda rounded corner */
    padding: -5px !important;   /* thoda kam padding */
}

.card.bg-primary.text-white.shadow-sm .card-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #00d8ff;  /* ek cool cyan-ish accent for icon */
}

.summary-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
}

</style>



<!-- 🧩 Bootstrap JS if not included already -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>



</body>
</html>
