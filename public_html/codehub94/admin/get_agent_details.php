<?php
include("conn.php");

$uid = intval($_GET['uid']);
$q = mysqli_query($conn, "SELECT * FROM dailysalary WHERE userid = $uid ORDER BY createdate DESC LIMIT 1");
$row = mysqli_fetch_assoc($q);

if (!$row) {
  echo "No salary record found.";
  exit;
}

echo "<b>User ID:</b> " . $row['userid'] . "<br>";
echo "<b>Success Recharge:</b> " . $row['totalsucrech'] . "<br>";
echo "<b>Fail Recharge:</b> " . $row['totalfailrech'] . "<br>";
echo "<b>Success Bet:</b> " . $row['totalsucbet'] . "<br>";
echo "<b>Fail Bet:</b> " . $row['totalfailbet'] . "<br>";
echo "<b>Salary:</b> ৳" . number_format($row['salary']) . "<br>";
echo "<b>Date:</b> " . $row['createdate'] . "<br><br>";

echo "<b>Recharge Users ✅:</b><br><pre>" . $row['tsruser'] . "</pre>";
echo "<b>Recharge Users ❌:</b><br><pre>" . $row['tfruser'] . "</pre>";
echo "<b>Bet Users ✅:</b><br><pre>" . $row['tsbuser'] . "</pre>";
echo "<b>Bet Users ❌:</b><br><pre>" . $row['tfbuser'] . "</pre>";
