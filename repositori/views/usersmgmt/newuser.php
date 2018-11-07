<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
// clear session from other forms newuser, updateuser, updatedoc
if (isset($_GET['s']) AND $_GET['s'] == 'n') {
  // form is opened from the sidebar, clear the session
  fnClearSessionNewUser();
  fnClearSessionNewDoc();
}
?>
<script>
  // alert("mula");
</script>
<?php  
// this is where the form's input is gathered
if(isset($_POST['btn_simpan_pengguna_baharu'])){
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
  if (isset($_POST['garam'])) {
    $_SESSION['garam']=$_POST['garam'];
  }
  else {
    $_SESSION['garam']="";
  }
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
  $_SESSION['status_pengguna'] = checkAndRevalue($_SESSION['status_pengguna']);
  /* id_penambah (bagi pengguna baharu sahaja) */
  $_SESSION['id_pendaftar']=$_SESSION['loggedinid'];
  $_SESSION['id_pendaftar'] = checkAndRevalue($_SESSION['id_pendaftar']);
  /* tarikh pengguna didaftarkan */
  $_SESSION['tarikh_daftar'] = date("Y-m-d H:i:s");
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
      fnInsertNewUser($DBServer,$DBUser,$DBPass,$DBName);
      fnRunAlert("Rekod telah disimpan.");
      # kosongkan sessions
      fnClearSessionNewUser();
    }
  }
}
?>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Pengurusan Pentadbir</h3>
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
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Pendaftaran Pentadbir <small>Borang Pendaftaran Rekod Pentadbir</small></h2>
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
              <form id="form-pengguna-baharu" action="newuser.php" method="POST" data-parsley-validate class="form-horizontal form-label-left">

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
                    <input value="<?php echo $_SESSION['nama_pengguna']; ?>" type="text" id="nama_pengguna" name="nama_pengguna" maxlength="25" class="form-control col-md-7 col-xs-12" maxlength="25" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_laluan">Kata Laluan <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="password" id="kata_laluan" name="kata_laluan" maxlength="64" class="form-control col-md-7 col-xs-12" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_laluan2">Ulang Kata Laluan <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="password" id="kata_laluan2" name="kata_laluan2" maxlength="64" class="form-control col-md-7 col-xs-12" maxlength="16" required>
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
                        if ($_SESSION['pentadbir_dokumen']=='1') {
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
                        if ($_SESSION['pentadbir_pengguna']=='1') {
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
                    <input type="submit" id="btn_simpan_pengguna_baharu" name="btn_simpan_pengguna_baharu" class="btn btn-success" value="Simpan">
                    <button type="reset" class="btn btn-danger">Batal</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div>
            &nbsp;<br/>
            &nbsp;<br/>
            &nbsp;<br/>
            &nbsp;<br/>
          </div>
        </div>
      </div>
      <!-- Habis borang dokumen baharu -->
      <!-- /page content -->
      <?php require "../layouts/lay_adminmainbottom.php"; ?>