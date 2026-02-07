<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:index.php?msg=unauthorized");
	}
?>
<?php
	include ("conn.php");
	
	$userid = $_GET['user'];
	
	$snum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `shonu_subjects` WHERE `id` = '".$userid."' "));
	$owncode = $snum['owncode'];
	
	$balquery = "SELECT motta
	  FROM shonu_kaichila
	  WHERE balakedara = ".$userid;
	$balresult = $conn->query($balquery);
	$balarr = mysqli_fetch_array($balresult);
	$total_balance = $balarr['motta'];
	
	$total_refer = mysqli_fetch_assoc(mysqli_query($conn,"SELECT count(id) as total FROM `shonu_subjects` where code = '".$owncode."'"));
	
	$bet_wingo_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate` where byabaharkarta = '".$userid."'"));
	$bet_wingo_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_drei` where byabaharkarta = '".$userid."'"));
	$bet_wingo_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_funf` where byabaharkarta = '".$userid."'"));
	$bet_wingo_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_zehn` where byabaharkarta = '".$userid."'"));
	$bet_k3_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru` where byabaharkarta = '".$userid."'"));
	$bet_k3_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_drei` where byabaharkarta = '".$userid."'"));
	$bet_k3_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_funf` where byabaharkarta = '".$userid."'"));
	$bet_k3_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_kemuru_zehn` where byabaharkarta = '".$userid."'"));
	$bet_5d_1 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi` where byabaharkarta = '".$userid."'"));
	$bet_5d_3 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_drei` where byabaharkarta = '".$userid."'"));
	$bet_5d_5 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_funf` where byabaharkarta = '".$userid."'"));
	$bet_5d_10 = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(ketebida) as total FROM `bajikattuttate_aidudi_zehn` where byabaharkarta = '".$userid."'"));
	$total_bet = $bet_wingo_1['total'] + $bet_wingo_3['total'] + $bet_wingo_5['total'] + $bet_wingo_10['total'] + $bet_k3_1['total'] + $bet_k3_3['total'] + $bet_k3_5['total'] + $bet_k3_10['total'] + $bet_5d_1['total'] + $bet_5d_3['total'] + $bet_5d_5['total'] + $bet_5d_10['total'];
	
	$total_recharge = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(motta) as total FROM `thevani` WHERE `sthiti` = '1' AND `balakedara` = '".$userid."'"));
	
	$total_withdraw = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(motta) as total FROM `hintegedukolli` WHERE sthiti = 1 AND balakedara = '".$userid."'"));
	
	$total_reward = mysqli_fetch_assoc(mysqli_query($conn,"SELECT sum(price) as total FROM `hodike_balakedara` where userkani = '".$userid."'"));
	
	//$wingo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT *  FROM `bajikattuttate_zehn` where byabaharkarta = '".$userid."'"));


	$refer_record = mysqli_query($conn,"SELECT * FROM `shonu_subjects` where code = '".$owncode."' ORDER BY id DESC");
	
	$deposit_record = mysqli_query($conn,"SELECT * FROM `thevani` WHERE `balakedara` = '".$userid."' ORDER BY shonu DESC");
	
	$withdraw_record = mysqli_query($conn,"SELECT * FROM `hintegedukolli` where balakedara = '".$userid."' ORDER BY shonu DESC");
	
	$rbxquery = "SELECT SUM(ayoga) as sumayoga
	  FROM vyavahara
	  WHERE balakedara = '".$userid."' AND (prakara = 'LVLCOMM1' OR prakara = 'LVLCOMM2' OR prakara = 'LVLCOMM3' OR prakara = 'LVLCOMM4' OR prakara = 'LVLCOMM5' OR prakara = 'LVLCOMM6')";
	$rbxresult = $conn->query($rbxquery);
	$rbxar = mysqli_fetch_array($rbxresult);
	$sumayoga = (float)$rbxar['sumayoga'];
?>
<?php include 'header.php'; ?>

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
<!-- User Details Header -->
<div class="row">
  <div class="col-sm-12 mb-4">
    <h4 class="font-weight-bold text-dark">👤 User Details</h4>
  </div>
</div>

<!-- User Detail Cards -->
<div class="row">
  <!-- Left Column -->
  <div class="col-lg-6 col-sm-12 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <li><strong>🆔 User ID:</strong> <span class="text-primary"><?php echo $snum['id']; ?></span></li>
          <li><strong>🔗 Referral ID:</strong> <span class="text-success"><?php echo $snum['owncode']; ?></span></li>
          <li><strong>🌐 IP Address:</strong> <span><?php echo $snum['ishonup']; ?></span></li>
          <li class="mt-3">
            <strong>🔒 Reset Password:</strong>
            <form action="update_userpass.php" method="POST" class="d-flex mt-2 gap-2">
              <input type="text" name="resetpsw" placeholder="New Password" class="form-control form-control-sm" required>
              <input type="hidden" name="user_id" value="<?php echo $snum['id']; ?>">
              <button type="submit" class="btn btn-sm btn-warning">Update</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Right Column -->
  <div class="col-lg-6 col-sm-12 mb-4">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <li><strong>📱 Phone No.:</strong> <span class="text-dark"><?php echo $snum['mobile']; ?></span></li>
          <li><strong>📅 Created At:</strong> <span><?php echo $snum['createdate']; ?></span></li>
          <li class="mt-3">
            <strong>👥 Referred By:</strong>
            <form action="update_reffcode.php" method="POST" class="d-flex mt-2 gap-2">
              <input type="text" name="referred_by" value="<?php echo $snum['code']; ?>" class="form-control form-control-sm" required>
              <input type="hidden" name="user_id" value="<?php echo $snum['id']; ?>">
              <button type="submit" class="btn btn-sm btn-info">Update</button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

 
         
          
          
<div class="row">
    <!-- Total Balance -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#2C3E50; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_transcation" style="color:white;">Total Balance</a></strong>
                    <h2>৳<?= round($total_balance,2);?></h2>
                </div>
                <div style="font-size:30px;">💰</div>
            </div>
        </div>
    </div>

    <!-- Total Referral -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#27AE60; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_refer" style="color:white;">Total Referral</a></strong>
                    <h2><?= (float)$total_refer['total'];?></h2>
                </div>
                <div style="font-size:30px;">👥</div>
            </div>
        </div>
    </div>

    <!-- Total Bet -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#8E44AD; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_bet" style="color:white;">Total Bet</a></strong>
                    <h2>৳<?= round($total_bet, 2);?></h2>
                </div>
                <div style="font-size:30px;">🎲</div>
            </div>
        </div>
    </div>

    <!-- Total Recharge -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#2980B9; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_recharge" style="color:white;">Total Recharge</a></strong>
                    <h2>৳<?= round($total_recharge['total'],2);?></h2>
                </div>
                <div style="font-size:30px;">➕💳</div>
            </div>
        </div>
    </div>

    <!-- Total Withdraw -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#E67E22; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_withdrawal" style="color:white;">Total Withdraw</a></strong>
                    <h2>৳<?= round($total_withdraw['total'],2);?></h2>
                </div>
                <div style="font-size:30px;">📤</div>
            </div>
        </div>
    </div>

    <!-- Total Gift -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#C0392B; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_reward" style="color:white;">Total Gift</a></strong>
                    <h2>৳<?= round($total_reward['total'],2);?></h2>
                </div>
                <div style="font-size:30px;">🎁</div>
            </div>
        </div>
    </div>

    <!-- Total Commission -->
    <div class="col-md-3 col-sm-12 col-lg-3 mb-3">
        <div style="background:#16A085; color:#fff; padding:20px; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><a href="#total_commission" style="color:white;">Total Commission</a></strong>
                    <h2>৳<?= $sumayoga;?></h2>
                </div>
                <div style="font-size:30px;">💸</div>
            </div>
        </div>
    </div>
</div>

		  <div class="row" style="background-color: #fff;">
            
             
            
            
			<div class="col-lg-12 col-sm-12 col-xs-12">
				<h4>Referral Record</h4>
				<div class="table-responsive">
					<table id="example1" class="table table-condensed">
						<thead>
							<tr>
								<th>Join Date</th>
								<th>Number</th>
								<th>Invite Id</th>
								<th>IP Address</th>
								<th>Total Recharge</th> 
								<th>Wallet</th> 
								<th>Action</th> 
							</tr>
						</thead>
						<tbody>
						<?php 
							while($item=mysqli_fetch_array($refer_record)){ 
						?>
								<tr>
									<td><?= $item['createdate']; ?></td>
									<td><?= $item['mobile']; ?></td>
									<td><?= $item['owncode']; ?></td>
									<td><?= $item['ishonup']; ?></td>                                    
									<td>৳
										<?php														    
											$totalRecharge['total'] = 0;
											$q = mysqli_query($conn,"SELECT sum(motta) as total FROM `thevani` WHERE `sthiti` = '1' AND `balakedara` = '".$item['id']."'");
											$totalRecharge = mysqli_fetch_assoc($q);
											echo round($totalRecharge['total'],2);                                                           
										?>
									</td>
									<td>৳
										<?php
											$totalRecharge['total'] = 0;
											$q = mysqli_query($conn,"SELECT motta as total FROM shonu_kaichila WHERE balakedara = '".$item['id']."' ");
											$totalRecharge = mysqli_fetch_assoc($q);
											echo round($totalRecharge['total'],2);                                                            
										?>
									</td>
									<td>
										<a href="user-details.php?user=<?= $item['id']; ?>"  target="_blank" class="update-person" style="background-color: darkorange; color:white; font-size:12px; padding: 5px;" title="User Detail">User Detail</a>
									</td>                                                                                                                       													
								</tr>
						<?php 
							} 
						?>
						</tbody>
					</table>
				</div>
			</div>        
		  </div>
		  <br>
		  <div class="row" style="background-color: #fff;">
			<div class="col-lg-12 col-sm-12 col-xs-12">
				<h4>Recharge Record</h4>
				<div class="table-responsive">
					<table id="example3" class="table table-condensed">
						<thead>
							<tr>
								<th>Updated At</th>
								<th>Transaction ID</th>
								<th>Amount</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php 
							while($item=mysqli_fetch_array($deposit_record)){ 
						?>
								<tr>
									<td><?= $item['dinankavannuracisi']; ?></td>
									<td><?= $item['ullekha']; ?></td>
									<td><?= $item['motta']; ?></td>                                    
									<td>৳
										<?php 
											if($item['sthiti'] == 1){
												echo "Success";
											}
											else if($item['sthiti'] == 0){
												echo "Pending";
											} 
											else if($item['sthiti'] == 2){
												echo "Rejected";
											} 
										?>
									</td>									                                                                                                                      													
								</tr>
						<?php 
							} 
						?>
						</tbody>
					</table>
				</div>
			</div>        
		  </div>
		  <br>
		  <div class="row" style="background-color: #fff;">
			<div class="col-lg-12 col-sm-12 col-xs-12">
				<h4>Withdraw Record</h4>
				<div class="table-responsive">
					<table id="example4" class="table table-condensed">
						<thead>
							<tr>
								<th>Updated At</th>
								<th>Amount</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
						<?php 
							while($item=mysqli_fetch_array($withdraw_record)){ 
						?>
								<tr>
									<td><?= $item['dinankavannuracisi']; ?></td>
									<td><?= $item['motta']; ?></td>                                
									<td>৳
										<?php 
											if($item['sthiti'] == 1){
												echo "Success";
											}
											else if($item['sthiti'] == 0){
												echo "Pending";
											} 
											else if($item['sthiti'] == 2){
												echo "Rejected";
											} 
										?>
									</td>									                                                                                                                      													
								</tr>
						<?php 
							} 
						?>
						</tbody>
					</table>
				</div>
              
              <br>
		  <div class="row" style="background-color: #fff;">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <h4>Wingo Bet Record</h4>
        <div class="table-responsive">
            <table id="example5" class="table table-condensed">
                <thead>
                    <tr>
                        <th>Wingo Type</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $query = "
                    SELECT 'Wingo 30 Sec' AS wingo_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_zehn WHERE byabaharkarta = '".$userid."'
                    UNION ALL
                    SELECT 'Wingo 1 Min' AS wingo_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate WHERE byabaharkarta = '".$userid."'
                    UNION ALL
                    SELECT 'Wingo 3 Min' AS wingo_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_drei WHERE byabaharkarta = '".$userid."'
                    UNION ALL
                    SELECT 'Wingo 5 Min' AS wingo_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_funf WHERE byabaharkarta = '".$userid."'
                ";

                $wingo = mysqli_query($conn, $query);

                if (!$wingo) {
                    echo "Error: " . mysqli_error($conn); // Error handling for query failure
                } else {
                    // Loop through all rows returned by the query
                    while ($imitator = mysqli_fetch_assoc($wingo)) {
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($imitator['wingo_type']); ?></td>
                        <td><?php echo htmlspecialchars($imitator['kalaparichaya']); ?></td>
                        <td><?php echo htmlspecialchars($imitator['ketebida']); ?></td>                                    
                        <td><?php 
                            echo ($imitator['phalaphala'] == "perte") ? "LOSS" : "WIN";
                        ?></td>           
                    </tr>
                <?php 
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>        
</div>
            
              
              <!--   here i add trx wingo   -->
              
<div class="row" style="background-color: #fff;">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <h4>Trx Wingo Bet Record</h4>
        <div class="table-responsive">
            <table id="example5" class="table table-condensed">
                <thead>
                    <tr>
                        <th>Trx Wingo Type</th>
                        <th>Period</th>
                        <th>Amount</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $query = "
                    SELECT 'Trx Wingo 1 Min' AS trx_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_trx WHERE byabaharkarta = '".$userid."'
                    UNION ALL
                    SELECT 'Trx Wingo 3 Min' AS trx_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_trx3 WHERE byabaharkarta = '".$userid."'
                    UNION ALL
                    SELECT 'Trx Wingo 5 Min' AS trx_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_trx5 WHERE byabaharkarta = '".$userid."'
                    UNION ALL
                    SELECT 'Trx Wingo 10 Min' AS trx_type, kalaparichaya, ketebida, phalaphala 
                    FROM bajikattuttate_trx10 WHERE byabaharkarta = '".$userid."'
                ";

                $trx = mysqli_query($conn, $query);

                if (!$trx) {
                    echo "Error: " . mysqli_error($conn); // Error handling for query failure
                } else {
                    // Loop through all rows returned by the query
                    while ($imitator = mysqli_fetch_assoc($trx)) {
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($imitator['trx_type']); ?></td>
                        <td><?php echo htmlspecialchars($imitator['kalaparichaya']); ?></td>
                        <td><?php echo htmlspecialchars($imitator['ketebida']); ?></td>                                    
                        <td><?php 
                            echo ($imitator['phalaphala'] == "perte") ? "LOSS" : "WIN";
                        ?></td>           
                    </tr>
                <?php 
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>        
</div>              
              
              
			</div>
            

		  </div>
		</div>		
		<footer class="footer">
			<div class="d-sm-flex justify-content-center justify-content-sm-between">
              
              
      





            
              
              
				<span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © Sol-0203.io</span>
			</div>
		</footer>
      </div>     
    </div>
  </div>  
 
  <script>
	$('#example1').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true,
	  "pageLength": 20
    });
	$('#example3').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true,
	  "pageLength": 20
    });
	$('#example4').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true,
	  "pageLength": 20
    });
    
    $('#example5').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true,
	  "pageLength": 20
    });
    
    
    $('#example6').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": false,
      "info": true,
      "autoWidth": true,
	  "pageLength": 20
    });
    
  </script>
</body>

</html>