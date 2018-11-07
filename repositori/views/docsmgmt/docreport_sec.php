<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
/*
errors
Notice: Undefined index: s in /Users/Salbiyah/Sites/srp1_0d/views/docsmgmt/listdoc.php on line 5

*/
# clear session from other forms newuser, updateuser, updatedoc
if (isset($_GET['s']) == 'n') {
  // form is opened from the sidebar, clear the session
  $_POST['btn_tutup_perincian_dokumen'] = "";
  $_POST['btn_papar_borang_kemaskini'] = "";
  $_POST['btn_papar_perincian_dokumen'] = "";
  $_SESSION['updateDocOK'] = "";
  $_SESSION['doclist_search_keyword'] = "";
  fnClearSessionNewUser();
  fnClearSessionListUser();
  fnClearSessionNewDoc();
  fnClearSessionListDoc();
  fnClearSessionForDoclistSearch();
  fnClearSessionForListFromReport();
}
fnClearSessionForListPages();
$_SESSION['page_title'] = "Laporan Dokumen";
$_SESSION['addnew_form_title'] = "Borang Tambah Rekod Dokumen";
$_SESSION['addnew_form_action'] = "Simpan Dokumen Baharu";
$_SESSION['preview_doc_title'] = "Paparan Rekod Dokumen";
$_SESSION['preview_doc_action'] = "Perincian Dokumen Sedia Ada";
$_SESSION['update_form_title'] = "Borang Kemaskini Rekod Dokumen";
$_SESSION['update_form_action'] = "Kemaskini Dokumen Sedia Ada";
$_SESSION['table_title'] = "Statistik mengikut Sektor";
$_SESSION['table_action'] = "Senaraikan Dokumen";
$actionfilename = "docreport_sec.php";
$table01name = "sektor";
$field01name = "kod_sektor";
$field02name = "nama_sektor";

# when user clicked 'Cari!'
if (isset($_POST['btn_search_doclist'])) {
  $_SESSION['doclist_search_keyword'] = $_POST['txt_search_doclist'];
  $_SESSION['updateDocOK'] = "";
  $_SESSION['updateDocOK'] = 0;
  $_POST['btn_papar_perincian_dokumen'] = "";
  $_SESSION['status_papar_perincian_dokumen'] = 0;
  $_POST['btn_tutup_perincian_dokumen'] = "";
  $_POST['btn_papar_borang_kemaskini'] = "";
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = "";
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = 0;
}
# When a user clicks the 'open edit doc form'
if (isset($_POST['btn_papar_borang_kemaskini'])) {
  $_SESSION['updateDocOK'] = 0;
  $_SESSION['status_papar_perincian_dokumen'] = 0;
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = 1;
  $_SESSION['kod_dok_untuk_dikemaskini'] = $_POST['btn_papar_borang_kemaskini'];
}
if (!isset($_SESSION['btn_papar_borang_kemaskini_dari_perincian_dokumen'])) {
  $_SESSION['updateDocOK'] = 0;
  $_SESSION['status_papar_perincian_dokumen'] = 0;
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = 1;
  // fnRunAlert("opened from view");
  // fnRunAlert("$_SESSION[kod_dok_untuk_dikemaskini]");
  // if (isset($_SESSION['kod_dok_untuk_dipapar'])) {
    // $_SESSION['kod_dok_untuk_dikemaskini'] = $_SESSION['kod_dok_untuk_dipapar'];
  // }
  // else {
    // fnRunAlert("Tiada kod dokumen!");
  // }
}
# When a user clicks the 'show doc detail'
if (isset($_POST['btn_papar_perincian_dokumen'])) {
  $_SESSION['updateDocOK'] = 0;
  $_SESSION['status_papar_perincian_dokumen'] = "";
  $_SESSION['status_papar_perincian_dokumen'] = 1;
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = "";
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = 0;
  $_SESSION['kod_dok_untuk_dikemaskini'] = $_POST['btn_papar_perincian_dokumen'];
  $_SESSION['kod_dok_untuk_dipapar'] = $_POST['btn_papar_perincian_dokumen'];
}
if (isset($_POST['btn_tutup_perincian_dokumen'])) {
  $_SESSION['status_papar_perincian_dokumen'] = 0;
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = 0;
  fnClearSessionListDoc();
}
if ($_SESSION['updateDocOK'] == 1) {
  $_SESSION['status_papar_perincian_dokumen'] = 0;
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = 0;
  fnClearSessionListDoc();
}

