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

<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-sm-12 mb-4 mb-xl-0">
        <div class="card shadow-sm border-0 rounded p-4 d-flex flex-md-row flex-column justify-content-between align-items-center" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
          <div>
            <h4 class="font-weight-bold mb-1">👋 Hi, Sol-0203 Welcome back!</h4>
            <p class="mb-0">
              <i class="material-icons align-middle" style="font-size: 18px; vertical-align: middle;">event</i>
              <?php echo date("F d, Y"); ?>
            </p>
          </div>

          <div class="d-flex align-items-center gap-3">
            <div class="text-white text-end me-3" style="font-size: 12px; line-height: 1.5;">
              <div><i class="material-icons" style="font-size: 16px; vertical-align: middle;">dns</i> IP: <?php echo $masked_ip; ?></div>
              <div><i class="material-icons" style="font-size: 16px; vertical-align: middle;">memory</i> PHP: <?php echo phpversion(); ?></div>
              <div><i class="material-icons" style="font-size: 16px; vertical-align: middle;">storage</i> <?php echo $_SERVER['SERVER_NAME']; ?></div>
            </div>
            <img src="https://Sol-0203.com/logos/Picsart_25-08-30_16-43-45-598.png" width="50" height="50" alt="User Avatar" style="border-radius: 50%;">
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
    
    
    
    <div class="row g-3 mt-3">
  <!-- Today User Join -->
<div class="col-md-3">
  <div class="card shadow-sm border-0 rounded-3 p-3" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <p class="mb-1 fw-semibold">Today User Join</p>
        <h4 class="fw-bold mb-0">
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
      </div>
      <div>
        <i class="material-icons" style="font-size: 30px; opacity: 0.7;">person_add</i>
      </div>
    </div>
    <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
  </div>
</div>


  <!-- Today's Recharge -->
<div class="col-md-3">
  <div class="card shadow-sm border-0 rounded-3 p-3" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <p class="mb-1 fw-semibold">Today's Recharge</p>
        <h4 class="fw-bold mb-0">
          <?php
            // Ensure current date is set
            $curdate = date('Y-m-d');

            // Query to calculate today's total recharge excluding demo users
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

            // Fetch and show the result
            if ($result && mysqli_num_rows($result) > 0) {
              $row = mysqli_fetch_assoc($result);
              echo "৳" . number_format($row["pending"] ?? 0, 0);
            } else {
              echo "৳0";
            }
          ?>
        </h4>
      </div>
      <div>
        <i class="material-icons" style="font-size: 36px; opacity: 0.6;">attach_money</i>
      </div>
    </div>
    <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
  </div>
</div>





<!-- Today's Withdrawal -->
<div class="col-md-3">
  <div class="card shadow-sm border-0 rounded-3 p-3" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <p class="mb-1 fw-semibold">Today's Withdrawal</p>
        <h4 class="fw-bold mb-0">
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
      </div>
      <div>
        <i class="material-icons" style="font-size: 30px; opacity: 0.7;">money_off</i>
      </div>
    </div>
    <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
  </div>
</div>

<div class="col-md-3">
  <div class="card shadow-sm border-0 rounded-3 p-3" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <p class="mb-1 fw-semibold">Today's Total Bet</p>
        <h4 class="fw-bold mb-0">
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
      </div>
      <div>
        <i class="material-icons" style="font-size: 32px; opacity: 0.7;">sports_esports</i>
      </div>
    </div>
    <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
  </div>
  </div>
  
  
<!-- Box 5: Total Win -->
<div class="col-md-3 mt-3"> <!-- 👈 added margin-top -->
  <div class="card shadow-sm border-0 rounded-3 p-3" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <p class="mb-1 fw-semibold">Today's Total Win</p>
        <h4 class="fw-bold mb-0">
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
      </div>
      <div>
        <i class="material-icons" style="font-size: 30px; opacity: 0.7;">emoji_events</i>
      </div>
    </div>
    <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
  </div>
</div>


<div class="col-md-3 mt-3"> <!-- 👈 added margin-top -->
  <a href="manage_walletuser.php" style="text-decoration: none;">
    <div class="card shadow-sm border-0 rounded-3 p-3" style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
          <p class="mb-1 fw-semibold">User Balance</p>
          <h4 class="fw-bold mb-0">
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
        </div>
        <div>
          <i class="material-icons" style="font-size: 30px; opacity: 0.7;">account_balance_wallet</i>
        </div>
      </div>
      <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
    </div>
  </a>
</div>


