		<?php
			$chkserial = mysqli_query($conn,"select * from `nirvahaka_shonu` where `unohs`='".$_SESSION['unohs']."'");
			$salu = mysqli_fetch_array($chkserial);
			$dashboard = $salu['dashboard'];
			$wingomanager = $salu['wingomanager'];
			$k3manager = $salu['k3manager'];
			$d5manager = $salu['5dmanager'];
			$finance = $salu['finance'];
			$managegame = $salu['managegame'];
            $support = $salu['support'];
            $setting = $salu['setting'];
            $agents = $salu['agents'];
            $marketing = $salu['marketing'];
            $admins= $salu['admins'];
		?>
		

		
		
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		
		
		
		

		<head>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<ul class="nav">
  <li class="nav-item">
    <a class="nav-link" href="dashboard.php">
      <i class="material-icons menu-icon">dashboard</i>
      <span class="menu-title">Dashboard</span>
    </a>
          </li>
		  <?php
			if($wingomanager == 1){
		  ?>
   <li class="nav-item">
  <a class="nav-link d-flex align-items-center" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
    <i class="material-icons" style="font-size: 22px; line-height: 1; margin-right: 8px;">sports_esports</i>
    <span class="menu-title">WinGo Manager</span>
    <i class="menu-arrow ml-auto"></i>
  </a>

            <div class="collapse" id="ui-basic">
              <ul class="nav flex-column sub-menu">
                <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="wingo10min.php">WinGo 30 sec</a></li>
                <li class="nav-item"> <a class="nav-link" href="wingo1min.php">WinGo 1 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="wingo3min.php">WinGo 3 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="wingo5min.php">WinGo 5 Min</a></li>
              </ul>
            </div>
          </li>
		  <?php
			}
			if($k3manager == 1){
		  ?>
		  <li class="nav-item">
  <a class="nav-link d-flex align-items-center" data-toggle="collapse" href="#ui-basic-1" aria-expanded="false" aria-controls="ui-basic-1">
    <i class="material-icons menu-icon" style="font-size: 22px; line-height: 1; margin-right: 8px;">sports_score</i>
    <span class="menu-title">K3 Manager</span>
    <i class="menu-arrow ml-auto"></i>
  </a>  
            <div class="collapse" id="ui-basic-1">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="k31min.php">K3 1 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="k33min.php">K3 3 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="k35min.php">K3 5 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="k310min.php">K3 10 Min</a></li>
              </ul>
            </div>
          </li>
		  <?php
			}
			if($d5manager == 1){
		  ?>
<li class="nav-item">
  <a class="nav-link d-flex align-items-center" data-toggle="collapse" href="#ui-basic-2" aria-expanded="false" aria-controls="ui-basic-2">
    <i class="material-icons menu-icon" style="font-size: 22px; line-height: 1; margin-right: 8px;">casino</i>
    <span class="menu-title">5D Manager</span>
    <i class="menu-arrow ml-auto"></i>
  </a>

            <div class="collapse" id="ui-basic-2">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="5d1min.php">5D 1 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="5d3min.php">5D 3 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="5d5min.php">5D 5 Min</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="5d10min.php">5D 10 Min</a></li>
              </ul>
            </div>
          </li>
          
          <?php
			}
			if($admins == 1){
		  ?>
		  <li class="nav-item">
    <a class="nav-link" data-toggle="collapse" href="#ui-basic-9" aria-expanded="false" aria-controls="ui-basic-9">
      <i class="material-icons menu-icon">admin_panel_settings</i> <!-- ðŸ‘¥ Agent icon -->
      <span class="menu-title">Admins Manager</span>
      <i class="menu-arrow"></i>
    </a>
    <div class="collapse" id="ui-basic-9">
      <!-- Submenu items can go here -->
      
                <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="adminpass.php">Admin Password</a></li>
                  </ul>
                  
                  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="addadmin.php">Add Admin</a></li>
              </ul>
         
          
		  <?php
			}
			if($finance == 1){
		  ?>
<li class="nav-item">
  <a class="nav-link d-flex align-items-center" data-toggle="collapse" href="#ui-basic-3" aria-expanded="false" aria-controls="ui-basic-3">
    <i class="material-icons menu-icon" style="font-size: 22px; line-height: 1; margin-right: 8px;">account_balance</i>
    <span class="menu-title">Finance</span>
    <i class="menu-arrow ml-auto"></i>
  </a>


            <div class="collapse" id="ui-basic-3">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="addupi.php">Add Upi</a></li>
              </ul>
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="usdtkids.php">USDT RATE </a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="addusdt.php">Add Usdt</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="addupiimg.php">Add Upi Image</a></li>
              </ul>			  
			  <ul class="nav flex-column sub-menu">
               <li class="nav-item"> <a class="nav-link" href="addimgusdt_2.php">Add Usdt Image</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="deposit_update.php">Deposit Update</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_withdraw.php">Withdraw Apply</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="withdraw_accept_list.php">Withdraw Sent</a></li>
              </ul>
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="withdraw_reject_list.php">Withdraw Reject</a></li>
              </ul>
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="update_with_pay.php">Update Gateway</a></li>
              </ul>
            </div>
          </li>
          
          
          <?php
			}
			if($support == 1){
		  ?>
		  <li class="nav-item">
  <a class="nav-link d-flex align-items-center" data-toggle="collapse" href="#ui-basic-5" aria-expanded="false" aria-controls="ui-basic-5">
    <i class="material-icons menu-icon" style="font-size: 22px; line-height: 1; margin-right: 8px;">support_agent</i>
    <span class="menu-title">Support</span>
    <i class="menu-arrow ml-auto"></i>
  </a>
</li>

            <div class="collapse" id="ui-basic-5">
              
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="deposite.php">Deposite Problem</a></li>
              </ul>
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="withprob.php">Withdrawal problem</a></li>
              </ul>
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="ifscm.php">IFSC Modification</a></li>
              </ul>
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="bankm.php">Bank Modification</a></li>
              </ul>
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="gamep.php">Game Problem</a></li>
              </ul>
            </div>
          </li>
          
          
          
		  <?php
			}
			if($managegame == 1){
		  ?>
		  <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic-4" aria-expanded="false" aria-controls="ui-basic-4">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Manage Users </span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic-4">
              
                <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="userbonus.php">Bonus Manage</a></li>
              </ul>
            <!--  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_bet.php">Manage illigal bet</a></li>
              </ul> -->
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_bankcard.php">Modify Bank Details</a></li>
              </ul>
              
              <div class="collapse" id="ui-basic-4">
                <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="autobanuser.php">Check Same IP</a></li>
                  </ul>
                
                
		<!-- <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="mainkids.php">maintainance</a></li>
              </ul>  -->
                
                 <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="banbybet.php">ban illigal users</a></li>
              </ul>
                
                
		<!-- <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="updatejet.php">Jet Max Value</a></li>
              </ul> -->
         	<!--     <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_support.php">Users Query</a></li>
              </ul> -->
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_user.php">Users</a></li>
              </ul>
          	<!--    <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_salary.php">Daily Salary</a></li>
              </ul>-->
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="addgiftcode.php">Gift Code</a></li>
              </ul>
		<!--	  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="addtelegram.php">Telegram</a></li>
              </ul> -->
			  
			  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="demouser.php">Demo User</a></li>
              </ul>
			  
            </div>
          </li>
          
          <?php
			}
			if($setting == 1){
		  ?>
		  <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#ui-basic-6" aria-expanded="false" aria-controls="ui-basic-6">
    <i class="material-icons menu-icon">settings</i>
    <span class="menu-title">Setting</span>
    <i class="menu-arrow"></i>
  </a>
  <div class="collapse" id="ui-basic-6">

              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="update_banner.php">Banner Update</a></li>
              </ul>
              
               <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="websetting.php">Website Setting</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="update_commission.php">Commission Setting</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="update_firstdepositbonus.php">Deposit Bonus Setting</a></li>
              </ul>
              
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="update_invitebonus.php">Invite Bonus Setting</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_paymentgateway.php">Payment Gateway Setting</a></li>
              </ul>
              
                            <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="mtg_bot_Predction.php">Manage Bot Predction</a></li>
              </ul>
              
               <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_walletuser.php">Manage User Wallet</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="update_activitybanner.php">Manage Activity Banner</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_custom_activitybanner.php">Activity Custom Banner</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_site_message.php">Site  Welcome Message</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_webmessage.php">Site Web Message</a></li>
              </ul>
          
          <?php
			}
			if($marketing == 1){
		  ?>
		  <li class="nav-item">
  <a class="nav-link" data-toggle="collapse" href="#ui-basic-7" aria-expanded="false" aria-controls="ui-basic-7">
    <i class="material-icons menu-icon">campaign</i>
    <span class="menu-title">Marketing</span>
    <i class="menu-arrow"></i>
  </a>
  <div class="collapse" id="ui-basic-7">

                <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="user_subtree.php">Users SubsTree</a></li>
              </ul>
                
                  <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="users_trunover.php">Users Turnover</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_turntable.php">TurnTable Manage</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_dailysignin.php">Daily Sign Bonus</a></li>
              </ul>
              
              
               <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="osscontens.php">Cloud Images Store</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="manage_rank.php">Users Rank</a></li>
              </ul>
                
             <?php
  }
  if ($agents == 1) {
?>
  <li class="nav-item">
    <a class="nav-link" data-toggle="collapse" href="#ui-basic-8" aria-expanded="false" aria-controls="ui-basic-8">
      <i class="material-icons menu-icon">supervisor_account</i> <!-- ðŸ‘¥ Agent icon -->
      <span class="menu-title">Agents</span>
      <i class="menu-arrow"></i>
    </a>
    <div class="collapse" id="ui-basic-8">
      <!-- Submenu items can go here -->
      
      

                <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="agentuser">Agent User</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="agents_management">Agent Summary</a></li>
              </ul>
              
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="fetch_agent_recharge">Agents Recharges</a></li>
              </ul>
                
		  <?php
			}
		  ?>
		 <li class="nav-item">
                    <a href="https://Sol-0203.io/home/" class="nav-link">
                        <i class="nav-icon fa fa-sign-out" aria-hidden="true"></i>
                        <p>Go To Website</p>
                    </a>
                </li>
        </ul>
        
        
        
        <!-- ✅ Place at the very bottom of compas.php -->
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

  document.addEventListener("DOMContentLoaded", function () {
    const current = window.location.pathname.split("/").pop();
    document.querySelectorAll(".nav-link").forEach(link => {
      const href = link.getAttribute("href");
      if (href && current === href) {
        link.classList.add("active");

        const collapseDiv = link.closest(".collapse");
        if (collapseDiv) {
          collapseDiv.classList.add("show");
          const toggleLink = document.querySelector(`a[href="#${collapseDiv.id}"]`);
          if (toggleLink) toggleLink.setAttribute("aria-expanded", "true");
        }
      }
    });
  });
</script>
