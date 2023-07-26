<?php

	require("db.php");

	function check_base64_image($base64) {
		 
		$base64 = str_ireplace("data:image/png;base64,","",$base64);
		
		$img = imagecreatefromstring(base64_decode($base64));
		if (!$img) {
			echo "00";
			return false;
		}
		
		$it = time();

		imagepng($img, __DIR__ . "/tmp/".$it.'.png');
		$info = getimagesize(__DIR__ . "/tmp/".$it.'.png');
		 

		unlink(__DIR__ . "/tmp/".$it.'.png');
		
		if($info[0] != 128 || $info[1] != 128) { return false; }

		if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
			return true;
		}

		return false;
	}
	
	$_POST = json_decode(file_get_contents('php://input'),true);
	
	if(check_base64_image($_POST['image'])) {
		
		$stmt = mysqli_prepare($con, "INSERT INTO pictures (pic) VALUES (?)");
		mysqli_stmt_bind_param($stmt, "s", $_POST['image']);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		
		echo "1";
	}
	else { echo "0"; }