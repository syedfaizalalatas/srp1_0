<!DOCTYPE html>
<html>
<head>
	<title></title>
	<?php 
	require "../engine/mysqlidbconnect.php";
	require "../engine/function.php";
	if (isset($_POST['btn_simpan'])) {
		// echo "Memproses borang!";
    /* Getting file info and separating the name */
    $filename = $_FILES["nama_dok"]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension
    /* Find biggest doc id/code */
    fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);
    /* Create new name for file */
    $new_id=$_SESSION['biggest_doc_id']+1;
    $new_base_name = "srp_doc".$new_id."_";
    /* Rename file */
    $newfilename = $new_base_name . $file_ext;
    /* Setting the target directory */
    $target_dir = "../papers/";
    $target_file = $target_dir . $new_base_name . basename($_FILES["nama_dok"]["name"]);
    echo $target_file;
	}
	
	fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);
	$new_id=$_SESSION['biggest_doc_id']+1;
	// echo $new_id;
	$new_file_name = "srp_doc".$new_id.".pdf";
	// echo $new_file_name;



	?>
</head>
<body>
	<form action="testfilename.php" method="post" accept-charset="utf-8"  enctype="multipart/form-data">
		<div class="form-group">
			<label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
			</label>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<input type="file" id="nama_dok" name="nama_dok" accept=".pdf" class="file form-control col-md-7 col-xs-12">
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
				<input type="submit" class="btn btn-success" id="btn_simpan" name="btn_simpan" title="Simpan Rekod" value="Simpan">
				<button type="reset" class="btn btn-danger" title="Kosongkan Borang">Batal</button>
			</div>
		</div>
		<?php  
		echo "jajaja";
		?>
	</form>
	<p>asdfasdf</p>
	<p>asdfasdf</p>
	<p>asdfasdf</p>
	<h1>asdfasd</h1>
</body>
</html>