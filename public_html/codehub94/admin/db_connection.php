<?php
	date_default_timezone_set('Asia/Kolkata');

	define('DB_SERVER', 'localhost');
	define('DB_USERNAME', 'u209477126_sol0203');
	define('DB_PASSWORD', 'UP209477126_sol0203');
	define('DB_NAME', 'u209477126_sol0203');

	$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
	if($conn == false){
		dir('Error: Cannot connect');
		echo "Fail";
	}
?>	