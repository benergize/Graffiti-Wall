<!DOCTYPE html>
<html>
	<head>
	
		<meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" />

		<title>Graffiti Wall</title> 
	</head>
	<body style = ''>
		<?php

			require("db.php");

			$t = date("Y-m-d");
			
			$q = mysqli_query($con, "SELECT * FROM pictures WHERE tstamp > $t");
			
			$squareSize = sqrt(mysqli_num_rows($q));
			$i = 0;
			
			echo "<div>";
			
			while($row = mysqli_fetch_assoc($q)) {
				
				if($i % ceil($squareSize) == 0 && $i != 0) { echo "<br/>"; }
				$i++;
				
				echo "<img width=128 height=128 src = '".$row['pic']."' data-id = '".$row['pic_id']."' style = 'width:128px;height:128px;' title = '".$row['tstamp']."'>";
			}
			
			echo "</div>";
		?>
		
		<script>window.scrollTo(document.body.scrollWidth/2 - window.innerWidth/2,document.body.scrollHeight/2 - window.innerHeight/2) </script>
		<style>
			body { display:flex;justify-content:center;align-items:center;min-width:100vw;min-height:100vh;overflow:auto;margin:0px; }
			
			@media only screen and (max-width: <?php echo round(128+($squareSize * 128)); ?>px) {
				br {display:none;}
				div {text-align:center;}
			}
		</style>
	</body>
</html>