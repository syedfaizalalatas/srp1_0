<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
fnClearSessionForListPages();
$_SESSION['page_title'] = "Pengurusan Rekod Pengguna";
$_SESSION['addnew_form_title'] = "Borang Tambah Rekod Pengguna";
$_SESSION['addnew_form_action'] = "Simpan Rekod Pengguna Baharu";
$_SESSION['update_form_title'] = "Borang Kemaskini Rekod Pengguna";
$_SESSION['update_form_action'] = "Kemaskini Rekod Pengguna Sedia Ada";
$_SESSION['table_title'] = "Jadual Rekod Pengguna";
$_SESSION['table_action'] = "Senaraikan Pengguna";
$actionfilename = "listuser.php";

if (isset($_POST['btn_kemaskini_rekod_pengguna'])) {
  // fnRunAlert("Memproses borang...");
  /* nama_penuh */
  $_SESSION['nama_penuh']=$_POST['nama_penuh'];
  $_SESSION['nama_penuh'] = checkAndRevalue($_SESSION['nama_penuh']);
  /* kod_gelaran */
  $_SESSION['kod_gelaran_nama']=$_POST['kod_gelaran_nama'];
  $_SESSION['kod_gelaran_nama'] = checkAndRevalue($_SESSION['kod_gelaran_nama']);
  /* nama_pengguna */
  $_SESSION['nama_pengguna']=$_POST['nama_pengguna'];
  $_SESSION['nama_pengguna'] = checkAndRevalue($_SESSION['nama_pengguna']);
  /* kata_laluan */
  $_SESSION['kata_laluan']=$_POST['kata_laluan'];
  $_SESSION['kata_laluan'] = checkAndRevalue($_SESSION['kata_laluan']);
  /* kata_laluan2 */
  $_SESSION['kata_laluan2']=$_POST['kata_laluan2'];
  $_SESSION['kata_laluan2'] = checkAndRevalue($_SESSION['kata_laluan2']);
  /* garam */
  $_SESSION['garam']=$_POST['garam'];
  $_SESSION['garam'] = checkAndRevalue($_SESSION['garam']);
  /* emel */
  $_SESSION['emel']=$_POST['emel'];
  $_SESSION['emel'] = checkAndRevalue($_SESSION['emel']);
  /* kod_kem */
  $_SESSION['kod_kem']=$_POST['kod_kem'];
  $_SESSION['kod_kem'] = checkAndRevalue($_SESSION['kod_kem']);
  /* kod_jab */
  $_SESSION['kod_jab']=$_POST['kod_jab'];
  $_SESSION['kod_jab'] = checkAndRevalue($_SESSION['kod_jab']);
  /* pentadbir_sistem */
  $_SESSION['pentadbir_sistem']=$_POST['pentadbir_sistem'];
  $_SESSION['pentadbir_sistem'] = checkAndRevalueCheckbox($_SESSION['pentadbir_sistem']);
  /* pentadbir_dokumen */
  $_SESSION['pentadbir_dokumen']=$_POST['pentadbir_dokumen'];
  $_SESSION['pentadbir_dokumen'] = checkAndRevalueCheckbox($_SESSION['pentadbir_dokumen']);
  /* pentadbir_pengguna */
  $_SESSION['pentadbir_pengguna']=$_POST['pentadbir_pengguna'];
  $_SESSION['pentadbir_pengguna'] = checkAndRevalueCheckbox($_SESSION['pentadbir_pengguna']);
  /* jumlah mata peranan */
  $_SESSION['jum_mata_peranan'] = $_SESSION['pentadbir_sistem'] + $_SESSION['pentadbir_dokumen'] + $_SESSION['pentadbir_pengguna'];
  /* status_pengguna */
  $_SESSION['status_pengguna']=$_POST['status_pengguna'];
  /* id_pengemaskini (bagi pengguna baharu dan pengemaskinian) */
  $_SESSION['id_pengemaskini']=$_SESSION['loggedinid'];
  $_SESSION['id_pengemaskini'] = checkAndRevalue($_SESSION['id_pengemaskini']);
  /* tarikh pengguna dikemaskini */
  $_SESSION['tarikh_kemaskini'] = date("Y-m-d H:i:s");
  /* status simpan rekod pengguna baharu */
  $_SESSION['status_simpan_pengguna_baharu']='0'; 
  // 0=belum berjaya, 1=berjaya
  #------------------------------#
  # required input all set? - OK 20161020
  # pattern all set?
  # maxlength all set? - OK 20161020
  # start verifying form
  $_SESSION['verifiedOK'] = 3; // initial value
  if ($_SESSION['verifiedOK'] != 0) {
    # 1. user logged in?
    # proses penyimpanan rekod jika ada loggedinid
    if ($_SESSION['loggedinid'] != 0) {
      $_SESSION['verifiedOK'] = 1;
    }
    else {
      $_SESSION['verifiedOK'] = 0;
      fnRunAlert("Maaf, borang tidak dapat diproses kerana pengguna tidak log masuk dengan sah.");
    }
    # semak 'kata_laluan' dengan 'kata_laluan2' sama atau tidak
    if ($_SESSION['verifiedOK'] == 1) {
      $_SESSION['newpasswordcompareOK'] = 0; // beri nilai awal
      fnCompareNewPasswords();
      if ($_SESSION['newpasswordcompareOK'] == 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Kata laluan yang dimasukkan tidak sama.");
      }
    }
    # semak pilihan kementerian
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_kem'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Kementerian bagi pengguna baharu ini.");
      }
    }
    # semak pilihan kementerian
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_jab'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Jabatan/Agensi bagi pengguna baharu ini.");
      }
    }
    # baru boleh simpan
    if ($_SESSION['verifiedOK'] == 1) {
      fnUpdateUser($DBServer,$DBUser,$DBPass,$DBName);
      fnRunAlert("Rekod pengguna telah dikemaskini.");
      # kosongkan sessions
      fnClearSessionListUser();
    }
  }
}
?>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Pengurusan Pengguna</h3>
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
    <?php  
    # When a user clicks the 'add new user' button (if any)
    if ($_POST['btn_papar_borang_tambah_pengguna']) {
      ?>
      <div class="clearfix"></div>
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Senarai Pengguna <small>Senarai dan Borang Kemaskini Pengguna</small></h2>
              <ul class="nav navbar-right panel_toolbox">
                <!-- <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a> -->
                <!-- </li> -->
                <!-- <li class="dropdown"> -->
                <!-- <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a> -->
                  <!-- <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Settings 1</a>
                    </li>
                    <li><a href="#">Settings 2</a>
                    </li>
                  </ul> -->
                  <!-- </li> -->
                  <!-- <li><a class="close-link"><i class="fa fa-close"></i></a> -->
                  <!-- </li> -->
                </ul>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">
                <br />
                <form id="form-dok-baharu" data-parsley-validate class="form-horizontal form-label-left">

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_penuh">Nama Penuh <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" value="<?php echo $_SESSION['nama_penuh']; ?>" id="nama_penuh" name="nama_penuh" required="required" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="gelaran">Gelaran <span class="required">*</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <select class="form-control" id="gelaran" name="gelaran" required="required">
                        <option>Sila pilih...</option>
                        <option>Pilihan...</option>
                        <option>Pilihan...</option>
                        <option>Pilihan...</option>
                        <option>Pilihan...</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_pengguna">Nama Pengguna <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" id="nama_pengguna" name="nama_pengguna" required="required" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_laluan">Kata Laluan <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="password" id="kata_laluan" name="kata_laluan" required="required" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_laluan2">Ulang Kata Laluan <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="password" id="kata_laluan2" name="kata_laluan2" required="required" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="emel">Emel <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="email" id="emel" name="emel" required="required" class="form-control col-md-7 col-xs-12">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="emel">Status <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <div id="status" class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                          <input type="radio" name="status" value="1"> &nbsp; Aktif &nbsp;
                        </label>
                        <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                          <input type="radio" name="status" value="0"> Tidak Aktif
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="ln_solid"></div>
                  <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                      <button type="submit" class="btn btn-success">Simpan</button>
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
    # When a user clicks the 'cancel' button in the edit user form
    if ($_POST['btn_batal_kemaskini']) {
      $_SESSION['updateUserOK'] = 0;
      $_SESSION['status_buka_borang_kemaskini_pengguna'] = 0;
      fnClearSessionListUser();
    }
    # When a user clicks the 'open edit user form'
    if ($_POST['btn_papar_borang_kemaskini_pengguna']) {
      $_SESSION['updateUserOK'] = 0;
      $_SESSION['status_buka_borang_kemaskini_pengguna'] = 1;
      $_SESSION['id_pengguna_utk_dikemaskini'] = $_POST['btn_papar_borang_kemaskini_pengguna'];
      fnGetUserRecForUpdate($DBServer,$DBUser,$DBPass,$DBName,$_SESSION['id_pengguna_utk_dikemaskini']);
    }
    if ($_SESSION['updateUserOK'] == 1) {
      $_SESSION['status_buka_borang_kemaskini_pengguna'] = 0;
    }
    if ($_SESSION['status_buka_borang_kemaskini_pengguna'] == 1) {
      ?>
      <div class="clearfix"></div>
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Kemaskini Pengguna <small>Borang Kemaskini Pengguna Sedia Ada</small></h2>
              <ul class="nav navbar-right panel_toolbox">
                <!-- <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a> -->
                <!-- </li> -->
                <!-- <li class="dropdown"> -->
                <!-- <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a> -->
                  <!-- <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Settings 1</a>
                    </li>
                    <li><a href="#">Settings 2</a>
                    </li>
                  </ul> -->
                  <!-- </li> -->
                  <!-- <li><a class="close-link"><i class="fa fa-close"></i></a> -->
                  <!-- </li> -->
                </ul>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">
                <br />
                <form id="form-kemaskini-pengguna" action="listuser.php" method="POST" data-parsley-validate class="form-horizontal form-label-left">

                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_penuh">Nama Penuh <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input value="<?php echo $_SESSION['nama_penuh']; ?>" type="text" id="nama_penuh" name="nama_penuh" autofocus class="form-control col-md-7 col-xs-12" maxlength="150" required>
                    </div>
                  </div>
                  <?php fnDropdownGelaranNama($DBServer,$DBUser,$DBPass,$DBName); ?>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_pengguna">Nama Pengguna <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input readonly value="<?php echo $_SESSION['nama_pengguna']; ?>" type="text" id="nama_pengguna" name="nama_pengguna" maxlength="25" class="form-control col-md-7 col-xs-12" maxlength="25" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_laluan">Kata Laluan <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="password" value="<?php echo $_SESSION['kata_laluan']; ?>" id="kata_laluan" name="kata_laluan" maxlength="64" class="form-control col-md-7 col-xs-12" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_laluan2">Ulang Kata Laluan <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="password" value="<?php echo $_SESSION['kata_laluan']; ?>" id="kata_laluan2" name="kata_laluan2" maxlength="64" class="form-control col-md-7 col-xs-12" maxlength="16" required>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="emel">Emel <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <input value="<?php echo $_SESSION['emel']; ?>" type="email" id="emel" name="emel" maxlength="50" class="form-control col-md-7 col-xs-12" maxlength="50" required>
                    </div>
                  </div>
                  <?php fnDropdownKem($DBServer,$DBUser,$DBPass,$DBName); ?>
                  <?php fnDropdownJab($DBServer,$DBUser,$DBPass,$DBName,"kod_jab"); ?>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_pentadbir">Pilihan Pentadbir <span class="required">*</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <div class="checkbox">
                        <label>
                          <?php  
                          if ($_SESSION['pentadbir_sistem']=='1') {
                            $checkedstatus01="checked";
                          }
                          else {
                            $checkedstatus01="";
                          }
                          ?>
                          <input <?php echo $checkedstatus01; ?> type="checkbox" id="pentadbir_sistem" name="pentadbir_sistem" value="1" class="flat"> Pentadbir Sistem
                        </label>
                      </div>
                      <div class="checkbox">
                        <label>
                          <?php  
                          if ($_SESSION['pentadbir_dokumen']=='2') {
                            $checkedstatus02="checked";
                          }
                          else {
                            $checkedstatus02="";
                          }
                          ?>
                          <input <?php echo $checkedstatus02; ?> type="checkbox" id="pentadbir_dokumen" name="pentadbir_dokumen" value="2" class="flat"> Pentadbir Dokumen
                        </label>
                      </div>
                      <div class="checkbox">
                        <label>
                          <?php  
                          if ($_SESSION['pentadbir_pengguna']=='3') {
                            $checkedstatus03="checked";
                          }
                          else {
                            $checkedstatus03="";
                          }
                          ?>
                          <input <?php echo $checkedstatus03; ?> type="checkbox" id="pentadbir_pengguna" name="pentadbir_pengguna" value="3" class="flat"> Pentadbir Pengguna
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="status_pengguna">Status <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                      <?php  
                      // 2 status; aktif dan pasif
                      if ($_SESSION['status_pengguna']==1) {
                        $statusaktif="checked";
                        $statuspasif="";
                      }
                      else {
                        $statusaktif="";
                        $statuspasif="checked";
                      }
                      ?>
                      <div class="radio">
                        <label>
                          <input <?php echo $statusaktif; ?> type="radio" class="flat" checked id="status_pengguna" name="status_pengguna" value="1"> Aktif
                        </label>
                      </div>
                      <div class="radio">
                        <label>
                          <input <?php echo $statuspasif; ?> type="radio" class="flat" id="status_pengguna" name="status_pengguna" value="0"> Tidak Aktif
                        </label>
                      </div>
                    </div>
                  </div>
                  <div class="ln_solid"></div>
                  <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                      <!-- <button type="submit" class="btn btn-success">Simpan</button> -->
                      <input type="submit" id="btn_kemaskini_rekod_pengguna" name="btn_kemaskini_rekod_pengguna" class="btn btn-success" value="Simpan">
                      <input type="submit" id="btn_batal_kemaskini" name="btn_batal_kemaskini" class="btn btn-danger" title="Batal" value="Batal">
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
                    <th>Nama Penuh</th>
                    <th width="300">Nama Pengguna</th>
                    <th width="115">Peranan</th>
                    <th width="115">Tindakan</th>
                  </tr>
                </thead>


                <tbody>
                  <?php 
                  fnShowUserTableContent($DBServer,$DBUser,$DBPass,$DBName); 
                  ?>
                </tbody>
              </table>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Habis borang dokumen baharu -->
      <!-- /page content -->
      <?php require "../layouts/lay_adminmainbottom.php"; ?>