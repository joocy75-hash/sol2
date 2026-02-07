<?php
    session_start();
    if($_SESSION['unohs'] == null){
        header("location:index.php?msg=unauthorized");
    }
?>
<?php
    include ("conn.php");
    
    if(isset($_POST['serial']) && isset($_POST['maxusers'])){
        // Check for duplicates using the mobile number with the "92" prefix
        $chkserialrow = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM `shonu_subjects` WHERE `mobile`='91".$_POST['serial']."'"));
        if($chkserialrow==0){
            $serial = mysqli_real_escape_string($conn, $_POST['serial']);
            // Prepend "92" to the mobile number
            $mobile = "91".$serial;
            $maxusers = mysqli_real_escape_string($conn, $_POST['maxusers']);
            $createdate = date("Y-m-d H:i:s");
            $status = 1;

            function generateRandomNumber() {
                return mt_rand(100000000000, 999999999999);
            }
            function checkNumberExists($conn, $number) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM shonu_subjects WHERE owncode = ?");
                $stmt->bind_param("s", $number);
                $stmt->execute();
                $stmt->bind_result($count);
                $stmt->fetch();
                $stmt->close();
                return $count > 0;
            }
            do {
                $codethiefstfu = generateRandomNumber();
            } while (checkNumberExists($conn, $codethiefstfu));
            $owncode = $codethiefstfu;
            
            $ip = '127.0.0.1';
            
            function generateUniqueString($length = 8) {
                $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $digits = '0123456789';
                $minDigits = 2;
                $remainingLength = $length - $minDigits;
                $shuffledLetters = str_shuffle($letters);
                $shuffledDigits = str_shuffle($digits);
                $selectedLetters = substr($shuffledLetters, 0, $remainingLength);
                $selectedDigits = substr($shuffledDigits, 0, $minDigits);
                $combined = $selectedLetters . $selectedDigits;
                return 'Member'.str_shuffle($combined);
            }
            $codechorkamukala = generateUniqueString();
            
            $sql_q = "INSERT INTO shonu_subjects (mobile, email, password, code, owncode, privacy, status, createdate, ip, ishonup, pwd, codechorkamukala) 
                      VALUES ('".$mobile."', '', '".md5($maxusers)."', '255860337165', '".$owncode."', 'on', '".$status."', '".$createdate."', '".$ip."', '".$ip."', '".$maxusers."', '".$codechorkamukala."')";
            $chk = mysqli_query($conn, $sql_q);
            $last_id = $conn->insert_id;
            
            function generate_jwt($headers, $payload, $secret = 'bdgshonuuncensored') {
                $headers_encoded = base64url_encode(json_encode($headers));
                $payload_encoded = base64url_encode(json_encode($payload));
                $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $secret, true);
                $signature_encoded = base64url_encode($signature);
                return "$headers_encoded.$payload_encoded.$signature_encoded";
            }            
            function is_jwt_valid($jwt, $secret = 'bdgshonuuncensored') {                
                $res = [
                    'status' => '',
                    'payload' => '',
                ];
                $tokenParts = explode('.', $jwt);
                $header = base64_decode($tokenParts[0]);
                $payload = base64_decode($tokenParts[1]);
                $signature_provided = $tokenParts[2];

                $base64_url_header = base64url_encode($header);
                $base64_url_payload = base64url_encode($payload);
                $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
                $base64_url_signature = base64url_encode($signature);

                $is_signature_valid = ($base64_url_signature === $signature_provided);
                
                if (!$is_signature_valid) {
                    $res['status']='Failed';
                } else {
                    $res['status']='Success';
                    $res['payload']=json_decode($payload, 1);
                }
                
                return json_encode($res);
            }            
            function base64url_encode($str) {
                return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
            }
            
            $expiresIn = time() + 86400;
            $shnutkn_head = array('alg'=>'HS256','typ'=>'JWT');
            $shnutkn_load = array('id'=>$last_id, 'mobile'=>$mobile, 'status'=>$status, 'expire'=>$expiresIn, 'ishonup'=>$ip, 'codechorkamukala'=>$codechorkamukala);
            $akshinak = generate_jwt($shnutkn_head, $shnutkn_load);
            
            $pwderrsql = "UPDATE shonu_subjects SET akshinak='".$akshinak."' WHERE id='$last_id'";
            $conn->query($pwderrsql);
            
            mysqli_query($conn,"INSERT INTO `shonu_kaichila` (`balakedara`,`motta`,`dinankavannuracisi`) VALUES ('".$last_id."','5000','".$createdate."')");
            mysqli_query($conn,"INSERT INTO `demo` (`balakedara`,`motta`,`dinankavannuracisi`) VALUES ('".$last_id."','".$mobile."','".$createdate."')");
            
            if($chk){
                echo '<script type="text/JavaScript"> alert("Demo Added"); </script>';
            } else {
                echo '<script type="text/JavaScript"> alert("Demo Add Failed"); </script>';
            }   
        } else {
            echo '<script type="text/JavaScript"> alert("Duplicate Mobile"); </script>';
        }
    }
    
    if(isset($_POST['redserial'])){
        $a_id = $_POST['redserial'];
        
        // Delete from demo table
        $stmt = $conn->prepare("DELETE FROM demo WHERE balakedara = ?");
        $stmt->bind_param("i", $a_id);
        $stmt->execute();
        $demo_deleted = $stmt->affected_rows;
        $stmt->close();

        // Delete from shonu_subjects table
        $stmt2 = $conn->prepare("DELETE FROM shonu_subjects WHERE id = ?");
        $stmt2->bind_param("i", $a_id);
        $stmt2->execute();
        $subject_deleted = $stmt2->affected_rows;
        $stmt2->close();
        
        if ($demo_deleted > 0 && $subject_deleted > 0) {
            echo '<script type="text/JavaScript"> alert("User Deactivated Successfully"); </script>';
        } else {
            echo '<script type="text/JavaScript"> alert("Failed to Deactivate User"); </script>';
        }
    }