<div class="col-md-3 mt-3"> <!-- add mt-3 if it's the 5th or 6th box -->
  <a href="manage_user.php" style="text-decoration: none;">
    <div class="card shadow-sm border-0 rounded-3 p-3"
         style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
          <p class="mb-1 fw-semibold">Total Users</p>
          <h4 class="fw-bold mb-0">
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
        </div>
        <div>
          <i class="material-icons" style="font-size: 30px; opacity: 0.7;">people</i>
        </div>
      </div>
      <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
    </div>
  </a>
</div>








<!-- Same HTML Layout -->
<div class="col-md-3 mt-3">
  <div class="card shadow-sm border-0 rounded-3 p-3"
       style="background: linear-gradient(to right, #df0000, #000000); color: #fff;">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <p class="mb-1 fw-semibold">Today's Profit</p>
        <h4 class="fw-bold mb-0">
          ৳<?= number_format($amount ?? 0, 2); ?>
        </h4>
      </div>
      <div>
        <i class="material-icons" style="font-size: 30px; opacity: 0.7;">trending_up</i>
      </div>
    </div>
    <div class="panel-footer mt-2">
        <span class="pull-left" style="color: white;">See in Detail</span>
        <div class="clearfix"></div>
      </div>
  </div>
</div>



<div class="setting-section">
  <!-- Card 1 -->
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


  

<!-- ✅ Card 1: Need to Deposit First -->
<div class="setting-card">
  <div class="card-left">
    <span class="material-icons icon">account_balance</span>
    <div>
      <div class="title">Need to Deposit First</div>
      <div class="desc">
        'Required' means players must deposit to play, while 'Optional' allows play without deposit, giving flexibility to join as desired.
      </div>
    </div>
  </div>
  <div class="card-right">
    <form method="post">
      <!-- Always sends allowbet=1 unless checkbox overrides -->
      <input type="hidden" name="allowbet" value="1">
      <label class="switch">
        <input type="checkbox" name="allowbet" value="0" onchange="this.form.submit()" <?= $allowbet == 0 ? 'checked' : '' ?>>
        <span class="slider round"></span>
      </label>
    </form>
    <span class="status"><?= $allowbet == 0 ? 'ON' : 'OFF' ?></span>
  </div>
</div>


<!-- ✅ Card 2: Wingo 30s Same Trend -->
<div class="setting-card">
  <div class="card-left">
    <span class="material-icons icon">sports_esports</span>
    <div>
      <div class="title">Wingo  Same Trend</div>
      <div class="desc">Toggle to enable or disable Wingo Game Mode.</div>
    </div>
  </div>
  <div class="card-right">
    <form method="post">
      <!-- inactive = default (OFF) -->
      <input type="hidden" name="wingo30" value="inactive">
      <label class="switch">
        <input type="checkbox" name="wingo30" value="active" onchange="this.form.submit()" <?= $wingo30status === 'active' ? 'checked' : '' ?>>
        <span class="slider round"></span>
      </label>
    </form>
    <span class="status"><?= $wingo30status === 'active' ? 'ON' : 'OFF' ?></span>
  </div>
</div>






<!-- Wingo 1min / 3min / 5min Coming Soon Card (simple and compact) -->
<div class="setting-card">
  <div class="card-left">
    <span class="material-icons icon">schedule</span>
    <div>
      <div class="title">K3, 5D &Trx Same Trend</div>
      <div class="desc">Coming Soon</div>
    </div>
  </div>
  <div class="card-right">
    <span class="status">Coming</span>
  </div>
</div>












		  
		  <?php } ?>
		</div>
		
		<!-- Floating Transaction Button -->
<div id="floating-transaction-icon" onclick="toggleTransactionPanel()">
    <i class="fa fa-exchange"></i> <!-- Transaction Icon -->
</div>

<!-- 🎮 Section Heading -->
<div class="w-100 mb-3">
  <h4 class="font-weight-bold text-dark">🎮 Game Settings</h4>
