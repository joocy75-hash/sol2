<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:index.php?msg=unauthorized");
	}	
	date_default_timezone_set("Asia/Dhaka");
?>
<?php
include "conn.php";

// Fetch existing records
$sql = "SELECT * FROM tbl_firstdepositreward";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['id'] as $key => $id) {
        $rewardAmount = $_POST['rewardAmount'][$key];
        $rechargeAmount = $_POST['rechargeAmount'][$key];
        
        $updateQuery = "UPDATE tbl_firstdepositreward SET rewardAmount = '$rewardAmount', rechargeAmount = '$rechargeAmount' WHERE id = '$id'";
        $conn->query($updateQuery);
    }
    header("Location: update_firstdepositbonus.php?success=1");
    exit();
}
?>
<?php include 'header.php'; ?>

      
      
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12"> <!-- Full width use karna -->
                <div class="card shadow-sm p-4">
                    <h4 class="font-weight-bold text-dark mb-4">Update Settings</h4>

                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success text-center">Data updated successfully!</div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover text-center align-middle w-100">
                                <thead class="table-primary">
                                    <tr>
                                        <th>ID</th>
                                        <th>Reward Amount</th>
                                        <th>Recharge Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">
                                                <strong><?php echo $row['id']; ?></strong>
                                            </td>
                                            <td>
                                                <input type="number" name="rewardAmount[]" 
                                                       class="form-control text-center fw-bold border-primary"
                                                       value="<?php echo $row['rewardAmount']; ?>" min="0">
                                            </td>
                                            <td>
                                                <input type="number" name="rechargeAmount[]" 
                                                       class="form-control text-center fw-bold border-success"
                                                       value="<?php echo $row['rechargeAmount']; ?>" min="0">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-lg btn-success w-100">Update Data</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<style>
  body {
    background-color: #0d1117;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #c9d1d9;
    margin: 0;
    padding: 2rem 0;
  }

  .main-panel {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem;
  }

  .card {
    background: #161b22;
    border-radius: 12px;
    box-shadow:
      0 4px 8px rgba(0,0,0,0.7),
      0 0 15px #1f6feb;
    padding: 2rem;
  }

  .card h4 {
    color: #58a6ff;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
    text-shadow: 0 0 6px #58a6ff88;
  }

  .alert-success {
    background-color: #23863633;
    color: #238636;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 600;
    text-align: center;
    box-shadow: 0 0 8px #23863655;
  }

  .table-responsive {
    overflow-x: auto;
    margin-bottom: 1.5rem;
  }

  table.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.7rem;
    background-color: transparent;
  }

  .table thead tr {
    background-color: #1f6feb;
    color: #f0f6fc;
    border-radius: 8px;
  }

  .table thead th {
    padding: 0.75rem 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: none !important;
  }

  .table tbody tr {
    background-color: #0d1117;
    border-radius: 8px;
    box-shadow:
      0 1px 3px rgba(31, 111, 235, 0.2);
    transition: background-color 0.3s ease;
  }

  .table tbody tr:hover {
    background-color: #161b22;
  }

  .table tbody td {
    padding: 1rem;
    border: none !important;
  }

  .form-control {
    background-color: #0d1117;
    border: 1.5px solid #30363d;
    border-radius: 6px;
    color: #c9d1d9;
    font-weight: 700;
    padding: 0.35rem 0.5rem;
    text-align: center;
    transition: border-color 0.3s ease;
    min-width: 100px;
  }

  .border-primary {
    border-color: #58a6ff !important;
  }

  .border-success {
    border-color: #238636 !important;
  }

  .form-control:focus {
    outline: none;
    border-color: #58a6ff !important;
    box-shadow: 0 0 8px #58a6ff88;
  }

  .btn-success {
    background-color: #238636;
    border: none;
    padding: 0.75rem;
    font-size: 1.2rem;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 12px #238636cc;
    transition: background-color 0.3s ease;
  }

  .btn-success:hover {
    background-color: #2ea043;
    box-shadow: 0 6px 15px #2ea043cc;
  }

  .text-center {
    text-align: center;
    width: 100%;
  }

  @media (max-width: 576px) {
    .form-control {
      min-width: 80px;
    }
    table thead th,
    table tbody td {
      padding: 0.5rem 0.75rem;
    }
  }
</style>




      
</html>

</body>
</html>


