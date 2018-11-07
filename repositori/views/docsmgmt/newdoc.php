<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
# clear session from other forms newuser, updateuser, updatedoc
if (isset($_GET['s']) == 'n') {
  // form is opened from the sidebar, clear the session
  $_POST['btn_simpan_dok_baru'] = "";
  fnCountTerasStrategik($DBServer,$DBUser,$DBPass,$DBName);
  fnClearSessionNewUser();
  fnClearSessionListUser();
  fnClearSessionNewDoc();
  fnClearSessionListDoc();
}
// when 'btn_simpan_dok_baru' is pressed/clicked
if (isset($_POST['btn_simpan_dok_baru']) AND !isset($_GET['s'])) {
  fnClearSessionNewDoc();
  $_SESSION['tajuk_dok'] = $_POST['tajuk_dokumen'];
  $_SESSION['tajuk_dok'] = checkAndRevalue($_SESSION['tajuk_dok']);
  // fnRunAlert("$_SESSION[tajuk_dok]");
  $_SESSION['bil_dok'] = $_POST['bil_dokumen'];
  // $_SESSION['bil_dok'] = checkAndRevalue($_SESSION['bil_dok']);
  $_SESSION['tahun_dok'] = $_POST['tahun_dokumen'];
  $_SESSION['tahun_dok'] = checkAndRevalue($_SESSION['tahun_dok']);
  $_SESSION['des_dok'] = $_POST['des_dokumen'];
  $_SESSION['des_dok'] = checkAndRevalue($_SESSION['des_dok']);
  $_SESSION['kod_kat'] = $_POST['kod_kat'];
  $_SESSION['kod_kat'] = checkAndRevalue($_SESSION['kod_kat']);
  $_SESSION['kod_sektor'] = $_POST['kod_sektor'];
  $_SESSION['kod_sektor'] = checkAndRevalue($_SESSION['kod_sektor']);
  $_SESSION['kod_bah'] = $_POST['kod_bah'];
  $_SESSION['kod_bah'] = checkAndRevalue($_SESSION['kod_bah']);
  $_SESSION['kod_kem'] = $_POST['kod_kem'];
  $_SESSION['kod_kem'] = checkAndRevalue($_SESSION['kod_kem']);
  $_SESSION['kod_jab'] = $_POST['kod_jab'];
  $_SESSION['kod_jab'] = checkAndRevalue($_SESSION['kod_jab']);
  $_SESSION['kod_status'] = $_POST['kod_status'];
  $_SESSION['kod_status'] = checkAndRevalue($_SESSION['kod_status']);
  $_SESSION['id_pendaftar'] = $_SESSION['loggedinid'];
  $_SESSION['id_pendaftar'] = checkAndRevalue($_SESSION['id_pendaftar']);
  $_SESSION['tarikh_wujud'] = $_POST['tarikh_wujud'];
  $_SESSION['tarikh_wujud'] = checkAndRevalue($_SESSION['tarikh_wujud']);
  $_SESSION['tarikh_wujud'] = date("Y-m-d", strtotime($_SESSION['tarikh_wujud']));
  $_SESSION['tarikh_mansuh'] = $_POST['tarikh_mansuh'];
  $_SESSION['tarikh_mansuh'] = checkAndRevalue($_SESSION['tarikh_mansuh']);
  $_SESSION['tarikh_pinda'] = $_POST['tarikh_pinda'];
  $_SESSION['tarikh_pinda'] = checkAndRevalue($_SESSION['tarikh_pinda']);
  $_SESSION['tajuk_dok_asal'] = $_POST['tajuk_dok_asal'];
  $_SESSION['tajuk_dok_asal'] = checkAndRevalue($_SESSION['tajuk_dok_asal']);
  $_SESSION['tajuk_dok_baharu'] = $_POST['tajuk_dok_baharu'];
  $_SESSION['tajuk_dok_baharu'] = checkAndRevalue($_SESSION['tajuk_dok_baharu']);
  $_SESSION['tarikh_serah'] = $_POST['tarikh_serah'];
  $_SESSION['tarikh_serah'] = checkAndRevalue($_SESSION['tarikh_serah']);
  $_SESSION['kod_jab_asal'] = $_POST['kod_jab_asal'];
  $_SESSION['kod_jab_asal'] = checkAndRevalue($_SESSION['kod_jab_asal']);
  $_SESSION['kod_jab_baharu'] = $_POST['kod_jab_baharu'];
  $_SESSION['kod_jab_baharu'] = checkAndRevalue($_SESSION['kod_jab_baharu']);
  $_SESSION['tarikh_dok'] = date("Y-m-d");
  $_SESSION['tarikh_dok'] = checkAndRevalue($_SESSION['tarikh_dok']);
  $_SESSION['tarikh_kemaskini'] = date("Y-m-d");
  $_SESSION['tarikh_kemaskini'] = checkAndRevalue($_SESSION['tarikh_kemaskini']);
  // $_SESSION['nama_dok'] = $_POST['nama_dok'];
  $_SESSION['tag_dokumen'] = $_POST['tag_dokumen'];
  $_SESSION['tag_dokumen'] = checkAndRevalue($_SESSION['tag_dokumen']);
  $_SESSION['catatan_dokumen'] = $_POST['catatan_dokumen'];
  $_SESSION['catatan_dokumen'] = checkAndRevalue($_SESSION['catatan_dokumen']);
  fnSetTarikhStatusDoc();

  # start verifying form
  $_SESSION['verifiedOK'] = 3; // initial value
  if ($_SESSION['verifiedOK'] != 0) {
    # 1. user logged in?
    # semak jika ada loggedinid; user yang sah telah log masuk
    if ($_SESSION['loggedinid'] != 0) {
      $_SESSION['verifiedOK'] = 1;
    }
    else {
      $_SESSION['verifiedOK'] = 0;
      fnRunAlert("Maaf, borang tidak dapat diproses kerana pengguna tidak log masuk dengan sah.");
    }
    # semak pilihan kategori
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_kat'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Kategori bagi dokumen ini.");
      }
    }
    # kira pilihan teras
    if ($_SESSION['verifiedOK'] == 1) {
      fnCountCheckedTeras($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['checked_teras'] == 0) {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih sekurang-kurangnya satu Teras Strategik.");
      }
      else {
        $_SESSION['verifiedOK'] = 1;
      }
    }
    # semak pilihan kementerian
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_kem'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Kementerian bagi dokumen ini.");
      }
    }
    # semak pilihan jabatan
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_jab'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Jabatan/Agensi bagi dokumen ini.");
      }
    }
    # semak pilihan sektor
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_sektor'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Sektor bagi dokumen ini.");
      }
    }
    # semak pilihan bahagian
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_bah'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Bahagian bagi dokumen ini.");
      }
    }
    # semak pilihan status
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_status'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Status bagi dokumen ini.");
      }
    }
    # semak jika ada duplikasi dokumen
    if ($_SESSION['verifiedOK'] == 1) {
      fnCheckSavedDoc($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['duplicatedoc'] == 0) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
      }
    }
    # muatnaik dokumen
    /*
    maklumat fail tidak akan disimpan di dalam table dokumen lagi tapi disimpan dalam table dok_sokongan
     */
    if ($_SESSION['verifiedOK'] == 1) {
      # semak jika fail yang hendak dimuat naik telah dipilih
      # Kira bilangan fail yang hendak dimuatnaik dan pastikan minimum 1 fail.
      fnPreUploadFilesRename();
    }
    # if file is uploaded, save record

    if (isset($_SESSION['touploadOK']) AND $_SESSION['touploadOK'] == 1 AND $_SESSION['verifiedOK'] == 1) {
      # baru boleh upload file
      $_SESSION['uploadOk'] = 0; // Assign initial value to 'uploadOk'
      if ($_SESSION['slot01_OK'] == 1) {
        fnUploadFilesRename("nama_dok"); // the altered original version (the working one! 20161018)
        // fnRunAlert("Dah upload slot01");
      }
      if ($_SESSION['slot02_OK'] == 1) {
        fnUploadFilesRename("nama_dok_01");
        // fnRunAlert("Dah upload slot02");
      }
      if ($_SESSION['slot03_OK'] == 1) {
        fnUploadFilesRename("nama_dok_02");
        // fnRunAlert("Dah upload slot03");
      }
      if ($_SESSION['slot04_OK'] == 1) {
        fnUploadFilesRename("nama_dok_03");
        // fnRunAlert("Dah upload slot04");
      }
      fnInsertCheckedTeras($DBServer,$DBUser,$DBPass,$DBName);
      fnInsertNewDoc($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['insertOK'] == 1) {
        fnRunAlert("Rekod BERJAYA disimpan.");
        // fnClearNewDocForm();
        fnClearSessionNewDoc();
      }
      else {
        fnRunAlert("Rekod GAGAL disimpan.");
      }
      # kosongkan sessions
    }
    # jika fail tidak dapat dimuatnaik, input akan dipaparkan semula
    elseif (!isset($_SESSION['touploadOK']) OR $_SESSION['touploadOK'] == 0 OR $_SESSION['verifiedOK'] == 0) {
      fnRunAlert("Rekod TIDAK disimpan.");
    }
  }
}

