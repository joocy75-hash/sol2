<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
    exit;
}

date_default_timezone_set("Asia/Dhaka");
include("conn.php");

// ✅ Process form submission for allowbet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['allowbet'])) {
        $allow = ($_POST['allowbet'] == 0) ? 0 : 1;
        // 👇 Change id from 0 to 1
        mysqli_query($conn, "UPDATE web_setting SET allowbet = $allow WHERE id = 1");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // ✅ Handle "Wingo 30s Same Trend" Toggle
    if (isset($_POST['wingo30'])) {
        $wingo30 = $_POST['wingo30'] === 'active' ? 'active' : 'inactive';
        mysqli_query($conn, "UPDATE sametrend SET status = '$wingo30' LIMIT 1");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ✅ Get current allowbet value (from id = 1)
$allowbet = 1; // default
$res = mysqli_query($conn, "SELECT allowbet FROM web_setting WHERE id = 1");
if ($row = mysqli_fetch_assoc($res)) {
    $allowbet = $row['allowbet'];
}

// ✅ Get current Wingo 30s Same Trend status
$wingo30status = 'inactive';
$wingoRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM sametrend LIMIT 1"));
if ($wingoRow) {
    $wingo30status = $wingoRow['status'];
}

// Default values
$wingo = $k3 = $d5 = $trx = 'default';

// ✅ Handle POST request for any game
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['wingo_type'])) {
        $pt = $_POST['wingo_type'];
        mysqli_query($conn, "UPDATE web_setting SET process_type_wingo = '$pt' WHERE id = 1");
    }
    if (isset($_POST['k3_type'])) {
        $pt = $_POST['k3_type'];
        mysqli_query($conn, "UPDATE web_setting SET process_type_k3 = '$pt' WHERE id = 1");
    }
    if (isset($_POST['d5_type'])) {
        $pt = $_POST['d5_type'];
        mysqli_query($conn, "UPDATE web_setting SET process_type_5d = '$pt' WHERE id = 1");
    }
    if (isset($_POST['trx_type'])) {
        $pt = $_POST['trx_type'];
        mysqli_query($conn, "UPDATE web_setting SET process_type_trx = '$pt' WHERE id = 1");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ✅ Fetch current values
$q = mysqli_query($conn, "SELECT process_type_wingo, process_type_k3, process_type_5d, process_type_trx FROM web_setting WHERE id = 1");
if ($row = mysqli_fetch_assoc($q)) {
    $wingo = $row['process_type_wingo'];
    $k3 = $row['process_type_k3'];
    $d5 = $row['process_type_5d'];
    $trx = $row['process_type_trx'];
}

?>

<?php
$curdate = date('Y-m-d');

$tables = [
  "bajikattuttate",
  "bajikattuttate_drei",
  "bajikattuttate_funf",
  "bajikattuttate_zehn",
  "bajikattuttate_kemuru",
  "bajikattuttate_kemuru_drei",
  "bajikattuttate_kemuru_funf",
  "bajikattuttate_kemuru_zehn",
  "bajikattuttate_aidudi",
  "bajikattuttate_aidudi_drei",
  "bajikattuttate_aidudi_funf",
  "bajikattuttate_aidudi_zehn"
];

$asila = 0; // total bet
$gala = 0;  // total win

foreach ($tables as $table) {
  // Total Bet
  $query_bet = "SELECT SUM(sesabida) AS total FROM `$table`
                WHERE DATE(tiarikala) = '$curdate'
                AND (byabaharkarta NOT IN (
                    SELECT balakedara FROM demo WHERE sthiti = 1
                ) OR NOT EXISTS (
                    SELECT 1 FROM demo WHERE sthiti = 1
                ))";
  $res_bet = mysqli_fetch_assoc(mysqli_query($conn, $query_bet));
  $asila += $res_bet['total'] ?? 0;

  // Total Win (gagner only)
  $query_win = "SELECT SUM(sesabida) AS total FROM `$table`
                WHERE phalaphala = 'gagner'
                AND DATE(tiarikala) = '$curdate'
                AND (byabaharkarta NOT IN (
                    SELECT balakedara FROM demo WHERE sthiti = 1
                ) OR NOT EXISTS (
                    SELECT 1 FROM demo WHERE sthiti = 1
                ))";
  $res_win = mysqli_fetch_assoc(mysqli_query($conn, $query_win));
  $gala += $res_win['total'] ?? 0;
}

$amount = $asila - $gala;
?>

<?php include 'header.php'; ?>

<?php
// Get server IP and mask it (optional)
$server_ip = $_SERVER['SERVER_ADDR'];
$ip_parts = explode('.', $server_ip);
$masked_ip = $ip_parts[0] . '.' . $ip_parts[1] . '.***.***';
?>

<style>
:root {
  --primary-blue: #1e40af;
  --secondary-blue: #3b82f6;
  --light-blue: #dbeafe;
  --accent-blue: #2563eb;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --dark: #1f2937;
  --light: #f8fafc;
  --gray: #6b7280;
  --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  --glass-bg: rgba(255, 255, 255, 0.7);
  --glass-border: rgba(255, 255, 255, 0.2);
  --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
}

body {
  font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #f0f4ff 0%, #e6f0ff 100%);
  color: #334155;
  min-height: 100vh;
}

.dashboard-header {
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  border-radius: 20px;
  box-shadow: var(--card-shadow);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
  position: relative;
  overflow: hidden;
}

.dashboard-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
  background-size: cover;
  pointer-events: none;
}

