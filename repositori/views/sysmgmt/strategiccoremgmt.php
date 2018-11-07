<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
$_SESSION['page_title'] = "Pengurusan Rekod Teras Strategik";
$_SESSION['addnew_form_title'] = "Borang Tambah Rekod Teras Strategik";
$_SESSION['addnew_form_action'] = "Simpan Teras Strategik Baharu";
$_SESSION['update_form_title'] = "Borang Kemaskini Rekod Teras Strategik";
$_SESSION['update_form_action'] = "Kemaskini Teras Strategik Sedia Ada";
$_SESSION['table_title'] = "Jadual Rekod Teras Strategik";
$_SESSION['table_action'] = "Senaraikan Teras Strategik";
$actionfilename = "strategiccoremgmt.php";
$table01name = "teras_strategik";
$field01name = "kod_teras";
$field02name = "nama_teras";
$_SESSION['code_display_status'] = "hidden";

// when user clicked update button in update form
if (isset($_POST['btn_kemaskini_data'])) {
	$_SESSION['kod_data'] = $_POST['kod_data'];
	$_SESSION['nama_data'] = $_POST['nama_data'];
	if (!isset($_POST['papar_data_form'])) {
		$_SESSION['papar_data'] = "";
	}
	else {
		$_SESSION['papar_data'] = $_POST['papar_data_form'];
	}
	$_SESSION['id_pengemaskini'] = $_SESSION['loggedinid'];
	$_SESSION['tkh_kemaskini'] = date("Y-m-d H:i:s");
	?>
	<script>
		// alert("<?php echo "Kod:".$_SESSION['kod_data']; ?>\n<?php echo "Nama:".$_SESSION['nama_data']; ?>\n<?php echo "Papar:".$_SESSION['papar_data']; ?>");
	</script>
	<?php
	fnUpdateData($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name,$_POST['kod_data']);
}

// when user clicked 'simpan'
if (isset($_POST['btn_simpan_data_contoh'])) {
	$_SESSION['kod_data'] = $_POST['kod_data'];
	$_SESSION['nama_data'] = $_POST['nama_data'];
	if (!isset($_POST['papar_data'])) {
		$_SESSION['papar_data'] = "";
	}
	else {
		$_SESSION['papar_data'] = $_POST['papar_data'];
	}
	$_SESSION['id_pengemaskini'] = $_SESSION['loggedinid'];
	$_SESSION['tkh_kemaskini'] = date("Y-m-d H:i:s");
	fnInsertNewData($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name);
}
?>
<!-- page content -->
<div class="right_col" role="main">
	<div class="">
		<div class="page-title">
			<div class="title_left">
				<h3><?php echo $_SESSION['page_title']; ?></h3>
			</div>

			<!-- <div class="title_right"> -->
			<!-- <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search"> -->
			<!-- <div class="input-group"> -->
			<!-- <input type="text" class="form-control" placeholder="Search for..."> -->
			<!-- <span class="input-group-btn"> -->
			<!-- <button class="btn btn-default" type="button">Go!</button> -->
			<!-- </span> -->
			<!-- </div> -->
			<!-- </div> -->
			<!-- </div> -->
		</div>
		<div class="clearfix"></div>
		<?php  
		// if update button in table is not clicked
		// this is the new data form
		if (!isset($_POST['btn_kemaskini_data_contoh1'])) {
			?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div class="x_panel">
						<div class="x_title">
							<h2><?php echo $_SESSION['addnew_form_title']; ?><small><?php echo $_SESSION['addnew_form_action']; ?></small></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<br />
							<form id="form-data-baharu" action="<?php echo $actionfilename; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
								<div class="form-group">
									<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_data" hidden="hidden">Kod Teras Strategik 
									</label>
									<div class="col-md-6 col-sm-6 col-xs-12">
										<input type="text" id="kod_data" name="kod_data" maxlength="11" autofocus class="form-control col-md-7 col-xs-12" hidden="hidden">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_data">Nama Teras Strategik <span class="required">*</span>
									</label>
									<div class="col-md-6 col-sm-6 col-xs-12">
										<input type="text" id="nama_data" name="nama_data" required="required" class="form-control col-md-7 col-xs-12">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-md-3 col-sm-3 col-xs-12" for="papar_data">Papar Teras Strategik? 
									</label>
					    			<div class="checkbox">
					    				<label>
					    					<input type="checkbox" id="papar_data" name="papar_data" value="1" checked class="flat"> 
					    				</label>
					    			</div>
								</div>
								<div class="form-group">
									<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
										<input type="submit" id="btn_simpan_data_contoh" name="btn_simpan_data_contoh" class="btn btn-success" value="Simpan">
										<button type="reset" class="btn btn-danger">Batal</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<?php  
		// if update button in table is clicked
		if (isset($_POST['btn_kemaskini_data_contoh1'])) {
			?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div class="x_panel">
						<div class="x_title">
							<h2><?php echo $_SESSION['update_form_title']; ?><small><?php echo $_SESSION['update_form_action']; ?></small></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<br />
							<form id="form-kemaskini-data" action="<?php echo $actionfilename; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
								<?php 
								fnShowUpdateFormContent($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name,$_POST['btn_kemaskini_data_contoh1']); 
								?>
								<div class="form-group">
									<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
										<input type="submit" id="btn_kemaskini_data" name="btn_kemaskini_data" class="btn btn-success" title="Kemaskini Data" value="Kemaskini">
										<button type="submit" class="btn btn-danger">Batal</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="clearfix"></div>
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
					<div class="x_title">
						<h2><?php echo $_SESSION['table_title']; ?> <small><?php echo $_SESSION['table_action']; ?></small></h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<p class="text-muted font-13 m-b-30">
							<!-- The Buttons extension for DataTables provides a common set of options, API methods and styling to display buttons on a page that will interact with a DataTable. The core library provides the based framework upon which plug-ins can built. -->
						</p>
						<form id="form-jadual-data" action="<?php echo $actionfilename; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
						<table id="datatable-buttons" class="table table-striped table-bordered">
							<thead>
								<tr>
									<th width="40">Bil</th>
									<th width="100" <?php echo $_SESSION['code_display_status']; ?>>Kod</th>
									<th>Nama</th>
									<th>Status</th>
									<th>Tindakan</th>
								</tr>
							</thead>


							<tbody>
								<?php 
								fnShowDataTableContent($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name); 
								?>
							</tbody>
						</table>
						</form>
					</div>
				</div>
			</div>
		</div>
		<br>&nbsp;
		<br>&nbsp;
		<br>&nbsp;
		<!-- Habis borang dummy baharu -->
		<!-- /page content -->
		<?php require "../layouts/lay_adminmainbottom.php"; ?>