?>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Pengurusan Dokumen</h3>
      </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Pendaftaran Dokumen <small>Borang Pendaftaran Rekod Dokumen</small></h2>
            <ul class="nav navbar-right panel_toolbox">
              </ul>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <br />
              <form id="form-dok-baharu" data-parsley-validate class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data" action="newdoc.php">
                <?php  
                fnDropdownKategori($DBServer,$DBUser,$DBPass,$DBName); 
                ?>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen<!-- <span class="required">*</span> -->
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input value="<?php echo $_SESSION['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tahun_dokumen">Tahun Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input value="<?php echo $_SESSION['tahun_dok']; ?>" type="text" id="tahun_dokumen" name="tahun_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="4" pattern="\d{1,4}">
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tajuk_dokumen">Tajuk Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input value="<?php echo $_SESSION['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="300"/>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="des_dokumen">Deskripsi Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea rows="4" id="des_dokumen" name="des_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $_SESSION['des_dok']; ?></textarea>
                  </div>
                </div>
                <?php  
                fnCheckboxTeras($DBServer,$DBUser,$DBPass,$DBName); 
                // fnDropdownList($DBServer,$DBUser,$DBPass,$DBName,"Sektor","kod_sektor","kod_sektor","nama_sektor","sektor"); // label,input name,field1,field2,table name
                ?>
                <?php 
                fnDropdownKem($DBServer,$DBUser,$DBPass,$DBName);
                fnDropdownJab($DBServer,$DBUser,$DBPass,$DBName,'kod_jab');
                fnDropdownSektor($DBServer,$DBUser,$DBPass,$DBName); 
                fnDropdownBahagian($DBServer,$DBUser,$DBPass,$DBName); 
                fnDropdownStatusDok($DBServer,$DBUser,$DBPass,$DBName);
                ?>
                <p class="stattext" hidden></p>
                <!-- mansuh -->
                <div class="form-group" id="divmansuh" hidden>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                </div>
                <!-- serah -->
                <div class="form-group" id="divserah" hidden>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <?php  
                  fnDropdownJabStatSerah($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_asal','Asal');
                  fnDropdownJabStatSerah($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_baharu','Baharu');
                  ?>
                </div>
                <!-- pinda -->
                <div class="form-group" id="divpinda" hidden>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                    <br><p style="font-size: 5px;">&nbsp;</p>
                    <input type="file" id="nama_dok_01" name="nama_dok_01" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                    <br><p style="font-size: 5px;">&nbsp;</p>
                    <input type="file" id="nama_dok_02" name="nama_dok_02" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                    <br><p style="font-size: 5px;">&nbsp;</p>
                    <input type="file" id="nama_dok_03" name="nama_dok_03" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                    <br><p style="font-size: 1px;">&nbsp;</p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-3" for="tarikh_wujud">Tarikh Kuat Kuasa Dokumen <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input value="<?php echo $_SESSION['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tag_dokumen"><i>Tag</i> Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea rows="4" id="tag_dokumen" name="tag_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $_SESSION['tag_dokumen']; ?></textarea>
                    <small>masukkan <i>tag</i> dipisahkan dengan tanda koma</small>
                  </div>
                </div>
                <!-- medan catatan: ditambah pada 20170321 oleh SFAA -->
                <div class="form-group"> 
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="catatan_dokumen">Catatan Dokumen
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea rows="4" id="catatan_dokumen" name="catatan_dokumen" class="form-control col-md-7 col-xs-12"><?php echo $_SESSION['catatan_dokumen']; ?></textarea>
                    <small>Sila masukkan catatan, jika ada.</small>
                  </div>
                </div>
                <!-- tamat medan catatan -->
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <input type="submit" class="btn btn-success" id="btn_simpan_dok_baru" name="btn_simpan_dok_baru" title="Simpan Rekod" value="Simpan">
                    <button type="reset" class="btn btn-danger" title="Kosongkan Borang">Batal</button>
                  </div>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
      <br>&nbsp;<br>&nbsp;<br>&nbsp;
      <!-- Habis borang dokumen baharu -->
      <!-- /page content -->
    <!-- MULA Pilihan Status Dokumen -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <script>
      $(document).ready(function(){
        $('#kod_status').on('change', function () {
            switch ($(this).val()) {
                case '1':
                    $('#divmansuh').prop('hidden', true);
                    $('#divserah').prop('hidden', true);
                    $('#divpinda').prop('hidden', true);
                    $('.selectpicker').selectpicker('refresh');
                    break;
                case '2':
                    $('#divmansuh').prop('hidden', true);
                    $('#divserah').prop('hidden', true);
                    $('#divpinda').prop('hidden', true);
                    $('.selectpicker').selectpicker('refresh');
                    break;
                case '3':
                    $('#divmansuh').prop('hidden', false);
                    $('#divserah').prop('hidden', true);
                    $('#divpinda').prop('hidden', true);
                    $('.selectpicker').selectpicker('refresh');
                    break;
                case '4':
                    $('#divmansuh').prop('hidden', true);
                    $('#divserah').prop('hidden', false);
                    $('#divpinda').prop('hidden', true);
                    $('.selectpicker').selectpicker('refresh');
                    break;
                case '5':
                    $('#divmansuh').prop('hidden', true);
                    $('#divserah').prop('hidden', true);
                    $('#divpinda').prop('hidden', false);
                    break;
            }
        }); 
      });


    </script>
    <!-- TAMAT Pilihan Status Dokumen -->



      <?php require "../layouts/lay_adminmainbottom.php"; ?>