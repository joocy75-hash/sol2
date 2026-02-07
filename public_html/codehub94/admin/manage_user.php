

<?php
session_start();

// Check if session is set
if (!isset($_SESSION['unohs']) || empty($_SESSION['unohs'])) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

include("conn.php");

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Total Users
try {
    $total_users_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM shonu_subjects");
    $total_users = mysqli_fetch_assoc($total_users_query)['total'] ?? 0;
} catch (Exception $e) {
    echo "Error fetching total users: " . $e->getMessage();
    $total_users = 0;
}

// Active Users (status = 1)
try {
    $active_users_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM shonu_subjects WHERE status = 1");
    $active_users = mysqli_fetch_assoc($active_users_query)['total'] ?? 0;
} catch (Exception $e) {
    echo "Error fetching active users: " . $e->getMessage();
    $active_users = 0;
}

// Banned Users (status = 0)
try {
    $banned_users_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM shonu_subjects WHERE status = 0");
    $banned_users = mysqli_fetch_assoc($banned_users_query)['total'] ?? 0;
} catch (Exception $e) {
    echo "Error fetching banned users: " . $e->getMessage();
    $banned_users = 0;
}

// Total Wallet (sum of motta)
try {
    $wallet_query = mysqli_query($conn, "SELECT SUM(CAST(motta AS DECIMAL(10,2))) AS total_wallet FROM shonu_kaichila");
    $total_wallet = mysqli_fetch_assoc($wallet_query)['total_wallet'] ?? 0;
} catch (Exception $e) {
    echo "Error fetching total wallet: " . $e->getMessage();
    $total_wallet = 0;
}

// Fetch All Users & Join wallet table
try {
    $users_query = mysqli_query($conn, "
        SELECT 
            s.id, s.mobile, s.owncode, s.ishonup, s.createdate, s.password, 
            k.motta AS wallet, k.rebet AS recharge, k.bonus AS first_recharge, k.balakedara,
            k.rebet, k.spin, k.bonus,
            k.kramasankhye, k.accountno 
        FROM shonu_subjects s
        LEFT JOIN shonu_kaichila k ON s.id = k.balakedara
        ORDER BY s.id DESC
    ");

    if (!$users_query) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }

    $users = [];
    while ($row = mysqli_fetch_assoc($users_query)) {
        $row['wallet'] = $row['wallet'] ?? 0;
        $row['recharge'] = $row['recharge'] ?? 0;
        $row['first_recharge'] = $row['first_recharge'] ?? 0;
        $row['ifsc'] = isset($row['ifsc']) ? $row['ifsc'] : 'N/A'; // Fallback for missing ifsc
        $row['account_no'] = $row['accountno'] ?? 'N/A';
        $users[] = $row;
    }
} catch (Exception $e) {
    echo "Error fetching users: " . $e->getMessage();
    $users = [];
}

include 'header.php';
?>
      <div class="main-panel">
        <div class="content-wrapper">
			<div class="row">
				<div class="box-header box-header2 align-middle">
					<div class="col-xs-6 text-right">
					<h3 class="box-title"><?php 
						if(isset($_GET['msg'])=="updt") 
						{ ?>
						<font size="+1" color="#FF0000">Update Successfully...</font>
						<?php  } ?></h3>
					</div>
					<div class="col-sm-6">
						<div class="pull-right">&nbsp;</div>
					</div>		  
				</div>
			</div>
          <!-- User Summary Section -->
<div class="row mb-4">
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card shadow h-100 border-0 text-white" style="background-color: #0d6efd;"> 
      <!-- #0d6efd Bootstrap ka primary blue hai, aap yaha apna shade bhi de sakte ho -->
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="h6">Total Users</div>
          <h4 class="mb-0"><?= $total_users; ?></h4>
        </div>
        <span class="material-icons md-48">group</span>
      </div>
    </div>
  </div>



  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card shadow h-100 border-0 bg-success text-white">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="h6">Active Users</div>
          <h4 class="mb-0"><?= $active_users; ?></h4>
        </div>
        <span class="material-icons md-48">verified_user</span>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card shadow h-100 border-0 bg-danger text-white">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="h6">Banned Users</div>
          <h4 class="mb-0"><?= $banned_users; ?></h4>
        </div>
        <span class="material-icons md-48">block</span>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card shadow h-100 border-0 bg-dark text-white">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="h6">Wallet Total</div>
          <h4 class="mb-0">৳<?= number_format($total_wallet, 2); ?></h4>
        </div>
        <span class="material-icons md-48">account_balance_wallet</span>
      </div>
    </div>
  </div>
</div>


<!-- Manage User Table -->
<div class="row">
  <div class="col-sm-12">
    <div class="card shadow-sm">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><span class="material-icons">manage_accounts</span> Manage Users</h5>
        <span class="badge bg-warning text-dark">Live User Management</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="example1" class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light text-center">
              <tr>
  <th><span class="material-icons">smartphone</span> Phone</th>
  <th><span class="material-icons">fingerprint</span> Reff. Code</th>
  <th><span class="material-icons">public</span> Location/IP</th>
  <th><span class="material-icons">badge</span> User ID</th>
  <th><span class="material-icons">account_balance_wallet</span> Wallet</th>
  <th><span class="material-icons">payments</span> Recharge</th>
  <th><span class="material-icons">bolt</span> Bet</th>
  <th><span class="material-icons">calendar_today</span> Join Date</th>
  <th><span class="material-icons">settings</span> Actions</th>
  <th><span class="material-icons">lock</span> Pswd</th>
  <th><span class="material-icons">account_balance</span> IFSC </th>
  <th><span class="material-icons">credit_card</span> Account No.</th>
