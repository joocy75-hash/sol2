<?php
session_start();
if ($_SESSION['unohs'] == null) {
    header("location:index.php?msg=unauthorized");
}
date_default_timezone_set("Asia/Karachi");

include "conn.php"; // Assuming this provides $conn as mysqli

// Handle new game addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_game_url'])) {
    $game_url = mysqli_real_escape_string($conn, $_POST['new_game_url']);
    $game_name = mysqli_real_escape_string($conn, $_POST['new_game_name']);

    // Extract domain from URL
    $parsed_url = parse_url($game_url);
    $domain = $parsed_url['host'] ?? '';

    // Map known domains to their API URLs
    $api_map = [
        'dmwin13.com' => 'https://api.dmgameapi.com/api/webapi/GetNoaverageEmerdList',
        'bigdaddygame.cc' => 'https://api.bigdaddygame.cc/api/webapi/GetNoaverageEmerdList',
        '91clubapi.com' => 'https://91clubapi.com/api/webapi/GetNoaverageEmerdList',
        'rtgreh5erh4.com' => 'https://api.rtgreh5erh4.com/api/webapi/GetNoaverageEmerdList',
        'dogeclubgamesapi.com' => 'https://dogeclubgamesapi.com/api/webapi/GetNoaverageEmerdList'
    ];

    // Determine API URL
    $api_url = $api_map[$domain] ?? "https://$domain/api/webapi/GetNoaverageEmerdList";
    
    // Generate secret code (e.g., hash of game name + timestamp)
    $secret_code = md5($game_name . time());

    // Insert into database
    $insert_query = "INSERT INTO game_trend_apis (game_name, api_url, secret_code, is_active) VALUES ('$game_name', '$api_url', '$secret_code', '0')";
    if (mysqli_query($conn, $insert_query)) {
        echo '<script type="text/JavaScript"> alert("New Game Added Successfully"); </script>';
    } else {
        echo '<script type="text/JavaScript"> alert("Failed to Add Game"); </script>';
    }
}

// Handle trend switch
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['game_id'])) {
    $game_id = mysqli_real_escape_string($conn, $_POST['game_id']);
    
    // Reset all trends to inactive
    $reset_query = "UPDATE game_trend_apis SET is_active = '0'";
    mysqli_query($conn, $reset_query);
    
    // If "none" is not selected, activate the chosen trend and empty tables
    if ($game_id !== "none") {
        $update_query = "UPDATE game_trend_apis SET is_active = '1' WHERE id = '$game_id'";
        if (mysqli_query($conn, $update_query)) {
            // Empty the specified tables
            $tables_to_empty = [
                'gelluonduhogu',
                'gelluonduhogu_drei',
                'gelluonduhogu_funf',
                'gelluonduhogu_zehn'
            ];
            foreach ($tables_to_empty as $table) {
                $empty_query = "TRUNCATE TABLE `$table`";
                mysqli_query($conn, $empty_query);
            }
            echo '<script type="text/JavaScript"> alert("Trend Updated Successfully and Tables Emptied"); </script>';
        } else {
            echo '<script type="text/JavaScript"> alert("Trend Update Failed"); </script>';
        }
    } else {
        echo '<script type="text/JavaScript"> alert("All Trends Turned Off"); </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Dashboard</title>
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
	#copied{
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
              Sol-0203
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
                            <h4 class="font-weight-bold text-dark">Same Trend Switch</h4>
                            <p class="font-weight-normal mb-2 text-muted"><?php echo date("F d, Y"); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="trend-section col-md-6">
                            <h5>Add New Game</h5>
                            <form action="#" method="post" autocomplete="off">
                                <div class="d-flex align-items-center">
                                    <input name="new_game_name" type="text" placeholder="Game Name" class="flex-grow-1 cool-input mr-2" style="height: 40px;" required />
                                    <input name="new_game_url" type="text" placeholder="Game URL" class="flex-grow-1 cool-input" style="height: 40px;" required />
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <button type="submit" class="btn btn-primary cool-button mr-2">Add Game</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="trend-section col-md-6">
                            <h5>Select Trend</h5>
                            <form action="#" method="post" autocomplete="off">
                                <?php
                                $sel_trends = "SELECT * FROM game_trend_apis";
                                $trend_result = mysqli_query($conn, $sel_trends);
                                $any_active = false;
                                while ($row = mysqli_fetch_array($trend_result)) {
                                    if ($row['is_active'] == 1) $any_active = true;
                                    ?>
                                    <div class="trend-option">
                                        <input name="game_id" type="radio" value="<?php echo $row['id']; ?>" <?php if ($row['is_active'] == 1) echo "checked"; ?> />
                                        <label for="<?php echo $row['id']; ?>"><?php echo $row['game_name']; ?></label>
                                        <span class="status <?php echo $row['is_active'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $row['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="trend-option">
                                    <input name="game_id" type="radio" value="none" <?php if (!$any_active) echo "checked"; ?> />
                                    <label for="none">None (Turn Off All)</label>
                                    <span class="status <?php echo !$any_active ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo !$any_active ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center mt-3">
                                    <button type="submit" class="btn btn-primary cool-button mr-2">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © Sol-0203 private limited 2025</span>
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
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>