<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  

/*
n = new
s1 = step 1
s2 = step 2
*/
// ("source = ".$_GET['s']);
if ($_GET['s'] != 's1' AND $_GET['s'] != 's2') {
  fnResetSessionsForPages();
}

if ($_SESSION['pageSource'] == 'new') {
  $_GET['s'] = 'n';
  $_SESSION['kod_kat_step2'] = '';
}

// fnRunAlert("source = ".$_GET['s']);
// fnRunAlert("pageSource = ".$_SESSION['pageSource']);

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

function fnGetDocCodeUsingAvailableInfo(){
  $DBServer       = $_SESSION['DBServer'];
  $DBUser         = $_SESSION['DBUser'];
  $DBPass         = $_SESSION['DBPass'];
  $DBName         = $_SESSION['DBName'];
  $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

  // check connection
  if ($conn->connect_error) {
      trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
  }

  $sql="SELECT kod_dok FROM dokumen WHERE kod_kat = ".$_SESSION['kod_kat_step2']." AND bil_dok = ".$_SESSION['bil_dok_step2']." AND tahun_dok = ".$_SESSION['tahun_dok_step2'];

  $rs=$conn->query($sql);

  if($rs === false) {
      trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
  } else {
      $arr = $rs->fetch_all(MYSQLI_ASSOC);
  }
  foreach($arr as $row) {
    $_SESSION['kod_dok_step2'] = $row['kod_dok'];
    // fnRunAlert("kod_dok = ".$_SESSION['kod_dok_step2']);
  }
}


// when 'btn_simpan_dok_baru' is pressed/clicked (the form)
if (isset($_POST['btn_simpan_dok_baru']) AND $_GET['s']=='s1') {
  fnClearSessionNewDoc();
  $_SESSION['tajuk_dok'] = $_POST['tajuk_dokumen'];
  $_SESSION['tajuk_dok'] = checkAndRevalue($_SESSION['tajuk_dok']);
  // fnRunAlert("$_SESSION[tajuk_dok]");
  $_SESSION['bil_dok'] = $_POST['bil_dokumen'];
  $_SESSION['bil_dok_step2'] = $_POST['bil_dokumen'];
  // $_SESSION['bil_dok'] = checkAndRevalue($_SESSION['bil_dok']);
  $_SESSION['tahun_dok'] = $_POST['tahun_dokumen'];
  $_SESSION['tahun_dok_step2'] = $_POST['tahun_dokumen'];
  $_SESSION['tahun_dok'] = checkAndRevalue($_SESSION['tahun_dok']);
  $_SESSION['des_dok'] = $_POST['des_dokumen'];
  $_SESSION['des_dok'] = checkAndRevalue($_SESSION['des_dok']);
  $_SESSION['kod_kat'] = $_POST['kod_kat'];
  $_SESSION['kod_kat'] = checkAndRevalue($_SESSION['kod_kat']);
  $_SESSION['kod_kat_step2'] = checkAndRevalue($_SESSION['kod_kat']);
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
      // fnPreUploadFilesRename(); # yang ni skip dulu... terus bagi $_SESSION['touploadOK'] = 1
      $_SESSION['slot01_OK'] = 0;
      $_SESSION['slot02_OK'] = 0;
      $_SESSION['slot03_OK'] = 0;
      $_SESSION['slot04_OK'] = 0;
      $_SESSION['touploadOK'] = 1;
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
      fnInsertNewDoc_v2($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['insertOK'] == 1) {
        fnRunAlert("Rekod BERJAYA disimpan.");
        $_SESSION['langkah'] = 2;
        fnGetDocCodeUsingAvailableInfo();
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

// when 'btn_simpan_dok_sokongan_baharu' is pressed/clicked
if ((isset($_POST['btn_simpan_dok_sokongan_baharu']) OR isset($_POST['btn_simpan_dok_sokongan_baharu_akhir'])) AND $_GET['s']=='s2') {
  fnClearSessionNewDoc();
  # dapatkan kategori dok yang telah disimpan
  $_SESSION['kod_kat_step2'];
  # dapatkan bil_dok yang telah disimpan
  $_SESSION['bil_dok_step2'];
  # dapatkan tahun dok yang telah disimpan
  $_SESSION['tahun_dok_step2'];
  # dapatkan  kod_dok menggunakan maklumat 3 medan di atas

  fnGetDocCodeUsingAvailableInfo();

  $_SESSION['tarikh_kemaskini'] = date("Y-m-d");
  $_SESSION['tarikh_kemaskini'] = checkAndRevalue($_SESSION['tarikh_kemaskini']);
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
      fnPreUploadFilesRename_v2(); # yang ni skip dulu... terus bagi $_SESSION['touploadOK'] = 1
      // $_SESSION['slot01_OK'] = 0;
      $_SESSION['slot02_OK'] = 0;
      $_SESSION['slot03_OK'] = 0;
      $_SESSION['slot04_OK'] = 0;
      // $_SESSION['touploadOK'] = 1;
    }
    # if file is uploaded, save record

    if (isset($_SESSION['touploadOK']) AND $_SESSION['touploadOK'] == 1 AND $_SESSION['verifiedOK'] == 1) {
      # baru boleh upload file
      $_SESSION['uploadOk'] = 0; // Assign initial value to 'uploadOk'
      if ($_SESSION['slot01_OK'] == 1) {
        fnUploadFilesRename_v2("nama_dok"); // the altered original version (the working one! 20161018)
        // fnRunAlert("Dah upload slot01");
      }
      if ($_SESSION['slot02_OK'] == 1) {
        fnUploadFilesRename_v2("nama_dok_01");
        // fnRunAlert("Dah upload slot02");
      }
      if ($_SESSION['slot03_OK'] == 1) {
        fnUploadFilesRename_v2("nama_dok_02");
        // fnRunAlert("Dah upload slot03");
      }
      if ($_SESSION['slot04_OK'] == 1) {
        fnUploadFilesRename_v2("nama_dok_03");
        // fnRunAlert("Dah upload slot04");
      }
      // fnInsertCheckedTeras($DBServer,$DBUser,$DBPass,$DBName);
      fnInsertNewSupportDoc_v2($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['slot01_OK'] == 1) {
        fnRunAlert("Rekod BERJAYA dikemaskini.");
        if (isset($_POST['btn_simpan_dok_sokongan_baharu_akhir'])) {
          $_SESSION['langkah'] = 1;
        } else {
          $_SESSION['langkah'] = 2;
        }
        fnClearSessionNewDoc();
      }
      else {
        fnRunAlert("Rekod TIDAK dikemaskini.");
      }
      # kosongkan sessions
    }
    # jika fail tidak dapat dimuatnaik, input akan dipaparkan semula
    elseif (!isset($_SESSION['touploadOK']) OR $_SESSION['touploadOK'] == 0 OR $_SESSION['verifiedOK'] == 0) {
      fnRunAlert("Rekod TIDAK dikemaskini.");
    }
  }
}

