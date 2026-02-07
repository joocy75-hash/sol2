<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:index.php?msg=unauthorized");
	}
?>
<?php
	include ("conn.php");
	
	$samasye = "SELECT atadaaidi FROM `gelluonduhogu_zehn` ORDER BY kramasankhye DESC LIMIT 1";
	$samasyephalitansa = $conn->query($samasye);
	$samasyephalitansa_dhadi = mysqli_fetch_assoc($samasyephalitansa);
	
	$munde = mysqli_query($conn,"SELECT sankhye FROM `hastacalita_phalitansa_zehn` WHERE `sthiti`='1'");
	if(mysqli_num_rows($munde)>0){
		$uhisi = mysqli_fetch_array($munde);
		$uhisisankhye = $uhisi['sankhye'];
	}
	else{
		$uhisisankhye = "NOT SET";			
	}
?>
<?php include 'header.php'; ?>

      </nav>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold text-dark">WinGo 30 sec</h4>
            </div>
          </div> 
		  <div class="row">
			<div class="col-sm-6 text-left">
              <h5>Count Down : <span id="demo"></span></h5>
			</div>
			<div class="col-sm-6 text-right">
			  <h5>Period Id : <span id="activeperiodid"><?php echo $samasyephalitansa_dhadi['atadaaidi'];?></span></h5>
			  <input type="hidden" name="periodid" id="periodid" value="<?php echo $samasyephalitansa_dhadi['atadaaidi'];?>">	
			</div>
          </div>
		  <div class="row">
            <div class="col-sm-12">
              <h5 style="text-align:center; color:red">Next prediction : <?php echo $uhisisankhye; ?> </h5>
			  <form action="itticina_geluvu_zehn" id="pre" method="POST">
				<h6 style="text-align:center; font-weight:bold">Prediction Form</h6>
				<div class="d-flex align-items-center">				
					<input type="text" name="username" id="next" placeholder="Enter a number from 0-9" class="flex-grow-1 cool-input" style="height: 40px;">					
				</div>
				<div class="d-flex align-items-center mt-3">
					<button type="button" onclick="sub()" class="btn btn-primary cool-button mr-2">Confirm Next Prediction</button>
					<button type="button" onclick="unsetman()" class="btn btn-secondary cool-button">Unset Prediction</button>
				</div>
			  </form>
            </div>
          </div>		  			
		  <div class="row">
            <div class="col-sm-12"> 
			  <div class="d-flex align-items-center mt-3">	
				<p>
					<span style="color:#333">TOTAL BET AMOUNT : </span><span id = "tobet" style="color:#333">0  </span>
				</p>
			  </div>
			  <table id="example1" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Result</th>
						<th>Number</th>
						<th>Bet</th>
						<th>No. of User</th>
						<th>Amount to Pay</th>
					</tr>
				</thead>
				<tbody id="betdetail">
					<?php 
						$samasye = mysqli_query($conn,"select * from `hastacalita_phalitansa_zehn`");
						$i=0;
						while($dhadi = mysqli_fetch_array($samasye)){
							$i++;
					?>
							<tr>
								<td><?php echo $dhadi["banna"]; ?></td>
								<td><?php echo $dhadi["sankhye"]; ?></td>
								<td class="text-orange">wait..</td>
								<td class="text-orange">wait..</td>
								<td class="text-orange">wait..</td>
							</tr>
					<?php
						}
					?>
				</tbody>
			  </table>
            </div>			
          </div>
		  <div class="row">
            <div class="col-sm-12">
				<div class="d-flex align-items-center mt-3">
					<h5>Live Bets</h5>
				</div>
				<table id="example2" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>User ID</th>
							<th>Value</th>
							<th>Amount</th>
							<th>Mobile</th>
							<th>Balance</th>
						</tr>
					</thead>
					<tbody>
		 
					</tbody>
				</table>
			</div>
		  </div>
		  <div class="row">
			<div class="col-sm-12 offset-sm-4 mt-3">
				<h5 id="copied" style="text-align:center; font-weight:bold">Enter a number from 0-9</h5>
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
  © <?= date('Y') ?> Sol-0203 All rights reserved. | <span style="color: #e0ffe0;">Patented & Protected</span>.
</footer>

<style>

/* === Global Theme === */
body {
  background-color: #000000;
  color: #00bfff;
  font-family: 'Segoe UI', sans-serif;
}

h4, h5, h6 {
  color: #00eaff;
}

a {
  color: #00eaff;
  text-decoration: none;
}

a:hover {
  color: #00ffee;
}

/* === Countdown & Period === */
#demo, #activeperiodid {
  font-weight: bold;
  color: #0ff;
}

/* === Inputs & Buttons === */
.cool-input {
  background-color: #fff;
  color: #0ff;
  border: 1px solid #00eaff;
  border-radius: 5px;
  padding: 0 10px;
}

.cool-input::placeholder {
  color: #777;
}