.stat-card {
  background: var(--glass-bg);
  border-radius: 16px;
  border: 1px solid var(--glass-border);
  transition: all 0.3s ease;
  box-shadow: var(--glass-shadow);
  height: 100%;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--hover-shadow);
  border-color: rgba(59, 130, 246, 0.3);
}

.stat-card.user-join { border-left: 4px solid #3b82f6; }
.stat-card.recharge { border-left: 4px solid #10b981; }
.stat-card.withdrawal { border-left: 4px solid #f59e0b; }
.stat-card.bet { border-left: 4px solid #8b5cf6; }
.stat-card.win { border-left: 4px solid #06b6d4; }
.stat-card.balance { border-left: 4px solid #ef4444; }
.stat-card.total-users { border-left: 4px solid #6366f1; }
.stat-card.profit { border-left: 4px solid #84cc16; }

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.stat-icon.user-join { background: #dbeafe; color: #1e40af; }
.stat-icon.recharge { background: #dcfce7; color: #166534; }
.stat-icon.withdrawal { background: #fef3c7; color: #92400e; }
.stat-icon.bet { background: #f3e8ff; color: #7c3aed; }
.stat-icon.win { background: #cffafe; color: #0e7490; }
.stat-icon.balance { background: #fee2e2; color: #dc2626; }
.stat-icon.total-users { background: #e0e7ff; color: #4338ca; }
.stat-icon.profit { background: #ecfccb; color: #4d7c0f; }

.setting-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 20px;
  margin: 30px 0;
}

.setting-card {
  background: var(--glass-bg);
  border-radius: 16px;
  padding: 24px;
  box-shadow: var(--glass-shadow);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.3s ease;
  border: 1px solid var(--glass-border);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.setting-card:hover {
  box-shadow: var(--hover-shadow);
  transform: translateY(-5px);
  border-color: rgba(59, 130, 246, 0.3);
}

.card-left {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  flex: 1;
}

.card-left .icon {
  font-size: 28px;
  color: var(--primary-blue);
  margin-top: 2px;
  background: rgba(59, 130, 246, 0.1);
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.title {
  font-weight: 600;
  font-size: 16px;
  color: var(--dark);
  margin-bottom: 6px;
}

.desc {
  font-size: 14px;
  color: var(--gray);
  line-height: 1.5;
}

.card-right {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 80px;
}

/* Enhanced Toggle Switch */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: .4s;
  border-radius: 34px;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

input:checked + .slider {
  background-color: var(--success);
}

input:checked + .slider:before {
  transform: translateX(24px);
}

.status {
  margin-top: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--gray);
}

input:checked + .slider + .status {
  color: var(--success);
}

/* Game Settings Cards */
.game-settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin: 25px 0;
}

.game-setting-card {
  background: var(--glass-bg);
  border-radius: 16px;
  padding: 20px;
  box-shadow: var(--glass-shadow);
  border: 1px solid var(--glass-border);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.game-setting-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--hover-shadow);
  border-color: rgba(59, 130, 246, 0.3);
}

.game-setting-card h5 {
  color: var(--primary-blue);
  font-weight: 600;
  margin-bottom: 15px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.game-setting-card .form-control {
  border-radius: 10px;
  border: 1px solid #d1d5db;
  padding: 10px 12px;
  margin-bottom: 12px;
  background: rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
}

.game-setting-card .form-control:focus {
  border-color: var(--primary-blue);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: white;
}

.game-setting-card .btn {
  border-radius: 10px;
  padding: 10px 20px;
  font-weight: 500;
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  border: none;
  transition: all 0.3s ease;
}

.game-setting-card .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
}

/* Floating Action Button */
.floating-action-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 25px rgba(30, 64, 175, 0.3);
  cursor: pointer;
  z-index: 1000;
  transition: all 0.3s ease;
  font-size: 24px;
  border: none;
}

.floating-action-btn:hover {
  transform: scale(1.1);
  background: linear-gradient(135deg, var(--secondary-blue), var(--accent-blue));
  box-shadow: 0 15px 30px rgba(30, 64, 175, 0.4);
}

/* Footer */
.dashboard-footer {
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  margin-top: 40px;
  border-radius: 20px 20px 0 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid var(--glass-border);
}

/* Responsive */
@media (max-width: 768px) {
  .setting-section {
    grid-template-columns: 1fr;
  }
  
  .setting-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .card-right {
    align-self: flex-end;
  }
  
  .game-settings-grid {
    grid-template-columns: 1fr;
  }
}

/* Badge Styles */
.badge-pill {
  border-radius: 20px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.9);
  color: var(--primary-blue);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Section Headers */
.section-header {
  color: var(--dark);
  font-weight: 600;
  margin: 30px 0 20px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid rgba(226, 232, 240, 0.7);
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 0.5);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.5);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.7);
}

/* Animation for cards */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-card, .setting-card, .game-setting-card {
  animation: fadeInUp 0.5s ease forwards;
}

/* Stagger animation for cards */
.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
.stat-card:nth-child(5) { animation-delay: 0.5s; }
.stat-card:nth-child(6) { animation-delay: 0.6s; }
.stat-card:nth-child(7) { animation-delay: 0.7s; }
.stat-card:nth-child(8) { animation-delay: 0.8s; }

/* Server info badges */
.server-info {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}

.server-badge {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  padding: 6px 12px;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 5px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
</style>

<div class="main-panel">
  <div class="content-wrapper">
    <!-- Welcome Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="dashboard-header p-4 text-white">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="text-center text-md-start mb-3 mb-md-0">
              <h4 class="fw-bold mb-2">👋 Welcome back, Sol-0203 Admin!</h4>
              <p class="mb-0 opacity-90">
                <i class="material-icons align-middle me-1" style="font-size: 18px;">event</i>
                <?php echo date("F d, Y"); ?>
              </p>
              <div class="server-info">
                <div class="server-badge">
                  <i class="material-icons me-1" style="font-size: 14px;">dns</i>
                  <span>IP: <?php echo $masked_ip; ?></span>
                </div>
                <div class="server-badge">
                  <i class="material-icons me-1" style="font-size: 14px;">memory</i>
                  <span>PHP: <?php echo phpversion(); ?></span>
                </div>
                <div class="server-badge">
                  <i class="material-icons me-1" style="font-size: 14px;">storage</i>
                  <span><?php echo $_SERVER['SERVER_NAME']; ?></span>
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-4">
              <div class="bg-white rounded-circle p-2 shadow">
                <img src="https://codehub94.io/favicon.ico" width="45" height="45" alt="Admin Avatar" style="border-radius: 50%;">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php 
      $chkserial = mysqli_query($conn,"SELECT * FROM `nirvahaka_shonu` WHERE `unohs`='".$_SESSION['unohs']."'");
      $salu = mysqli_fetch_array($chkserial);
      $dashboard = $salu['dashboard'];
      if($dashboard == 1){
    ?>

    <!-- Statistics Cards -->
    <div class="row g-3">
      <!-- Today User Join -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card user-join p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted small mb-1">Today User Join</p>
              <h4 class="fw-bold mb-0 text-dark">
                <?php
                  $curdate = date('Y-m-d');
                  $result = mysqli_query($conn, "
                    SELECT COUNT(*) AS total_user 
                    FROM shonu_subjects 
                    WHERE status = 1 
                    AND DATE(createdate) = '$curdate'
                    AND (
                      id NOT IN (
                        SELECT balakedara FROM demo WHERE sthiti = 1
                      ) OR NOT EXISTS (
                        SELECT 1 FROM demo WHERE sthiti = 1
                      )
                    )
                  ");
                  if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    echo $row["total_user"];
                  } else {
                    echo "0";
                  }
                ?>
              </h4>
              <small class="text-muted">Active registrations</small>
            </div>
            <div class="stat-icon user-join">
              <i class="material-icons">person_add</i>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Recharge -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card recharge p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted small mb-1">Today's Recharge</p>
              <h4 class="fw-bold mb-0 text-dark">
                <?php
                  $curdate = date('Y-m-d');
                  $result = mysqli_query($conn, "
                    SELECT SUM(motta) AS pending 
                    FROM thevani 
                    WHERE sthiti = 1 
                    AND DATE(dinankavannuracisi) = '$curdate'
                    AND (
                      balakedara NOT IN (
                        SELECT balakedara FROM demo WHERE sthiti = 1
                      ) OR NOT EXISTS (
                        SELECT 1 FROM demo WHERE sthiti = 1
                      )
                    )
                  ");
                  if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    echo "৳" . number_format($row["pending"] ?? 0, 0);
                  } else {
                    echo "৳0";
                  }
                ?>
              </h4>
              <small class="text-muted">Total deposits</small>
            </div>
            <div class="stat-icon recharge">
              <i class="material-icons">payments</i>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Withdrawal -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card withdrawal p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted small mb-1">Today's Withdrawal</p>
              <h4 class="fw-bold mb-0 text-dark">
                <?php
                  $result = mysqli_query($conn,"SELECT sum(motta) as 'succ_w' FROM hintegedukolli WHERE sthiti = 1 AND balakedara NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(dinankavannuracisi) = DATE('".$curdate."')");
                  if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_array($result);
                    echo "৳" . number_format($row["succ_w"], 0);
                  } else {
                    echo "৳0";
                  }
                ?>
              </h4>
              <small class="text-muted">Total withdrawals</small>
            </div>
            <div class="stat-icon withdrawal">
              <i class="material-icons">savings</i>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Total Bet -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card bet p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted small mb-1">Today's Total Bet</p>
              <h4 class="fw-bold mb-0 text-dark">
                <?php
                  $bet_wingo_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_wingo_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_drei` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_wingo_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_funf` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_wingo_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_zehn` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));

                  $bet_k3_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_k3_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_drei` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_k3_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_funf` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_k3_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_zehn` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));

                  $bet_5d_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_5d_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_drei` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_5d_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_funf` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));
                  $bet_5d_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_zehn` WHERE byabaharkarta NOT IN (SELECT balakedara FROM `demo` WHERE `sthiti`='1') AND DATE(tiarikala) = DATE('".$curdate."')"));

                  $total_bet = $bet_wingo_1['total'] + $bet_wingo_3['total'] + $bet_wingo_5['total'] + $bet_wingo_10['total'] +
                               $bet_k3_1['total'] + $bet_k3_3['total'] + $bet_k3_5['total'] + $bet_k3_10['total'] +
                               $bet_5d_1['total'] + $bet_5d_3['total'] + $bet_5d_5['total'] + $bet_5d_10['total'];

                  echo "৳" . number_format($total_bet, 2);
                ?>
              </h4>
              <small class="text-muted">Total betting volume</small>
            </div>
            <div class="stat-icon bet">
              <i class="material-icons">casino</i>
            </div>
          </div>
        </div>
      </div>

      <!-- Today's Total Win -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card win p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted small mb-1">Today's Total Win</p>
              <h4 class="fw-bold mb-0 text-dark">
                <?php
                  $total_bet = 0;
                  $tables = [
                    "bajikattuttate", "bajikattuttate_drei", "bajikattuttate_funf", "bajikattuttate_zehn",
                    "bajikattuttate_kemuru", "bajikattuttate_kemuru_drei", "bajikattuttate_kemuru_funf", "bajikattuttate_kemuru_zehn",
                    "bajikattuttate_aidudi", "bajikattuttate_aidudi_drei", "bajikattuttate_aidudi_funf", "bajikattuttate_aidudi_zehn"
                  ];
                  foreach ($tables as $table) {
                    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(sesabida) AS total FROM $table WHERE phalaphala = 'gagner' AND byabaharkarta NOT IN (SELECT balakedara FROM demo WHERE sthiti='1') AND DATE(tiarikala) = DATE('$curdate')"));
                    $total_bet += $res['total'];
                  }
                  echo "৳" . number_format($total_bet ?? 0, 2);
                ?>
              </h4>
              <small class="text-muted">Player winnings</small>
            </div>
            <div class="stat-icon win">
              <i class="material-icons">emoji_events</i>
            </div>
          </div>
        </div>
      </div>

      <!-- User Balance -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <a href="manage_walletuser.php" style="text-decoration: none;">
          <div class="stat-card balance p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">User Balance</p>
                <h4 class="fw-bold mb-0 text-dark">
                  <?php
                    $result = mysqli_query($conn, "SELECT SUM(motta) as wallt FROM shonu_kaichila WHERE balakedara NOT IN (SELECT balakedara FROM demo WHERE sthiti='1') AND motta > 0");
                    if (mysqli_num_rows($result) > 0) {
                      $row = mysqli_fetch_array($result);
                      echo "৳" . number_format($row['wallt'] ?? 0, 2);
                    } else {
                      echo "৳0.00";
                    }
                  ?>
                </h4>
                <small class="text-muted">Total wallet balance</small>
              </div>
              <div class="stat-icon balance">
                <i class="material-icons">account_balance_wallet</i>
              </div>
            </div>
          </div>
        </a>
      </div>

      <!-- Total Users -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <a href="manage_user.php" style="text-decoration: none;">
          <div class="stat-card total-users p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-muted small mb-1">Total Users</p>
                <h4 class="fw-bold mb-0 text-dark">
                  <?php
                    $result = mysqli_query($conn, "SELECT COUNT(*) as total_user FROM shonu_subjects WHERE id NOT IN (SELECT balakedara FROM demo WHERE sthiti='1') AND status = 1");
                    if (mysqli_num_rows($result) > 0) {
                      $row = mysqli_fetch_array($result);
                      echo $row['total_user'];
                    } else {
                      echo "0";
                    }
                  ?>
                </h4>
                <small class="text-muted">Registered users</small>
              </div>
              <div class="stat-icon total-users">
                <i class="material-icons">people</i>
              </div>
            </div>
          </div>
        </a>
      </div>

      <!-- Today's Profit -->
      <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="stat-card profit p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted small mb-1">Today's Profit</p>
              <h4 class="fw-bold mb-0 text-dark">
                ৳<?= number_format($amount ?? 0, 2); ?>
              </h4>
              <small class="text-muted">Net profit</small>
            </div>
            <div class="stat-icon profit">
              <i class="material-icons">trending_up</i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Settings Section -->
    <h4 class="section-header mt-5">⚙️ System Settings</h4>
    
    <div class="setting-section">
      <!-- Game Winning Type (Coming Soon) -->
      <div class="setting-card">
        <div class="card-left">
          <span class="material-icons icon">casino</span>
          <div>
            <div class="title">Game Winning Type</div>
            <div class="desc">Random Betting chooses a winner by luck, while Least Bet lets the smallest unique bet win, mixing chance and strategy.</div>
          </div>
        </div>
        <div class="card-right">
          <label class="switch">
            <input type="checkbox" disabled>
            <span class="slider round"></span>
          </label>
          <span class="status" style="color: #999;">Coming Soon</span>
        </div>
      </div>

      <!-- Need to Deposit First -->
      <div class="setting-card">
        <div class="card-left">
          <span class="material-icons icon">account_balance</span>
          <div>
            <div class="title">Need to Deposit First</div>
            <div class="desc">'Required' means players must deposit to play, while 'Optional' allows play without deposit, giving flexibility to join as desired.</div>
          </div>
        </div>
        <div class="card-right">
          <form method="post">
            <input type="hidden" name="allowbet" value="1">
            <label class="switch">
              <input type="checkbox" name="allowbet" value="0" onchange="this.form.submit()" <?= $allowbet == 0 ? 'checked' : '' ?>>
              <span class="slider round"></span>
            </label>
          </form>
          <span class="status"><?= $allowbet == 0 ? 'ON' : 'OFF' ?></span>
        </div>
      </div>

      <!-- Wingo 30s Same Trend -->
      <div class="setting-card">
        <div class="card-left">
          <span class="material-icons icon">sports_esports</span>
          <div>
            <div class="title">Wingo Same Trend</div>
            <div class="desc">Toggle to enable or disable Wingo Game Mode.</div>
          </div>
        </div>
        <div class="card-right">
          <form method="post">
            <input type="hidden" name="wingo30" value="inactive">
            <label class="switch">
              <input type="checkbox" name="wingo30" value="active" onchange="this.form.submit()" <?= $wingo30status === 'active' ? 'checked' : '' ?>>
              <span class="slider round"></span>
            </label>
          </form>
          <span class="status"><?= $wingo30status === 'active' ? 'ON' : 'OFF' ?></span>
        </div>
      </div>

      <!-- K3, 5D & Trx Same Trend (Coming Soon) -->
      <div class="setting-card">
        <div class="card-left">
          <span class="material-icons icon">schedule</span>
          <div>
            <div class="title">K3, 5D & Trx Same Trend</div>
            <div class="desc">Coming Soon - Advanced game mode settings for other games.</div>
          </div>
        </div>
        <div class="card-right">
          <span class="badge badge-pill bg-secondary">Coming</span>
        </div>
      </div>
    </div>

    <!-- Game Settings Section -->
    <h4 class="section-header">🎮 Game Process Settings</h4>
    
    <div class="game-settings-grid">
      <!-- Wingo Setting -->
      <div class="game-setting-card">
        <form method="POST">
          <h5><i class="material-icons" style="font-size: 18px;">casino</i> Wingo 30Sec Process Type</h5>
          <select name="wingo_type" class="form-control mb-3">
            <option value="highest_bet_wins" <?= ($wingo == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
            <option value="random" <?= ($wingo == "random") ? "selected" : "" ?>>Random</option>
            <option value="default" <?= ($wingo == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
          </select>
          <button type="submit" class="btn btn-primary w-100">
            <i class="material-icons me-1" style="font-size: 16px;">save</i> Save Settings
          </button>
        </form>
      </div>

      <!-- K3 Setting -->
      <div class="game-setting-card">
        <form method="POST">
          <h5><i class="material-icons" style="font-size: 18px;">timer</i> K3 1Min Process Type</h5>
          <select name="k3_type" class="form-control mb-3">
            <option value="highest_bet_wins" <?= ($k3 == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
            <option value="random" <?= ($k3 == "random") ? "selected" : "" ?>>Random</option>
            <option value="default" <?= ($k3 == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
          </select>
          <button type="submit" class="btn btn-primary w-100">
            <i class="material-icons me-1" style="font-size: 16px;">save</i> Save Settings
          </button>
        </form>
      </div>

      <!-- 5D Setting -->
      <div class="game-setting-card">
        <form method="POST">
          <h5><i class="material-icons" style="font-size: 18px;">view_in_ar</i> 5D 1Min Process Type</h5>
          <select name="d5_type" class="form-control mb-3">
            <option value="highest_bet_wins" <?= ($d5 == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
            <option value="random" <?= ($d5 == "random") ? "selected" : "" ?>>Random</option>
            <option value="default" <?= ($d5 == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
          </select>
          <button type="submit" class="btn btn-primary w-100">
            <i class="material-icons me-1" style="font-size: 16px;">save</i> Save Settings
          </button>
        </form>
      </div>

      <!-- Trx Setting -->
      <div class="game-setting-card">
        <form method="POST">
          <h5><i class="material-icons" style="font-size: 18px;">currency_bitcoin</i> Trx 1Min Process Type</h5>
          <select name="trx_type" class="form-control mb-3">
            <option value="highest_bet_wins" <?= ($trx == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
            <option value="random" <?= ($trx == "random") ? "selected" : "" ?>>Random</option>
            <option value="default" <?= ($trx == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
          </select>
          <button type="submit" class="btn btn-primary w-100">
            <i class="material-icons me-1" style="font-size: 16px;">save</i> Save Settings
          </button>
        </form>
      </div>
    </div>

    <?php } ?>

   
  <!-- Footer -->
  <footer class="dashboard-footer text-white text-center py-3 mt-5">
    <div class="container">
      <p class="mb-0">
        © <?= date('Y') ?> Sol-0203 | All rights reserved. | 
        <span style="color: #fbbf24;">Patented & Protected</span>
      </p>
    </div>
  </footer>
</div>

<!-- Floating Action Button -->
<button class="floating-action-btn" onclick="toggleTransactionPanel()">
  <i class="material-icons">receipt</i>
</button>

<!-- Transaction Panel (Keep existing functionality) -->
<div id="transaction-panel" style="display: none;">
    <div class="transaction-header bg-primary text-white p-3 rounded-top">
        <h5 class="mb-0">Live Transactions</h5>
        <button class="btn btn-sm btn-light" onclick="toggleTransactionPanel()">—</button>
    </div>
    <div id="transaction-content" class="p-3 bg-white rounded-bottom" style="height: 300px; overflow-y: auto;">
        <p class="text-muted">Loading transactions...</p>
    </div>
</div>

<!-- Notification Popup (Keep existing functionality) -->
<div id="notificationPopup" style="display: none;">
  <!-- Existing notification code -->
</div>

<script>
// Transaction Panel Toggle
function toggleTransactionPanel() {
    const panel = document.getElementById('transaction-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// Add hover effects to cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.stat-card, .setting-card, .game-setting-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add staggered animation delay for cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${(index + 1) * 0.1}s`;
    });
});
</script>
</body>
</html>