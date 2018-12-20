<?php 
require '../engine/pdodbc_srp.php';
require '../engine/function.php';
session_start(); 
$deleteID = $_GET['id'];
// fnRunAlert($deleteID);
$deleteSource = $_GET['source'];
// fnRunAlert($deleteSource);
$DBServer       = $_SESSION['DBServer'];
$DBUser         = $_SESSION['DBUser'];
$DBPass         = $_SESSION['DBPass'];
$DBName         = $_SESSION['DBName'];
$conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fnInsertNewAuditLog() {

}
	$logStmt = $conn->prepare("INSERT INTO log_audit (masa, id_pengguna, operasi, jadual, id_terlibat, ip_pengguna) VALUES (?, ?, ?, ?, ?, ?)");
	if($logStmt === false) {
	    trigger_error('Wrong SQL: ' . $delStmt . ' Error: ' . $conn->error, E_USER_ERROR);
	}
	$logStmt->bind_param("sissis", $masa_operasi, $id_pengguna_operasi, $operasi, $jadual_operasi, $id_terlibat_operasi, $ip_pengguna_operasi);

	// Function to get the client IP address
	function get_client_ip() {
	    $ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}


	$ip_pengguna_operasi = get_client_ip();
	// fnRunAlert($ip_pengguna_operasi);
	$date = new DateTime(null, new DateTimeZone('Asia/Kuala_Lumpur'));
	// echo $date->format('Y-m-d H:i:s') . "<br>";
	$masa_operasi = $date->format('Y-m-d H:i:s');
	// fnRunAlert($masa_operasi);
	$id_pengguna_operasi = $_SESSION["loggedinid"];
	$operasi = "Hapus rekod dokumen.";
	$jadual_operasi = "dokumen";
	$id_terlibat_operasi = $deleteID;
	$logStmt->execute();
	$logStmt->close();
// fnInsertNewAuditLog();


$delStmt = $conn->prepare("UPDATE dokumen SET tanda_hapus=? WHERE kod_dok = ?");
// $delStmt = $pdo->prepare("UPDATE dokumen SET tanda_hapus=? WHERE kod_dok = ?");
// $delStmt = $pdo->prepare("DELETE FROM dokumen WHERE kod_dok = ?");
/*
tambahan 7 bari di bawah komen ini oleh syedfaizalalatas pada 20180717 1137
 */
// $delStmt = $conn->prepare($delStmt);
if($delStmt === false) {
    trigger_error('Wrong SQL: ' . $delStmt . ' Error: ' . $conn->error, E_USER_ERROR);
}
/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
$delStmt->bind_param("ii", $nilaiHapus, $deleteID);
$nilaiHapus = 0;
$delStmt->execute();
// $deleted = $delStmt->rowCount();
$delStmt->close();
$conn->close();
if ($deleteSource == "l") {
	$url = "listdoc.php?s=n";
}
else {
	$url = "searchdoc_o.php";
}
$_SESSION['sourceOfDeletion'] = "";
// fnRunAlert($url);
fnRunAlert("Rekod telah dihapuskan.");
?>
<script type="text/javascript">location.href = '<?php echo $url; ?>';</script>
<?php

// echo "<meta http-equiv=\"refresh\" content=\"0; url=$url\">";

?>