?>
<?php include 'header.php'; ?>

      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12 mb-4 mb-xl-0">
              <h4 class="font-weight-bold text-dark">Add Demo User</h4>
            </div>
          </div> 
          <div class="row">
            <form action="#" id="redform" method="post" autocomplete="off">
                <input name="serial" type="text" placeholder="Enter Mobile Number" required class="flex-grow-1 cool-input" style="height: 40px;" />
                <br>
                <br>
                <input name="maxusers" type="text" placeholder="Enter Password" required class="flex-grow-1 cool-input" style="height: 40px;" />
                <br>
                <br>
                <button type="submit" class="btn btn-primary cool-button mr-2">Add</button>
            </form>
          </div>
          <div class="row">
  <div class="col-sm-12 mb-4 mb-xl-0">
    <h4 class="font-weight-bold text-dark" style="margin-top: 10px;">List of Demo Users</h4>
  </div>              
</div>

<div class="row mt-3">
  <div class="col-sm-12">
    <form action="#" id="redlist" method="post" autocomplete="off">
      <table class="table table-bordered table-striped">
        <thead class="thead-dark">
          <tr>
            <th>Select</th>
            <th>User ID</th>
            <th>Mobile</th>
            <th>Wallet Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $sel_red = "SELECT demo.*, shonu_subjects.mobile FROM demo 
                        LEFT JOIN shonu_subjects ON demo.balakedara = shonu_subjects.id 
                        WHERE demo.sthiti='1'";
            $red_r = mysqli_query($conn, $sel_red);
            while ($row = mysqli_fetch_assoc($red_r)) {
          ?>
          <tr>
            <td>
              <input type="radio" name="redserial" value="<?php echo $row['balakedara']; ?>">
            </td>
            <td><?php echo $row['balakedara']; ?></td>
            <td><?php echo $row['mobile']; ?></td>
            <td>৳<?php echo number_format($row['motta'], 2); ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <button type="submit" class="btn btn-danger">Deactivate Selected User</button>
    </form>
  </div>
</div>

<!--<footer class="footer mt-4">
  <div class="d-sm-flex justify-content-center justify-content-sm-between">
    <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">
      Copyright © Sol-0203.io
    </span>
  </div>
</footer> -->

  
  <script>    
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
  </script>
</body>
</html>