# when user clicked 'simpan'
if (isset($_POST['btn_simpan_dok_dikemaskini'])) {
  # use $_SESSION['kod_dok_untuk_dikemaskini'] for this doc's id
  $_SESSION['tajuk_dok'] = $_POST['tajuk_dokumen'];
  $_SESSION['tajuk_dok'] = checkAndRevalue($_SESSION['tajuk_dok']);
  $_SESSION['bil_dok'] = $_POST['bil_dokumen'];
  $_SESSION['bil_dok'] = checkAndRevalue($_SESSION['bil_dok']);
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
  $_SESSION['id_pengemaskini'] = $_SESSION['loggedinid'];
  $_SESSION['id_pengemaskini'] = checkAndRevalue($_SESSION['id_pengemaskini']);
  $_SESSION['tarikh_wujud'] = $_POST['tarikh_wujud'];
  $_SESSION['tarikh_wujud'] = checkAndRevalue($_SESSION['tarikh_wujud']);
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
  $_SESSION['tarikh_dok'] = date("Y-m-d H:i:s");
  $_SESSION['tarikh_dok'] = checkAndRevalue($_SESSION['tarikh_dok']);
  $_SESSION['tarikh_kemaskini'] = date("Y-m-d H:i:s");
  $_SESSION['tarikh_kemaskini'] = checkAndRevalue($_SESSION['tarikh_kemaskini']);
  $_SESSION['nama_dok'] = $_POST['nama_dok'];
  $_SESSION['tag_dokumen'] = $_POST['tag_dokumen'];
  $_SESSION['tag_dokumen'] = checkAndRevalue($_SESSION['tag_dokumen']);
  ?>
  <script>
    // alert("<?php echo 
    $_SESSION['tajuk_dok'].'\n'.
    $_SESSION['bil_dok'].'\n'.
    $_SESSION['tahun_dok'].'\n'.
    $_SESSION['des_dok'].'\n'.
    $_SESSION['kod_kat'].'\n'.
    $_SESSION['kod_sektor'].'\n'.
    $_SESSION['kod_teras'].'\n'.
    $_SESSION['kod_kem'].'\n'.
    $_SESSION['kod_jab'].'\n'.
    $_SESSION['kod_status'].'\n'.
    $_SESSION['id_pendaftar'].'\n'.
    $_SESSION['tarikh_wujud'].'\n'.
    $_SESSION['tarikh_dok'].'\n'.
    $_SESSION['nama_fail_asal'].'\n'.
    $_SESSION['nama_fail_baru'].'\n'.
    $_SESSION['tarikh_kemaskini'].'\n'.
    date("Y-m-d H:i:s e"); ?>");
  </script>
  <?php
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
      fnCheckSavedDocToUpdate($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['duplicatedoc'] == 0) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
      }
    }
    # semak pilihan kategori
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_kat'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Kategori bagi dokumen baharu ini.");
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
        fnRunAlert("Sila pilih Kementerian bagi dokumen baharu ini.");
      }
    }
    # semak pilihan jabatan
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_jab'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Jabatan/Agensi bagi dokumen baharu ini.");
      }
    }
    # semak pilihan sektor
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_sektor'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Sektor bagi dokumen baharu ini.");
      }
    }
    # semak pilihan bahagian
    if ($_SESSION['verifiedOK'] == 1) {
      if ($_SESSION['kod_bah'] != 1) {
        $_SESSION['verifiedOK'] = 1;
      }
      else {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Bahagian bagi dokumen baharu ini.");
      }
    }
    # semak pilihan status
    if ($_SESSION['verifiedOK'] == 1) {
      # 1: Sila pilih...
      # 2: Masih berkuatkuasa
      # 3: Dimansuhkan - semak 'tarikh_mansuh'
      # 4: Diserahkan kepada - semak 'tarikh_serah', 'kod_jab_asal', 'kod_jab_baharu'
      # 5: Pindaan kepada - semak 'tajuk_dok_asal', 'tajuk_dok_baharu'
      if ($_SESSION['kod_status'] == 1) {
        $_SESSION['verifiedOK'] = 0;
        fnRunAlert("Sila pilih Status bagi dokumen baharu ini.");
      }
      elseif ($_SESSION['kod_status'] == 2) {
        $_SESSION['verifiedOK'] = 1;
      }
      elseif ($_SESSION['kod_status'] == 3) {
        if ($_POST['tarikh_mansuh'] != "") {
          $_SESSION['verifiedOK'] = 1;
        }
        else {
          $_SESSION['verifiedOK'] = 0;
          fnRunAlert("Sila lengkapkan maklumat status 'Dimansuhkan' dokumen ini.");
        }
      }
      elseif ($_SESSION['kod_status'] == 4) {
        if ($_POST['tarikh_serah'] != "" AND $_POST['kod_jab_asal'] != "1" AND $_POST['kod_jab_baharu'] != "1") {
          $_SESSION['verifiedOK'] = 1;
        }
        else {
          $_SESSION['verifiedOK'] = 0;
          fnRunAlert("Sila lengkapkan maklumat status 'Diserahkan kepada' dokumen ini.");
        }
      }
      elseif ($_SESSION['kod_status'] == 5) {
        if ($_POST['tarikh_pinda'] != "" AND $_POST['tajuk_dok_asal'] != "" AND $_POST['tajuk_dok_baharu'] != "") {
          $_SESSION['verifiedOK'] = 1;
        }
        else {
          $_SESSION['verifiedOK'] = 0;
          fnRunAlert("Sila lengkapkan maklumat status 'Pindaan kepada' dokumen ini.");
        }
      }
    }
    # muatnaik dokumen
    if ($_SESSION['verifiedOK'] == 1) {
      # baru boleh upload file
      $_SESSION['uploadOk'] = 0; // Assign initial value to 'uploadOk'
      fnUploadFilesUpdateDoc(); // the altered original version (the working one! 20161018)
    }
    # if file is uploaded, save record
    if ($_SESSION['verifiedOK'] == 1 AND $_SESSION['uploadOk'] == 1) {
      fnUpdateCheckedTeras($DBServer,$DBUser,$DBPass,$DBName);
      fnUpdateDoc($DBServer,$DBUser,$DBPass,$DBName);
      fnRunAlert("Rekod BERJAYA dikemaskini.");
      # kosongkan sessions
      fnClearSessionNewDoc();
      $_SESSION['updateDocOK'] = 1;
    }
    # jika fail tidak dapat dimuatnaik, input akan dipaparkan semula
    elseif ($_SESSION['verifiedOK'] == 0 OR $_SESSION['uploadOk'] == 0) {
      fnRunAlert("Rekod TIDAK dikemaskini.");
      $_SESSION['updateDocOK'] = 0;
    }
  }
}
?>
<!-- page content -->
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3><?php echo $_SESSION['page_title']; ?></h3>
      </div>

    </div>
    <div class="clearfix"></div>
    <?php  
    // if update button in table is not clicked
    if (isset($_POST['btn_batal_kemaskini'])) {
      fnClearSessionListDoc();
    }
    # if view button in table is clicked
    if ($_SESSION['status_papar_perincian_dokumen'] == 1) {
      $_SESSION['status_papar_perincian_dokumen'] = 0;
      $kod_dok_untuk_dipapar = $_SESSION['kod_dok_untuk_dipapar'];
      ?>
      <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <div class="x_panel">
            <div class="x_title">
              <h2><?php echo $_SESSION['preview_doc_title']; ?><small><?php echo $_SESSION['preview_doc_action']; ?></small></h2>
              <div class="clearfix"></div>
            </div>
            <div class="x_content">
              <br />
              <form id="form-kemaskini-data" action="<?php echo $actionfilename; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
                <?php 
                fnClearTerasDokSessionForUpdateForm();
                fnShowViewDocContent($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name); 
                ?>
                <div class="form-group">
                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3" align="center">
                    <input type="submit" id="btn_papar_borang_kemaskini_dari_perincian_dokumen" name="btn_papar_borang_kemaskini_dari_perincian_dokumen" class="btn btn-success" title="Buka Borang Kemaskini Dokumen" value="Buka Borang Kemaskini">
                    <input type="submit" id="btn_tutup_perincian_dokumen" name="btn_tutup_perincian_dokumen" class="btn btn-danger" title="Tutup" value="Tutup">
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
            <div class="title_right">
              <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                <div class="input-group">
                  <input type="text" id="txt_search_doclist" name="txt_search_doclist" class="form-control" placeholder="Cari...">
                  <span class="input-group-btn">
                    <input type="submit" id="btn_search_doclist" name="btn_search_doclist" class="btn btn-default" value="Cari!">
                  </span>
                </div>
              </div>
            </div>
            <!-- <table id="datatable-buttons" class="table table-striped table-bordered tablesorter"> -->
            <table id="myTable" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th width="40">Bil</th>
                  <th width="100" hidden>Kod</th>
                  <th>Sektor</th>
                  <th width="115">Jum. Dokumen</th>
                </tr>
              </thead>


              <tbody id="myTableBody">
                <?php 
                fnShowDocReportBySec($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name); 
                ?>
                <tr>
                  <td colspan="2" align="right"><strong>JUMLAH</strong></td>
                  <td align="center"><strong><?php echo $_SESSION['jum_semua_sek']; ?></strong></td>
                </tr>
              </tbody>
            </table>
            </form>
            <div class="col-md-12 text-center">
              <ul class="pagination pagination-sm pager" id="myPager"></ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <br>&nbsp;<br>&nbsp;<br>&nbsp;
    <!-- Habis borang dummy baharu -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../engine/bootstrap.tablesorter.js"></script>
    <script>
      $(document).ready(function(){
        // alert("Ok ready!");
        $('#btn_search_doclist').on('click', function(){
          <?php  

          ?>
        });
        $('#kod_status').on('load', function () {
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
                    $('.selectpicker').selectpicker('refresh');
                    break;
            }
        }); 
      });
      $('#myTableBody').pageMe({pagerSelector:'#myPager',showPrevNext:true,hidePageNumbers:false,perPage:10});

    </script>
    <!-- /page content -->
    <?php require "../layouts/lay_adminmainbottom.php"; ?>