</div>
 
 
<!-- Wingo Setting -->
<div class="setting-card">
  <form method="POST">
    <h5>Wingo 30Sec Process Type</h5>
    <select name="wingo_type" class="form-control mb-2">
      <option value="highest_bet_wins" <?= ($wingo == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
      <option value="random" <?= ($wingo == "random") ? "selected" : "" ?>>Random</option>
      <option value="default" <?= ($wingo == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">Save</button>
  </form>
</div>

<!-- K3 Setting -->
<div class="setting-card">
  <form method="POST">
    <h5>K3 1Min Process Type</h5>
    <select name="k3_type" class="form-control mb-2">
      <option value="highest_bet_wins" <?= ($k3 == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
      <option value="random" <?= ($k3 == "random") ? "selected" : "" ?>>Random</option>
      <option value="default" <?= ($k3 == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">Save</button>
  </form>
</div>

<!-- 5D Setting -->
<div class="setting-card">
  <form method="POST">
    <h5>5D 1Min Process Type</h5>
    <select name="d5_type" class="form-control mb-2">
      <option value="highest_bet_wins" <?= ($d5 == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
      <option value="random" <?= ($d5 == "random") ? "selected" : "" ?>>Random</option>
      <option value="default" <?= ($d5 == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">Save</button>
  </form>
</div>

<!-- Trx Setting -->
<div class="setting-card">
  <form method="POST">
    <h5>Trx 1Min Process Type</h5>
    <select name="trx_type" class="form-control mb-2">
      <option value="highest_bet_wins" <?= ($trx == "highest_bet_wins") ? "selected" : "" ?>>Higher Bet Wins</option>
      <option value="random" <?= ($trx == "random") ? "selected" : "" ?>>Random</option>
      <option value="default" <?= ($trx == "default") ? "selected" : "" ?>>Higher Bet Lose</option>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">Save</button>
  </form>
</div>

<style>





 .setting-section {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin: 30px auto;
  max-width: 1200px;
  justify-content: center;
}

.setting-card {
  background: #f5f6fa;
  border-radius: 12px;
  padding: 20px 25px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  flex: 1 1 500px;
  min-width: 300px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-left {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  flex: 1;
}

.card-left .icon {
  font-size: 28px;
  color: #001f5b;
  margin-top: 3px;
}

.title {
  font-weight: 700;
  font-size: 16px;
  color: #001f5b;
  margin-bottom: 5px;
}

.desc {
  font-size: 13px;
  color: #333;
}

.card-right {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 80px;
}

/* Toggle */
.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
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
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #6c3ff2;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

.slider.round {
  border-radius: 34px;
}

.status {
  margin-top: 5px;
  font-size: 12px;
  color: #333;
}
   


/* Floating Transaction Icon */
#floating-transaction-icon {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #df0000;
    color: white;
    padding: 15px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    transition: all 0.3s ease-in-out;
}
#floating-transaction-icon:hover {
    background: #e64a19;
}

/* Transaction Panel */
#transaction-panel {
    position: fixed;
    bottom: 70px;
    right: 20px;
    width: 300px;
    height: 400px;
    background: white;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    z-index: 10000;
}

/* Header */
.transaction-header {
    background: #df0000;
    color: white;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}
.transaction-header button {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

/* Content */
#transaction-content {
    padding: 10px;
    overflow-y: auto;
    height: 350px;
}
.transaction-item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
.transaction-item.success { color: green; }
.transaction-item.failed { color: red; }

</style>


<!-- ✅ Notification Popup CSS -->
<style>
#notificationPopup {
  position: fixed;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  width: 480px;
  max-height: 560px;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 15px 50px rgba(0,0,0,0.25);
  overflow: hidden;
  display: none;
  border-top: 5px solid #007bff;
  animation: slideUp 0.4s ease-in-out;
}

#notificationPopup img {
  width: 100%;
  max-height: 240px;
  object-fit: contain;
  border-bottom: 1px solid #eee;
}

#notificationPopup .content {
  padding: 20px;
  max-height: 280px;
  overflow-y: auto;
  font-family: 'Segoe UI', sans-serif;
}

#notificationPopup .content h6 {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 10px;
  color: #222;
}

#notificationPopup .content p {
  font-size: 15px;
  color: #444;
  line-height: 1.5;
  margin-bottom: 12px;
}

#notificationPopup .close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 22px;
  font-weight: bold;
  cursor: pointer;
  color: #555;
}

#notifLink {
  display: inline-block;
  background-color: #007bff;
  color: white !important;
  padding: 10px 20px;
  font-size: 15px;
  border-radius: 8px;
  text-decoration: none;
  margin-top: 5px;
  transition: 0.3s;
}
#notifLink:hover {
  background-color: #0056b3;
}

@keyframes slideUp {
  from {
    transform: translate(-50%, 30px);
    opacity: 0;
  }
  to {
    transform: translate(-50%, 0px);
    opacity: 1;
  }
}
</style>


<!-- Transaction Chat Panel -->
<div id="transaction-panel">
    <div class="transaction-header">
        <h4>Live Transactions</h4>
        <button onclick="toggleTransactionPanel()">—</button>
    </div>
    <div id="transaction-content">
        <p>Loading transactions...</p>
    </div>
</div>

<!-- ✅ Notification Popup HTML -->
<div id="notificationPopup">
  <span class="close-btn" onclick="hideNotification()">&times;</span>
  <img id="notifImage" src="" alt="Image" />
  <div class="content">
    <h6 id="notifTitle"></h6>
    <p id="notifMessage" style="font-size: 14px;"></p>
    <p id="notifCost" style="font-weight: 500;"></p>
    <a id="notifLink" href="#" target="_blank" class="btn btn-sm btn-primary mt-2" style="display:none;">Contact Developer</a>
  </div>