.cool-button {
  background: linear-gradient(45deg, #008cff, #00f0ff);
  border: none;
  color: #000;
  font-weight: bold;
  border-radius: 5px;
  padding: 10px 15px;
  transition: 0.3s ease-in-out;
}

.cool-button:hover {
  background: #00eaff;
  color: #000;
}

/* === Tables === */
table {
  background-color: #fff;
  color: #0ff;
  border: 1px solid #00eaff;
}

table thead {
  background-color: #002244;
  color: #00eaff;
}

table td, table th {
  border: 1px solid #00eaff;
}

.text-orange {
  color: #ffaa33;
}

/* === Custom Classes === */
.text-right {
  text-align: right;
}

.text-left {
  text-align: left;
}

.text-center {
  text-align: center;
}

.btn {
  padding: 6px 12px;
  font-size: 14px;
}

/* === Responsive === */
@media screen and (max-width: 768px) {
  h4, h5, h6 {
    font-size: 90%;
  }

  .cool-button {
    width: 100%;
    margin-bottom: 10px;
  }
}


@keyframes rgbflow {
  0%   { background-position: 0% 50%; }
  50%  { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>

      </div>
    </div>
  </div>
  
  <script>
	$(function () {
		$('#example1').DataTable({
		  "paging": false,
		  "lengthChange": false,
		  "searching": false,
		  "ordering": false,
		  "info": false,
		  "autoWidth": true,
		  "pageLength": 15
		});
	});
	$(document).ready(function () {
		var xyz = setInterval(function() { 
		wingoonetotal();
		getbettingdata('getdata');
		}, 2000);
	});
	function wingoonetotal()
	{
		$.ajax({
		type: "Post",
		url: "ottu_gellaluhogiondu_zehn.php",
		success: function (html) {
		 document.getElementById("tobet").innerHTML = html;		 
		  return false;
		  },
		  error: function (e) {}
		  });
	}
	function getbettingdata(actiontype)
	{
		var periodid=$("#periodid").val();
			$.ajax({
			type: "Post",
			data:"periodid=" + periodid + "& actiontype=" + actiontype ,
			url: "mottavannupadeyiri_zehn.php",
			success: function (html) {
			 document.getElementById("betdetail").innerHTML = html;
			  return false;
			  },
			  error: function (e) {}
			  });
	}
	function fetchServerTime() {
		return fetch('servertime')
			.then(response => {
				if (!response.ok) {
					throw new Error(`Failed to fetch server time. Status: ${response.status}`);
				}
				return response.json().then(data => data.serverTime);
			});
	}
	function sub(){
		var p=document.getElementById("next").value;
		if(p==''){			 
		   var x = document.getElementById("copied");
			x.className = "show";
			setTimeout(function () { x.className = x.className.replace("show", ""); }, 3000); 
		}else if(-1<p && p<10){
			console.log(p);
		 document.getElementById("pre").submit();  
		}else{
			 console.log("3");
			var x = document.getElementById("copied");
			x.className = "show";
			setTimeout(function () { x.className = x.className.replace("show", ""); }, 3000); 
		}		
	}
	
	setInterval(function() {
	var table = $('#example2').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": "detavannunirvahisi_zehn.php",
		"paging": true,
		"lengthChange": false,
		"searching": true,
		"ordering": false,
		"info": true,
		"autoWidth": true,
		"pageLength": 50,
		"dom": 'lrtip',
		"bDestroy": true
	});
	}, 2000);
	
	function resetman(){		
		$.ajax({
			type: "Post",
			data:"stat=" + "1",
			url: "maruhondisi_gellalu_zehn.php",
			success: function (html) {
			 console.log(html);
			  return false;
			  },
			  error: function (e) {}
		});
	}
	function unsetman(){
		resetman();
		location.reload();
	}
	
	function startTimer() {
    function updateTimer() {
        const currentTime = Math.floor(Date.now() / 1000);
        const cycleStart = Math.floor(currentTime / 30) * 30;
        const nextCycle = cycleStart + 30;
        const remainingSeconds = nextCycle - currentTime;

        const displaySeconds = remainingSeconds.toString().padStart(2, '0');
        document.getElementById("demo").innerHTML = `<span class='timer'>00</span><span>:</span><span class='timer'>${displaySeconds}</span>`;
        
        if (remainingSeconds === 29) {
            resetman();
        }
        if (remainingSeconds <= 1) {
            location.reload();
        }
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}
$(document).ready(function() {
    startTimer();
    
    setInterval(function() {
        $.ajax({
            type: "Post",
            url: "ottu_gellaluhogiondu_zehn.php",
            success: function(html) {
                $("#tobet").html(html);
            }
        });
        
        let periodid = $("#periodid").val();
        $.ajax({
            type: "Post",
            data: "periodid=" + periodid + "&actiontype=getdata",
            url: "mottavannupadeyiri_zehn.php",
            success: function(html) {
                $("#betdetail").html(html);
            }
        });
    }, 2000);
});

function sub() {
    let value = $("#next").val();
    if (value === '' || value < 0 || value > 9) {
        let notice = $("#copied");
        notice.addClass("show");
        setTimeout(() => notice.removeClass("show"), 3000);
    } else {
        $("#pre").submit();
    }
}

function unsetman() {
    $.ajax({
        type: "Post",
        data: "stat=1",
        url: "maruhondisi_gellalu_zehn.php",
        success: function() {
            location.reload();
        }
    });
}
  </script>
</body>

</html>