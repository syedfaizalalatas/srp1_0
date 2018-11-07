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
		$_SESSION['bil_teras']=4; // tak termasuk id=1
		for ($i=0; $i <= $_SESSION['bil_teras']; $i++) { 
			$j=$i+1;
			echo '<input type="checkbox" name="option'.$i.'" id="option'.$i.'" value="'.$j.'" title="'.$j.'"> '.$j.'<br>';
		}
		?>
		<input type="submit" name="submit" id="submit" value="Submit">
		<br>
		<br>
		<br>
	</form>
</body>
</html>