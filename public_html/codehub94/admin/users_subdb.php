<?php
session_start();
if($_SESSION['unohs'] == null){
    header("location:../index.php?msg=unauthorized");
    exit;
}

include 'conn.php';

$searchedId = null;
$subordinates = [];
$teamSubordinates = [];
$subStats = $teamStats = [
    'commission' => 0, 'betters' => 0, 'betAmount' => 0,
    'deposits' => 0, 'depositAmount' => 0, 'firstDepositors' => 0, 'firstDepositAmount' => 0
];
$selectedDate = null;
$selectedLevel = 'all';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = intval($_POST['user_id']);
    $searchedId = $userId;
    $selectedDate = !empty($_POST['date']) ? $_POST['date'] : null;
    $selectedLevel = isset($_POST['level']) ? $_POST['level'] : 'all';

    $codeQuery = $conn->prepare("SELECT owncode, code1, code2, code3, code4, code5 FROM shonu_subjects WHERE id = ?");
    $codeQuery->bind_param("i", $userId);
    $codeQuery->execute();
    $codeResult = $codeQuery->get_result();
    
    if ($codeRow = $codeResult->fetch_assoc()) {
        $owncode = $codeRow['owncode'];
        $teamCodes = array_filter([$codeRow['code1'], $codeRow['code2'], $codeRow['code3'], $codeRow['code4'], $codeRow['code5']]);
        
        $subQuery = $conn->prepare("SELECT id FROM shonu_subjects WHERE code = ?");
        $subQuery->bind_param("s", $owncode);
        $subQuery->execute();
        $subResult = $subQuery->get_result();
        while ($row = $subResult->fetch_assoc()) {
            $subordinates[] = $row['id'];
        }

        if (!empty($teamCodes)) {
            $teamCodesStr = implode("','", $teamCodes);
            $teamQuery = $conn->query("SELECT id FROM shonu_subjects WHERE owncode IN ('$teamCodesStr')");
            while ($row = $teamQuery->fetch_assoc()) {
                $teamSubordinates[] = $row['id'];
            }
        }

        function fetchStats($conn, $subordinateIds, $selectedDate, $selectedLevel) {
            $stats = [
                'commission' => 0, 'betters' => 0, 'betAmount' => 0,
                'deposits' => 0, 'depositAmount' => 0, 'firstDepositors' => 0, 'firstDepositAmount' => 0
            ];

            if (!empty($subordinateIds)) {
                $ids = implode(",", $subordinateIds);
                $dateCondition = $selectedDate ? " AND DATE(tiarikala) = '$selectedDate'" : "";
                $levelCondition = $selectedLevel !== 'all' ? " AND prakara = 'LVLCOMM" . intval($selectedLevel) . "'" : "";

                $filteredUsers = [];
                $result = $conn->query("SELECT DISTINCT balakedara FROM vyavahara WHERE balakedara IN ($ids) $dateCondition $levelCondition");
                while ($row = $result->fetch_assoc()) {
                    $filteredUsers[] = $row['balakedara'];
                }

                if (!empty($filteredUsers)) {
                    $filteredIds = implode(",", $filteredUsers);

                    $result = $conn->query("SELECT SUM(ayoga) AS total FROM vyavahara WHERE balakedara IN ($filteredIds) $dateCondition $levelCondition");
                    $stats['commission'] = $result->fetch_assoc()['total'] ?? 0;

                    $betTables = [
                        "bajikattuttate_aidudi", "bajikattuttate_aidudi_drei", "bajikattuttate_aidudi_funf",
                        "bajikattuttate_aidudi_zehn", "bajikattuttate_drei", "bajikattuttate_funf",
                        "bajikattuttate_kemuru", "bajikattuttate_kemuru_drei", "bajikattuttate_kemuru_funf",
                        "bajikattuttate_kemuru_zehn", "bajikattuttate_trx", "bajikattuttate_trx3",
                        "bajikattuttate_trx5", "bajikattuttate_trx10", "bajikattuttate_zehn"
                    ];
                    $betters = [];
                    foreach ($betTables as $table) {
                        $result = $conn->query("SELECT DISTINCT byabaharkarta FROM $table WHERE byabaharkarta IN ($filteredIds) $dateCondition");
                        while ($row = $result->fetch_assoc()) {
                            $betters[$row['byabaharkarta']] = true;
                        }
                        $result = $conn->query("SELECT SUM(wettanzahl) AS total FROM $table WHERE byabaharkarta IN ($filteredIds) $dateCondition");
                        $stats['betAmount'] += $result->fetch_assoc()['total'] ?? 0;
                    }
                    $stats['betters'] = count($betters);

                    $depositDateCondition = $selectedDate ? " AND DATE(dinankavannuracisi) = '$selectedDate'" : "";
                    $result = $conn->query("SELECT COUNT(*) AS count, SUM(motta) AS total FROM thevani WHERE balakedara IN ($filteredIds) AND sthiti = 1 $depositDateCondition");
                    $row = $result->fetch_assoc();
                    $stats['deposits'] = $row['count'] ?? 0;
                    $stats['depositAmount'] = $row['total'] ?? 0;

                    $result = $conn->query("SELECT COUNT(DISTINCT balakedara) AS count, SUM(motta) AS total FROM thevani WHERE balakedara IN ($filteredIds) AND sthiti = 1 $depositDateCondition");
                    $row = $result->fetch_assoc();
                    $stats['firstDepositors'] = $row['count'] ?? 0;
                    $stats['firstDepositAmount'] = $row['total'] ?? 0;
                }
            }
            return $stats;
        }

        $subStats = fetchStats($conn, $subordinates, $selectedDate, 'all');
        $teamStats = fetchStats($conn, $teamSubordinates, $selectedDate, $selectedLevel);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Subordinate Data</title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/feather/feather.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="vendors/flag-icon-css/css/flag-icon.min.css"/>
    <link rel="stylesheet" href="vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="vendors/jquery-bar-rating/fontawesome-stars-o.css">
    <link rel="stylesheet" href="vendors/jquery-bar-rating/fontawesome-stars.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css">
    <link rel="shortcut icon" href="https://Sol-0203.io/favicon.ico" />
    <style>
        .cool-input {
            border: 2px solid #007bff;
            border-radius: 0.25rem;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .cool-input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .cool-input::placeholder {
            color: #6c757d;
            opacity: 1;
        }
        .cool-button {
            padding: 0.5rem 1rem;
            font-size: 1rem;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        .cool-button:hover {
            background-color: #0056b3;
            color: #fff;
        }
        .cool-button.btn-secondary:hover {
            background-color: #343a40;
            color: #fff;
        }
        #copied {
            visibility: hidden;
            z-index: 1;
            position: fixed;
            bottom: 50%;
            background-color: #333;
            color: #fff;
            border-radius: 6px;
            padding: 16px;
            max-width: 250px;
            font-size: 17px;
        }       
        #copied.show {
            visibility: visible;
            -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }
        .info-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="dashboard.php"><img src="https://Sol-0203.io/logo.png" alt="logo"/></a>
                <a class="navbar-brand brand-logo-mini" href="dashboard.php"><img src="images/logo-mini.png" alt="logo"/></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="icon-menu"></span>
                </button>       
                <ul class="navbar-nav navbar-nav-right">           
                    <li class="nav-item dropdown d-flex mr-4 ">
                        <a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center" id="notificationDropdown" href="#" data-toggle="dropdown">
                            <i class="icon-cog"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                            <p class="mb-0 font-weight-normal float-left dropdown-header">Settings</p>              
                            <a class="dropdown-item preview-item" href="logout.php">
                                <i class="icon-inbox"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                    <span class="icon-menu"></span>
                </button>
            </div>
        </nav>
        <div class="container-fluid page-body-wrapper">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <div class="user-profile">
                    <div class="user-image">
                        <img src="images/faces/face28.png">
                    </div>
                    <div class="user-name">
                        ALADDINN GAME
                    </div>
                    <div class="user-designation">
                        Admin
                    </div>
                </div>
                <?php include 'compass.php';?>
            </nav>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-sm-12 mb-4 mb-xl-0">
                            <h4 class="font-weight-bold text-dark">Subordinate Data</h4>
                        </div>
                    </div> 
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <form method="post" autocomplete="off">
                                        <div class="form-group">
                                            <label>User ID</label>
                                            <input type="number" class="cool-input w-100" name="user_id" placeholder="Enter User ID" required style="height: 40px;" />
                                        </div>
                                        <div class="form-group">
                                            <label>Select Level</label>
                                            <select class="cool-input w-100" name="level" id="level" style="height: 40px;">
                                                <option value="all" <?php echo $selectedLevel == 'all' ? 'selected' : ''; ?>>All Levels</option>
                                                <option value="1" <?php echo $selectedLevel == '1' ? 'selected' : ''; ?>>Level 1</option>
                                                <option value="2" <?php echo $selectedLevel == '2' ? 'selected' : ''; ?>>Level 2</option>
                                                <option value="3" <?php echo $selectedLevel == '3' ? 'selected' : ''; ?>>Level 3</option>
                                                <option value="4" <?php echo $selectedLevel == '4' ? 'selected' : ''; ?>>Level 4</option>
                                                <option value="5" <?php echo $selectedLevel == '5' ? 'selected' : ''; ?>>Level 5</option>
                                                <option value="6" <?php echo $selectedLevel == '6' ? 'selected' : ''; ?>>Level 6</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Date (Optional)</label>
                                            <input type="date" class="cool-input w-100" name="date" value="<?php echo $selectedDate; ?>" style="height: 40px;" />
                                        </div>
                                        <button type="submit" class="btn btn-primary cool-button">Fetch Data</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php if ($searchedId !== null): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-center">Searched User ID: <?php echo $searchedId; ?> <?php if ($selectedDate !== null): ?> | Date: <?php echo $selectedDate; ?> <?php endif; ?></h5>
                                        <h4>Direct Subordinates</h4>
                                        <div class="info-box">💰 <strong>Commission Amount:</strong> ৳<?php echo number_format($subStats['commission'], 2); ?></div>
                                        <div class="info-box">🎲 <strong>Number of Betters:</strong> <?php echo $subStats['betters']; ?></div>
                                        <div class="info-box">💵 <strong>Total Bet Amount:</strong> ৳<?php echo number_format($subStats['betAmount'], 2); ?></div>
                                        <div class="info-box">📥 <strong>Number of Deposits:</strong> <?php echo $subStats['deposits']; ?></div>
                                        <div class="info-box">💳 <strong>Total Deposit Amount:</strong> ৳<?php echo number_format($subStats['depositAmount'], 2); ?></div>
                                        <div class="info-box">🎉 <strong>First Depositors:</strong> <?php echo $subStats['firstDepositors']; ?></div>
                                        <div class="info-box">🏆 <strong>First Deposit Amount:</strong> ৳<?php echo number_format($subStats['firstDepositAmount'], 2); ?></div>
                                        <h4 class="mt-4">Team Subordinates</h4>
                                        <div class="info-box">💰 <strong>Commission Amount:</strong> ৳<?php echo number_format($teamStats['commission'], 2); ?></div>
                                        <div class="info-box">🎲 <strong>Number of Betters:</strong> <?php echo $teamStats['betters']; ?></div>
                                        <div class="info-box">💵 <strong>Total Bet Amount:</strong> ৳<?php echo number_format($teamStats['betAmount'], 2); ?></div>
                                        <div class="info-box">📥 <strong>Number of Deposits:</strong> <?php echo $teamStats['deposits']; ?></div>
                                        <div class="info-box">💳 <strong>Total Deposit Amount:</strong> ৳<?php echo number_format($teamStats['depositAmount'], 2); ?></div>
                                        <div class="info-box">🎉 <strong>First Depositors:</strong> <?php echo $teamStats['firstDepositors']; ?></div>
                                        <div class="info-box">🏆 <strong>First Deposit Amount:</strong> ৳<?php echo number_format($teamStats['firstDepositAmount'], 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © tiranga08.site 2025</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <script src="vendors/base/vendor.bundle.base.js"></script>
    <script src="js/off-canvas.js"></script>
    <script src="js/hoverable-collapse.js"></script>
    <script src="js/template.js"></script>
    <script src="vendors/chart.js/Chart.min.js"></script>
    <script src="vendors/jquery-bar-rating/jquery.barrating.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>