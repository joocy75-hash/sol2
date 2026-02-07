<?php
	$conn = mysqli_connect('localhost', 'u209477126_sol0203', 'UP209477126_sol0203', 'u209477126_sol0203');
	
	if (!$conn) {
		echo "Error: " . mysqli_connect_error();
		exit();
	}
	
	date_default_timezone_set("Asia/Kolkata"); 
?>