<!DOCTYPE html>
<html>
<head>
	<title></title>
	<?php  
	session_start();
	if (!isset($submit)) {
		?>
		<?php
		echo "Dah tekan<br>";
		for ($i=0; $i <= $_SESSION['bil_teras']; $i++) {
			echo $_POST["option$i"].'<br>';
		}
	}
	?>
</head>
<body>
	<form action="testarray.php" method="post" accept-charset="utf-8">
		<?php  
		$_SESSION['testmultiarray'] = array();
		for ($row=0; $row < 4; $row++) { 
			for ($col=0; $col < 3; $col++) { 
						$_SESSION['testmultiarray'][$row][$col] = $row."-".$col;
			}		
		}
		for ($row=0; $row < 4; $row++) { 
			for ($col=0; $col < 3; $col++) { 
						echo $_SESSION['testmultiarray'][$row][$col]." ";
			}
			echo "<br/>";		
		}
		?>
		<input type="submit" name="submit" id="submit" value="Submit">
		<br>
		<br>
		<br>
	</form>
</body>
</html>