<?php
session_start();
if (!isset($_SESSION['unohs'])) {
    header("location:index.php?msg=unauthorized");
    exit;
}
date_default_timezone_set("Asia/Dhaka");

include 'conn.php'; // Database connection from separate file

// Reset single user
if (isset($_POST['reset_single']) && isset($_POST['balakedara'])) {
    $userId = intval($_POST['balakedara']);
    $sql = "UPDATE shonu_kaichila SET motta = 0 WHERE balakedara = $userId";
    if ($conn->query($sql) === TRUE) {
        $msg = "✅ User ID $userId ka wallet reset ho gaya.";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}

// Reset all users
if (isset($_POST['reset_all'])) {
    $sql = "UPDATE shonu_kaichila SET motta = 0";
    if ($conn->query($sql) === TRUE) {
        $msg = "✅ Sabhi users ka wallet reset ho gaya.";
    } else {
        $msg = "❌ Error resetting all: " . $conn->error;
    }
}

// Fetch user list
$result = $conn->query("SELECT balakedara, motta FROM shonu_kaichila ORDER BY balakedara ASC");
?>

<?php include 'header.php'; ?>

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
	<style>
  body {
    background-color: #0d1117;
    color: #c9d1d9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 2rem 1rem;
  }
  .main-panel {
    max-width: 960px;
    margin: 0 auto;
  }
  .card {
    background-color: #161b22;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgb(31 111 235 / 0.25);
    padding: 2rem;
  }
  h4.font-weight-bold {
    color: #58a6ff;
    text-shadow: 0 0 8px #58a6ff88;
    margin-bottom: 1.5rem;
  }
  .alert-info {
    background-color: #1f6feb33;
    color: #1f6feb;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    margin-bottom: 1.25rem;
  }
  .form-label {
    font-weight: 700;
    color: #c9d1d9;
    display: block;
    margin-bottom: 0.4rem;
  }
  input.form-control,
  select.form-select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border: 1.5px solid #30363d;
    background-color: #0d1117;
    color: #c9d1d9;
    font-weight: 600;
    transition: border-color 0.3s ease;
  }
  input.form-control:focus,
  select.form-select:focus {
    outline: none;
    border-color: #58a6ff;
    box-shadow: 0 0 8px #58a6ff88;
  }
  .btn {
    font-weight: 700;
    border-radius: 8px;
    padding: 0.6rem 1rem;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }
  .btn-warning {
    background-color: #d29922;
    color: #fff;
    box-shadow: 0 4px 12px #d2992288;
  }
  .btn-warning:hover {
    background-color: #f0b90b;
    box-shadow: 0 6px 15px #f0b90b88;
  }
  .btn-danger {
    background-color: #cf222e;
    color: #fff;
    box-shadow: 0 4px 12px #cf222e88;
  }
  .btn-danger:hover {
    background-color: #f85149;
    box-shadow: 0 6px 15px #f8514988;
  }
  .btn-outline-danger {
    background: transparent;
    border: 2px solid #cf222e;
    color: #cf222e;
  }
  .btn-outline-danger:hover {
    background-color: #cf222e;
    color: #fff;
    box-shadow: 0 6px 15px #cf222e88;
  }
  .d-flex {
    display: flex;
  }
  .gap-2 {
    gap: 0.5rem;
  }
  .row {
    margin: 0 -0.5rem;
    display: flex;
    flex-wrap: wrap;
  }
  .col-md-6 {
    padding: 0 0.5rem;
    flex: 0 0 50%;
    max-width: 50%;
  }
  .col-lg-10 {
    max-width: 83.3333%;
  }
  .col-md-12 {
    max-width: 100%;
  }
  .justify-content-center {
    justify-content: center;
  }
  .table-responsive {
    overflow-x: auto;
    max-height: 300px;
    border: 1.5px solid #30363d;
    border-radius: 8px;
    background-color: #0d1117;
  }
  table.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.5rem;
  }
  thead.table-light tr {
    background-color: #21262d;
    color: #58a6ff;
  }
  thead.table-light th {
    padding: 0.75rem 1rem;
    font-weight: 700;
  }
  tbody tr {
    background-color: #161b22;
    border-radius: 8px;
    transition: background-color 0.3s ease;
  }
  tbody tr:hover {
    background-color: #21262d;
  }
  tbody td {
    padding: 0.7rem 1rem;
    color: ##a2a2a2;
  }

  </style>



<div class="main-panel">
    <div class="content-wrapper">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card shadow-sm p-4">
                    <h4 class="font-weight-bold text-dark mb-4">Reset User Wallets</h4>

                    <?php if (isset($msg)) : ?>
                        <div class="alert alert-info"><?php echo $msg; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <!-- Left Section -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Search User by ID:</label>
                                    <input type="search" id="searchInput" onkeyup="filterTable()" class="form-control" placeholder="Type user ID...">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select User:</label>
                                    <select name="balakedara" id="userSelect" class="form-select" required>
                                        <option value="">-- Select User ID --</option>
                                        <?php
                                        mysqli_data_seek($result, 0);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='{$row['balakedara']}'>User ID: {$row['balakedara']} | Balance: ৳{$row['motta']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3 d-flex gap-2">
                                    <button type="submit" name="reset_single" class="btn btn-warning w-100">🔁 Reset Selected</button>
                                    <button type="submit" name="reset_all" class="btn btn-danger w-100" onclick="return confirm('Are you sure? This will reset all users to 0.')">⚠️ Reset All</button>
                                </div>
                            </div>

                            <!-- Right Section -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Wallet Overview:</label>
                                    <div class="table-responsive border rounded" style="max-height: 300px; overflow-y: auto;">
                                        <table id="userTable" class="table table-sm table-striped mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>User ID</th>
                                                    <th>Balance (৳)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                mysqli_data_seek($result, 0);
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr><td>{$row['balakedara']}</td><td>৳{$row['motta']}</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional full-width reset -->
                        <button type="submit" name="reset_all" class="btn btn-outline-danger w-100 mt-3" onclick="return confirm('Really reset all users?')">⚠️ Reset All Users Wallet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Filter rows by input
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#userTable tbody tr");
    rows.forEach(row => {
        let userId = row.cells[0].textContent.toLowerCase();
        row.style.display = userId.includes(input) ? "" : "none";
    });
}
</script>





</body>
</html>
