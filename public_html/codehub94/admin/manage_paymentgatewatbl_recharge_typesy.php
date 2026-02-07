<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit();
}

include "conn.php"; // Database connection

// ✅ Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // ✅ Fetch Data from Database
    if ($action === 'fetch') {
        $sql = "SELECT * FROM payment_methods";
        $result = $conn->query($sql);
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data);
        exit();
    } 
    
    // ✅ Update Database Securely
    elseif ($action === 'update') {
        // 📌 Securely Fetching Input Data
        $id = intval($_POST['id']);
        $payName = $conn->real_escape_string($_POST['payName']);
        $miniPrice = floatval($_POST['miniPrice']);
        $maxPrice = floatval($_POST['maxPrice']);
        $scope = $conn->real_escape_string($_POST['scope']);
        $startTime = $conn->real_escape_string($_POST['startTime']);
        $endTime = $conn->real_escape_string($_POST['endTime']);
        $rechargeRifts = floatval($_POST['rechargeRifts']);
        $status = $conn->real_escape_string($_POST['status']);

        // 📌 Secure Prepared Statement (Prevents SQL Injection)
        $sql = "UPDATE tbl_recharge_types SET 
                payName = ?, miniPrice = ?, maxPrice = ?, scope = ?, 
                startTime = ?, endTime = ?, rechargeRifts = ?, status = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sddsssdsd", $payName, $miniPrice, $maxPrice, $scope, $startTime, $endTime, $rechargeRifts, $status, $id);
            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Database update failed"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "Database statement preparation failed"]);
        }
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Dashboard</title>
  <!-- ✅ jQuery First Load Karo -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ✅ Then Bootstrap JavaScript Load Karo -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
  
  .table-responsive {
    overflow-x: auto;
    width: 100%;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: center;
}
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
      <div class="container mt-4">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payment Methods</title>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4 text-primary fw-bold">Manage Payment Methods</h2>
    

    <!-- ✅ Success/Error Messages -->
    <div id="responseMessage" class="alert d-none text-center"></div>

    <div class="table-responsive shadow-lg p-3 bg-white rounded">
        <table class="table table-striped table-hover table-bordered align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Pay Name</th>
                    <th>Min Price</th>
                    <th>Max Price</th>
                    <th>Scope</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Recharge Gifts</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="gatewayTable">
                <!-- Data Loaded Here Dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title">Edit Payment Gateway</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Pay Name</label>
                            <input type="text" id="editPayName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Min Price</label>
                            <input type="number" id="editMinPrice" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Max Price</label>
                            <input type="number" id="editMaxPrice" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Scope</label>
                            <input type="text" id="editScope" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Start Time</label>
                            <input type="time" id="editStartTime" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">End Time</label>
                            <input type="time" id="editEndTime" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Recharge Gifts</label>
                            <input type="number" id="editRechargeRifts" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status</label>
                            <select id="editStatus" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ✅ JavaScript for AJAX Data Handling -->
<script>
    $(document).ready(function () {
    fetchGateways();

    // ✅ Dynamic Event Delegation for Edit Button (Fixes future dynamic table issues)
    $(document).on("click", ".btn-edit", function () {
        let id = $(this).data("id");
        let payName = $(this).data("payname");
        let miniPrice = $(this).data("minprice");
        let maxPrice = $(this).data("maxprice");
        let scope = $(this).data("scope");
        let startTime = $(this).data("starttime");
        let endTime = $(this).data("endtime");
        let rechargeRifts = $(this).data("rechargerifts");
        let status = $(this).data("status");

        editGateway(id, payName, miniPrice, maxPrice, scope, startTime, endTime, rechargeRifts, status);
    });
});

// ✅ Function to Fetch Data & Render Table
function fetchGateways() {
    $.post("backend.php", { action: "fetch" }, function (response) {
        let tableBody = "";
        response.forEach(gateway => {
            tableBody += `
                <tr>
                    <td>${gateway.id}</td>
                    <td>${gateway.payName}</td>
                    <td>${gateway.miniPrice}</td>
                    <td>${gateway.maxPrice}</td>
                    <td>${gateway.scope}</td>
                    <td>${gateway.startTime}</td>
                    <td>${gateway.endTime}</td>
                    <td>${gateway.rechargeRifts}</td>
                    <td><span class="badge bg-${gateway.status === 'active' ? 'success' : 'danger'}">${gateway.status}</span></td>
                    <td>
                        <button class="btn btn-warning btn-sm fw-bold btn-edit"
                                data-id="${gateway.id}"
                                data-payname="${gateway.payName}"
                                data-minprice="${gateway.miniPrice}"
                                data-maxprice="${gateway.maxPrice}"
                                data-scope="${gateway.scope}"
                                data-starttime="${gateway.startTime}"
                                data-endtime="${gateway.endTime}"
                                data-rechargerifts="${gateway.rechargeRifts}"
                                data-status="${gateway.status}">Edit</button>
                    </td>
                </tr>`;
        });
        $("#gatewayTable").html(tableBody);
    }, "json");
}

// ✅ Function to Handle Edit Modal
function editGateway(id, payName, miniPrice, maxPrice, scope, startTime, endTime, rechargeRifts, status) {
    $("#editId").val(id);
    $("#editPayName").val(payName);
    $("#editMinPrice").val(miniPrice);
    $("#editMaxPrice").val(maxPrice);
    $("#editScope").val(scope);
    $("#editStartTime").val(startTime);
    $("#editEndTime").val(endTime);
    $("#editRechargeRifts").val(rechargeRifts);
    $("#editStatus").val(status);

    // ✅ Ensure Bootstrap Modal Works Properly
    var myModal = new bootstrap.Modal(document.getElementById('editModal'), {});
    myModal.show();
}

// ✅ Update Form Submission
$("#editForm").submit(function (e) {
    e.preventDefault();

    $.post("backend.php", {
        action: "update",
        id: $("#editId").val(),
        payName: $("#editPayName").val(),
        miniPrice: $("#editMinPrice").val(),
        maxPrice: $("#editMaxPrice").val(),
        scope: $("#editScope").val(),
        startTime: $("#editStartTime").val(),
        endTime: $("#editEndTime").val(),
        rechargeRifts: $("#editRechargeRifts").val(),
        status: $("#editStatus").val()
    }, function (response) {
        if (response.success) {
            $("#responseMessage").removeClass("d-none alert-danger").addClass("alert-success").text("Update successful!");
            var myModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            myModal.hide();
            fetchGateways(); // ✅ Refresh table after update
        } else {
            $("#responseMessage").removeClass("d-none alert-success").addClass("alert-danger").text("Error updating: " + response.error);
        }
    }, "json");
});

</script>
    

</html>
</body>
</html>