</tr>
<style>
  th .material-icons {
    vertical-align: middle;
    margin-right: 6px;
    font-size: 20px;
    color: #007bff;
  }
  th {
    white-space: nowrap;
    font-weight: 600;
    font-size: 14px;
  }
</style>


            </thead>
            <tbody class="text-center">
              <?php foreach ($users as $user): ?>
              <tr>
                <td><?= $user['mobile']; ?></td>
                <td><?= $user['owncode']; ?></td>
                <td><?= $user['ishonup']; ?></td>
                <td><?= $user['id']; ?></td>
                <td>৳<?= number_format($user['wallet'], 2); ?></td>
                <td>৳<?= number_format($user['recharge'], 2); ?></td>
                <td>৳<?= number_format($user['first_recharge'], 2); ?></td>
                <td><?= $user['createdate']; ?></td>
                <td>
                  <button class="btn btn-sm btn-outline-success" title="Login as User">
                    <span class="material-icons">login</span>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" title="Ban User">
                    <span class="material-icons">block</span>
                  </button>
                </td>
                <td>••••••</td>
                <td><?= $user['ifsc']; ?></td>
                <td><?= $user['account_no']; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

		  
		<!-- Change Amount Modal -->
<div id="excel" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      
      <!-- Modal Header -->
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="chn">
          <span class="material-icons align-middle me-1">edit</span>
          Change Amount
          <br>
          <small id="mob" class="text-light"></small>
        </h5>
        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Form -->
      <form name="type" id="type" enctype="multipart/form-data" action="#" method="post">
        <div class="modal-body">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="amount" name="amount" placeholder="Amount" onkeypress="return isNumber(event)" required>
            <label for="amount">Enter New Amount</label>
            <input type="hidden" id="editid" name="editid">
            <div id="error" class="text-danger small mt-1"></div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">
            <span class="material-icons align-middle">check_circle</span> Save
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
	</div>
	
		<footer style="
  width: 100%;
  background: linear-gradient(90deg, #1f4bb9, #0d0e37, #0016b5);
  color: white;
  padding: 12px 0;
  text-align: center;
  font-size: 14px;
  font-weight: 500;
  position: relative;
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
      </div>     
    </div>
  </div>  

  <script>
	$(function () {
		var table = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"ajax": "manage_user_data.php",
			"paging": true,
			"lengthChange": true,
			"searching": true,
			"ordering": false,
			"info": true,
			"autoWidth": true,
			"pageLength": 50,
			"dom": 'lrtip'
		});
	 $('#example1 thead th').each(function () {
        var title = $(this).text();
        if (title === 'Mobile' || title === 'Cust ID' || title === 'IP Address') {
          $(this).html(title + ' <input type="text" class="col-search-input" placeholder="Search ' + title + '" />');
        }
      });

      // Apply the search
      table.columns().every(function () {
        var column = this;
        $('input', this.header()).on('keyup change', function () {
          if (column.search() !== this.value) {
            column.search(this.value).draw();
          }
        });
      });
    });
	function edit(id,mob,balance) {
		$('#excel').modal({backdrop: 'static', keyboard: false})   
		$('#excel').modal('show');
		document.getElementById('mob').innerHTML = 'Mobile: '+mob;
		document.getElementById('amount').value = balance;
		document.getElementById('editid').value = id;
	}
	
	$(document).ready(function () {
		$("#type").on('submit',(function(e) {
			e.preventDefault();
			var quantity = $('input#quantity').val();
			if ((quantity)== "") {
				$("input#quantity").focus();
				$('#quantity').css({'border-color': '#f00'});
				return false;
			}						
			$.ajax({
				type: "POST", 
				url: "updatewalletNow.php",              
				data: new FormData(this), 
				contentType: false,       
				cache: false,             
				processData:false,       

				success: function(html)   
				{
					if (html == 1) {
						alert("Amount update successfully...");			
						$("#type")[0].reset();
						$('#excel').modal('hide');
						window.location ='';
					}			
					else if(html==0)
					{ 
						alert("Some Technical Error....");						
					}			
				}
			});	
		}));			
	});
	
	function delete_row(Id) {
		var strconfirm = confirm("Are You Sure You Want To Delete?");
		if (strconfirm == true) {
			$.ajax({
				type: "Post",
				data:"id=" + Id + "& type=" + "delete" ,
				url: "manage_userAction.php",
				success: function (html) { 
					if(html==1){
						alert("Selected Item Deleted Sucessfully....");
						window.location = '';
					}
					else if(html==0){
						alert("Some Technical Problem");							  
					}
				},
				error: function (e) {
				}
			});
		}
	}
	
	function Respond(Id) {
		var strconfirm = confirm("Are you sure you want to Unpublish?");
        if (strconfirm == true) {
            $.ajax({
                type: "Post",
                data:"id=" + Id + "& type=" + "chk" ,
                url: "manage_userAction.php",
                success: function (html) {
                    window.location = '';
                    return false;
                },
                error: function (e) {
                }
            });
        }
    }
	
	function UnRespond(Id) {
	    var strconfirm = confirm("Are you sure you want to Publish?");
        if (strconfirm == true) {
            $.ajax({
                type: "Post",
                data:"id=" + Id + "& type=" + "unchk" ,
                url: "manage_userAction.php",
                success: function (html) {
                    window.location = '';
                    return false;
                },
                error: function (e) {
                }
            });
        }
    }
  </script>
</body>

</html>