</div>

		
		
		
		
		
  
  <script>
    function checkForUpdates() {
        $.ajax({
            url: 'check_update.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.update_available) {
                    $("#bell-icon").addClass("text-warning");
                    $("#notification-count").text("1").show();
                    $("#update-list").html('<a href="#" class="dropdown-item" onclick="applyUpdate(\'' + response.file_url + '\')">Update to v' + response.version + '</a>');
                } else {
                    $("#bell-icon").removeClass("text-warning");
                    $("#notification-count").hide();
                    $("#update-list").html('<p class="dropdown-item">No new updates</p>');
                }
            }
        });
    }

    function applyUpdate(fileUrl) {
        $.ajax({
            url: 'apply_update.php',
            type: 'POST',
            data: { file_url: fileUrl },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                checkForUpdates(); // âœ… Update apply hone ke baad dobara check karo
            }
        });
    }

    setInterval(checkForUpdates, 5000); // âœ… Har 5 sec me update check karega
    
    
</script>

<script>
function toggleTransactionPanel() {
    $("#transaction-panel").fadeToggle(); // ðŸ”¥ Panel ko slide in/out karega
}
</script>


<!-- ✅ Notification Popup JS -->
<script>
const popupKey = "notification_popup_hidden_until";

function hideNotification() {
  const now = new Date();
  const hideUntil = new Date(now.getTime() + 60 * 60 * 1000); // Hide for 1 hour
  localStorage.setItem(popupKey, hideUntil.toISOString());
  document.getElementById("notificationPopup").style.display = "none";
}

async function checkNotification() {
  const now = new Date();
  const hiddenUntil = localStorage.getItem(popupKey);
  if (hiddenUntil && new Date(hiddenUntil) > now) {
    console.log("Notification hidden until:", hiddenUntil);
    return;
  }

  try {
    const response = await fetch("https://updatepanel.jalwagame.gamblly.com/api/get_notifications.php");
    const data = await response.json();
    console.log("Notification response:", data);

    if (data.status && data.notifications.length > 0) {
      const n = data.notifications[0];

      // Title + Message
      document.getElementById("notifTitle").innerText = n.title;
      document.getElementById("notifMessage").innerText = n.message;

      // Image
      if (n.images.length > 0) {
        document.getElementById("notifImage").src = "https://updatepanel.jalwagame.gamblly.com/" + n.images[0];
        document.getElementById("notifImage").style.display = "block";
      } else {
        document.getElementById("notifImage").style.display = "none";
      }

      // Update Cost
      if (n.update_cost && n.update_cost !== "") {
        document.getElementById("notifCost").innerText = "🪙 Update Cost: ৳" + n.update_cost;
        document.getElementById("notifCost").style.display = "block";
      } else {
        document.getElementById("notifCost").style.display = "none";
      }

      // Contact Button
      if (n.contact_link) {
        const btn = document.getElementById("notifLink");
        btn.href = n.contact_link;
        btn.innerText = "💬 Contact Developer";
        btn.style.display = "inline-block";
      } else {
        document.getElementById("notifLink").style.display = "none";
      }

      // Show Notification
      document.getElementById("notificationPopup").style.display = "block";
    }

  } catch (e) {
    console.error("Notification fetch error:", e.message);
  }
}

document.addEventListener("DOMContentLoaded", checkNotification);
</script>


<script>
document.getElementById('allowBetToggle').addEventListener('change', function () {
  let isChecked = this.checked ? 1 : 0;

  fetch("", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "allowbet=" + isChecked
  }).then(() => {
    document.getElementById('statusText').innerText = isChecked === 1 ? "ON" : "OF";
  });
});


document.getElementById("wingo30Toggle").addEventListener("change", function () {
  document.getElementById("statusText").innerText = this.checked ? "ON" : "OFF";
  // You can also call an AJAX request here to update `sametrend.id` to 1 or 0
});



</script>

<footer style="
  width: 100%;
  background: linear-gradient(90deg, #1f4bb9, #0d0e37, #0016b5);
  color: white;
  padding: 12px 0;
  text-align: center;
  font-size: 14px;
  font-weight: 500;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 999;
  font-family: 'Segoe UI', sans-serif;
  animation: rgbflow 8s ease infinite;
  background-size: 300% 300%;
  border-top: 2px solid rgba(255, 255, 255, 0.15);
">
  © <?= date('Y') ?> Sol-0203 | All rights reserved. | <span style="color:rgb(231, 89, 113);">Patented & Protected</span>.
</footer>


<style>
@keyframes rgbflow {
  0%   { background-position: 0% 50%; }
  50%  { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>
 


</body>

</html>