<?php
	$conn = mysqli_connect('localhost', 'lottery24db', 'lottery24db', 'lottery24db');
	
	if (!$conn) {
		echo "Error: " . mysqli_connect_error();
		exit();
	}
	
	date_default_timezone_set("Asia/Kolkata"); 
?>