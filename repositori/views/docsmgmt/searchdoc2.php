<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
$_SESSION['page_title'] = "Carian Rekod Dokumen";
$_SESSION['simplesearch_form_title'] = "Borang Carian Mudah Dokumen";
$_SESSION['simplesearch_form_action'] = "Carian Mudah";
$_SESSION['advancedsearch_form_title'] = "Borang Carian Lengkap Dokumen";
$_SESSION['advancedsearch_form_action'] = "Carian Lengkap Dokumen Sedia Ada";
$_SESSION['table_title'] = "Jadual Rekod Dokumen";
$_SESSION['table_action'] = "Senaraikan Dokumen";
$actionfilename = "searchdoc.php";
$table01name = "dokumen";
$field01name = "kod_data";
$field02name = "nama_data";

# when simple search button is clicked
if (isset($_POST['sbmt_carian_mudah'])) {

}

# when advanced search button is clicked
if (isset($_POST['sbmt_carian_lengkap'])) {

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
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
					<select class="form-control selectpicker" id="kod_pilihan_carian" name="kod_pilihan_carian" required="required">
						<option value="1">Sila pilih...</option>
						<option value="2">Carian Mudah</option>
						<option value="3">Carian Lengkap</option>
					</select>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12" id="divsimplesearch" hidden>
				<div class="x_panel">
					<div class="x_title">
						<h2><?php echo $_SESSION['simplesearch_form_title']; ?><small>dd<?php echo $_SESSION['simplesearch_form_action']; ?></small></h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<br />
						<form id="form-data-baharu" action="<?php echo $actionfilename; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_data" hidden="hidden">Kod Data 
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<input type="text" id="kod_data" name="kod_data" maxlength="11" autofocus class="form-control col-md-7 col-xs-12" hidden="hidden">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_data">Nama Data <span class="required">*</span>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<input type="text" id="nama_data" name="nama_data" required="required" class="form-control col-md-7 col-xs-12">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="papar_data">Papar Data? 
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
	    <div class="clearfix"></div>
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12" id="divadvancedsearch" hidden>
				<div class="x_panel">
					<div class="x_title">
						<h2><?php echo $_SESSION['advancedsearch_form_title']; ?><small><?php echo $_SESSION['advancedsearch_form_action']; ?></small></h2>
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
	    <div class="clearfix"></div>
    </div>
		<!-- /page content -->
	    <script src="../vendors/jquery/dist/jquery.min.js"></script>
	    <script>
	    	$(document).ready(function(){
	    		$('#kod_pilihan_carian').on('change', function () {
	    			switch ($(this).val()) {
	    				case '1':
	    				$('#divsimplesearch').prop('hidden', true);
	    				$('#divadvancedsearch').prop('hidden', true);
	    				$('.selectpicker').selectpicker('refresh');
	    				break;
	    				case '2':
	    				$('#divsimplesearch').prop('hidden', false);
	    				$('#divadvancedsearch').prop('hidden', true);
	    				$('.selectpicker').selectpicker('refresh');
	    				break;
	    				case '3':
	    				$('#divsimplesearch').prop('hidden', true);
	    				$('#divadvancedsearch').prop('hidden', false);
	    				$('.selectpicker').selectpicker('refresh');
	    				break;
	    			}
	    		}); 
	    	});
	    </script>
	    <?php require "../layouts/lay_adminmainbottom.php"; ?>