if (isset($_POST['btn_selesai_muatnaik'])) {
  $_SESSION['langkah'] = 1;
}

// $_SESSION['langkah'] = 1;
?>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Pengurusan Dokumen</h3>
      </div>
    </div>
    <!-- BORANG MUAT NAIK DOKUMEN -->
    <?php  
    if ($_SESSION['langkah']==2) {
      $_SESSION['pageSource'] = '';
      ?>
      <div class="clearfix"></div>
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Muat Naik Dokumen <small>Borang Pendaftaran Rekod Dokumen</small></h2>
              <ul class="nav navbar-right panel_toolbox">
              </ul>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <br />
              <form id="form-dok-sokongan-baharu" data-parsley-validate class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data" action="newdoc_v2.php?s=s2">
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen Berkaitan<span class="required">*</span>
                  </label>
                  <?php  
                  $arr = array(1, 2, 3, 4);

                  foreach($arr as $bil_upload) {
                    // $_SESSION['kod_dok_step2'] = $bil_upload['kod_dok'];
                    // fnRunAlert("kod_dok = ".$_SESSION['kod_dok_step2']);
                    ?>
                    <?php
                  }

                  ?>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="control-group" id="fields">
                      <div class="controls">
                        <div class="entry input-group col-xs-3">
                          <input class="btn btn-default" id="nama_dok" name="nama_dok" title="Sila pilih dokumen untuk dimuat naik" type="file" accept="application/*, image/*">
                          <span class="input-group-btn">
                            <button hidden class="btn btn-success btn-add" type="button">
                              <span class="glyphicon glyphicon-plus"></span>
                            </button>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-2">
                    <input type="submit" class="btn btn-success" id="btn_simpan_dok_sokongan_baharu" name="btn_simpan_dok_sokongan_baharu" title="Muat Naik & Muat Naik Lagi" value="Muat Naik & Teruskan">
                    <input type="submit" class="btn btn-warning" id="btn_simpan_dok_sokongan_baharu" name="btn_simpan_dok_sokongan_baharu_akhir" title="Muat Naik & Selesai" value="Muat Naik & Selesai">
                    <input type="submit" class="btn btn-danger" id="btn_simpan_dok_sokongan_baharu" name="btn_selesai_muatnaik" title="Selesai Muat Naik & Kembali ke Borang Daftar Dokumen Baharu" value="Keluar">
                    <button type="reset" class="btn btn-secondary" title="Kosongkan Borang">Kosongkan</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <!-- Jadual untuk senaraikan dokumen sokongan yang telah dimuat naik -->
      <div class="clearfix"></div>
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2>Senarai Dokumen yang Telah Dimuatnaik <small>Untuk Rekod Ini Sahaja</small></h2>
              <ul class="nav navbar-right panel_toolbox">
              </ul>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <br />
              <table class="table table-striped">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nama Fail Asal</th>
                    <th scope="col">Nama Fail Disimpan</th>
                    <th scope="col">Tindakan</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Jika tiada untuk disenaraikan -->
                  <?php  
                  function fnListSupportingDocsForThisRecord(){
                      $DBServer       = $_SESSION['DBServer'];
                      $DBUser         = $_SESSION['DBUser'];
                      $DBPass         = $_SESSION['DBPass'];
                      $DBName         = $_SESSION['DBName'];

                      $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

                      // check connection
                      if ($conn->connect_error) {
                          trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
                      }

                      $sql='SELECT * FROM dok_sokongan WHERE kod_dok_fk='.$_SESSION['kod_dok_step2'];

                      $rs=$conn->query($sql);

                      if($rs === false) {
                          trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                      } else {
                          $rows_returned = $rs->num_rows;
                      }

                      if ($rows_returned > 0) {
                        # code...
                        $rs=$conn->query($sql);

                        if($rs === false) {
                            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr = $rs->fetch_all(MYSQLI_ASSOC);
                        }

                        $table_number = 1;
                        foreach($arr as $row) {
                          $target_dir = "../papers/";
                          $target_file = "$target_dir" . $row['nama_dok_disimpan'];
                          if (file_exists($target_file)) {
                            // fnRunAlert("File ni ada dlm folder.".$target_file);
                            ?>
                            <tr class="table table-striped">
                              <th scope="row"><?= $table_number; ?></th>
                              <td><?= $row['nama_dok_asal'] ?></td>
                              <td><?= $row['nama_dok_disimpan'] ?></td>
                              <td>
                                <button class="btn btn-danger"><span class="fa fa-trash"></span></button>
                              </td>
                            </tr>
                            <?php
                            $table_number++;  
                          }
                        }
                      }
                      else {
                        ?>
                        <tr class="table table-striped" align="right">
                          <th scope="row" colspan="4">Tiada dokumen sokongan telah dimuatnaik.</th>
                        </tr>
                        <?php
                      }
                      // echo $rows_returned; // uncomment for debugging only
                      // echo $arr['kod_dok']; // uncomment for debugging only


                      $conn->close();
                  }
                  fnListSupportingDocsForThisRecord();
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php
    }
    ?>
    <!-- /BORANG MUAT NAIK DOKUMEN -->
    <?php
    if ($_SESSION['langkah']==1) {
      $_SESSION['pageSource'] = '';
      ?>
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
              <form id="form-dok-baharu" data-parsley-validate class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data" action="newdoc_v2.php?s=s1">
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
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">
                    Tarikh Pinda <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">
                    Tajuk Asal <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">
                    Tajuk Baharu <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $_SESSION['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                  </div>
                </div>

                <div hidden class="form-group">
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
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tag_dokumen">
                    <i>Tag</i> Dokumen <span class="required">*</span>
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
                    <input type="submit" class="btn btn-success" id="btn_simpan_dok_baru" name="btn_simpan_dok_baru" title="Simpan Rekod" value="Teruskan">
                    <button type="reset" class="btn btn-danger" title="Kosongkan Borang">Batal</button>
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