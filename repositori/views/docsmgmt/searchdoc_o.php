<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
<?php  
$_SESSION['page_title'] = "Carian Rekod Dokumen";
$_SESSION['addnew_form_title'] = "Borang Carian Mudah";
$_SESSION['addnew_form_action'] = "Dokumen yang Telah Direkod";
// $_SESSION['update_form_title'] = "Borang Carian Lengkap";
$_SESSION['update_form_title'] = "Borang Carian";
$_SESSION['update_form_action'] = "Dokumen yang Telah Direkod";
/*$_SESSION['ss_table_title'] = "Hasil Carian Mudah Rekod Dokumen";*//*sebelum diubah pada 20180420*/
$_SESSION['ss_table_title'] = "Senarai Rekod Dokumen";
/*$_SESSION['ss_table_action'] = "Senaraikan Dokumen";*//*sebelum diubah pada 20180420*/
$_SESSION['ss_table_action'] = "Hasil Carian Mudah";
/*$_SESSION['as_table_title'] = "Hasil Carian Lengkap Rekod Dokumen";*//*sebelum diubah pada 20180420*/
/*$_SESSION['as_table_action'] = "Senaraikan Dokumen";*//*sebelum diubah pada 20180420*/
$_SESSION['as_table_title'] = "Senarai Rekod Dokumen";
$_SESSION['as_table_action'] = "Hasil Carian Lengkap";
$actionfilename = "searchdoc_o.php";
$table01name = "dokumen";
$field01name = "kod_data";
$field02name = "nama_data";

# when search page is opened from the menu
/*
sources:
	'' = new
	sf = simple find
	cf = complete find
	ou = open update form
	sfla = simple find list action
	u = update a record
	um = upload more documents
	uf = upload and fiinish
	du = done uploading
	ov = open view form
	d = delete a record
*/
// fnRunAlert("(1) s = ".$_GET['s']);
// fnRunAlert("(1) kod_dok_langkah_2 = ".$_SESSION['kod_dok_langkah_2']); // untuk lihat value selepas page refreshed
if ($_GET['s'] == '') {
	// fnRunAlert("Reset untuk buka baharu.");
  fnResetSessionsForPages();
}

// fnRunAlert("(1) pageSource = ".$_SESSION['pageSource']);
if ($_SESSION['pageSource'] == 'new') {
  $_GET['s'] = 'n';
  $_SESSION['pageSource'] = '';
  $_SESSION['kod_kat_step2'] = '';
  // $_SESSION['kod_dok_langkah_2'] = '';
  $_SESSION['langkah_kemas_kini'] = 1;
}

// fnRunAlert("(2) s = ".$_GET['s']);
// if ($_GET['s'] == "n" && isset($_GET['s'])) {
if ($_GET['s'] === "n") {
	$_SESSION['search_form_opened'] = "advanced";
	fnClearSimpleDocSearchSessions();
	fnClearAdvancedDocSearchSessions();
	$_SESSION['bil_dok_carian_lengkap'] = "";
	// fnRunAlert("bil_dok_carian_lengkap = ".$_SESSION['bil_dok_carian_lengkap']);
	$_SESSION['status_papar_perincian_dokumen'] = 0;
}

# when user clicked 'Cari' in simple search form
if (isset($_POST['sbmt_cari_mudah'])) {
	$_SESSION['search_form_opened'] = "simple";
	$_SESSION['kata_kunci_mudah'] = $_POST['kata_kunci_mudah'];
	# start verifying form
	$_SESSION['verifiedOK'] = 3; // initial value
	if ($_SESSION['verifiedOK'] == 3) {
		# 1. user logged in?
		# semak jika ada loggedinid; user yang sah telah log masuk
		if ($_SESSION['loggedinid'] != 0) {
			$_SESSION['verifiedOK'] = 1;
		}
		else {
			$_SESSION['verifiedOK'] = 0;
			fnRunAlert("Maaf, borang tidak dapat diproses kerana pengguna tidak log masuk dengan sah.");
		}
	    # semak katakunci diisi
		if ($_SESSION['verifiedOK'] == 1) {
			if ($_SESSION['kata_kunci_mudah'] != "") {
				$_SESSION['verifiedOK'] = 1;
			}
			else {
				$_SESSION['verifiedOK'] = 0;
				fnRunAlert("Sila masukkan kata kunci carian.");
			}
		}
		if ($_SESSION['verifiedOK'] == 1) {
			fnSearchDocSimple();
		}
	}
}

# when user clicked 'Cari' in advanced search form
if (isset($_POST['sbmt_cari_lengkap'])) {
	$_SESSION['bil_dok_carian_lengkap'] = "";
	$_SESSION['search_form_opened'] = "advanced"; # beri nilai
	// fnRunAlert("Carian lengkap dimulakan...");
	# kategori, tahun, tajuk, kementerian, agensi, sektor, bahagian, status
	fnClearAdvancedDocSearchSessions();
	$_SESSION['kod_dok_langkah_2'] = "";
	$_SESSION['cl_tajuk_dokumen'] = $_POST['cl_tajuk_dokumen']; # beri nilai drp borang
	if ($_POST['cl_tahun_dokumen'] <> "" && $_POST['cl_tahun_dokumen_hingga'] <> "") {
		// fnRunAlert("Kombinasi A");
		$_SESSION['cl_tahun_dokumen'] = $_POST['cl_tahun_dokumen']; # beri nilai drp borang
		$_SESSION['cl_tahun_dokumen_to_show_below_view'] = $_SESSION['cl_tahun_dokumen']; # beri nilai drp borang
		if ($_POST['cl_tahun_dokumen']>$_POST['cl_tahun_dokumen_hingga']) {
			$_SESSION['cl_tahun_dokumen_hingga_to_show_below_view'] = $_POST['cl_tahun_dokumen']; # beri nilai drp borang
			$_SESSION['cl_tahun_dokumen_hingga'] = $_SESSION['cl_tahun_dokumen_hingga_to_show_below_view'];
		} else {
			$_SESSION['cl_tahun_dokumen_hingga_to_show_below_view'] = $_POST['cl_tahun_dokumen_hingga']; # beri nilai drp borang
			$_SESSION['cl_tahun_dokumen_hingga'] = $_SESSION['cl_tahun_dokumen_hingga_to_show_below_view'];
		}
	}
	elseif ($_POST['cl_tahun_dokumen'] <> "" && $_POST['cl_tahun_dokumen_hingga'] == "") {
		// fnRunAlert("Kombinasi B");
		$_SESSION['cl_tahun_dokumen'] = $_POST['cl_tahun_dokumen']; # beri nilai drp borang
		$_SESSION['cl_tahun_dokumen_hingga'] = $_SESSION['cl_tahun_dokumen']; # beri nilai drp borang
		$_SESSION['cl_tahun_dokumen_hingga_to_show_below_view'] = $_SESSION['cl_tahun_dokumen']; # beri nilai drp borang						
	}
	elseif ($_POST['cl_tahun_dokumen'] == "" && $_POST['cl_tahun_dokumen_hingga'] <> "") {
		// fnRunAlert("Kombinasi C");
		$_SESSION['cl_tahun_dokumen_hingga'] = $_POST['cl_tahun_dokumen_hingga'];
		// fnRunAlert("cl_tahun_dokumen_hingga = ".$_SESSION['cl_tahun_dokumen_hingga']);
		$_SESSION['cl_tahun_dokumen'] = $_SESSION['cl_tahun_dokumen_hingga'];
		// fnRunAlert("cl_tahun_dokumen = ".$_SESSION['cl_tahun_dokumen']);
		$_SESSION['cl_tahun_dokumen_to_show_below_view'] = $_SESSION['cl_tahun_dokumen_hingga']; # beri nilai drp borang
		// fnRunAlert("cl_tahun_dokumen_to_show_below_view = ".$_SESSION['cl_tahun_dokumen_to_show_below_view']);
	}
	else {
		// fnRunAlert("Kombinasi D");
		$_SESSION['cl_tahun_dokumen'] = "";
		$_SESSION['cl_tahun_dokumen_to_show_below_view'] = "";
		$_SESSION['cl_tahun_dokumen_hingga'] = "";
		$_SESSION['cl_tahun_dokumen_hingga_to_show_below_view'] = "";
	}
	// fnRunAlert("cl_tahun_dokumen = ".$_SESSION['cl_tahun_dokumen']." & cl_tahun_dokumen_hingga = ".$_SESSION['cl_tahun_dokumen_hingga']);
	// fnRunAlert("cl_tahun_dokumen_to_show_below_view = ".$_SESSION['cl_tahun_dokumen_to_show_below_view']);
	$_SESSION['kod_kat'] = $_POST['kod_kat']; # beri nilai drp borang
	$_SESSION['kod_sektor'] = $_POST['kod_sektor']; # beri nilai drp borang
	$_SESSION['kod_bah'] = $_POST['kod_bah']; # beri nilai drp borang
	$_SESSION['kod_status'] = $_POST['kod_status']; # beri nilai drp borang
	$_SESSION['cl_tajuk_dokumen_to_show_below_view'] = $_POST['cl_tajuk_dokumen']; # beri nilai drp borang
	// $_SESSION['cl_tahun_dokumen_to_show_below_view'] = $_POST['cl_tahun_dokumen']; # beri nilai drp borang
	$_SESSION['kod_kat_to_show_below_view'] = $_POST['kod_kat']; # beri nilai drp borang
	$_SESSION['kod_sektor_to_show_below_view'] = $_POST['kod_sektor']; # beri nilai drp borang
	$_SESSION['kod_bah_to_show_below_view'] = $_POST['kod_bah']; # beri nilai drp borang
	$_SESSION['kod_status_to_show_below_view'] = $_POST['kod_status']; # beri nilai drp borang
	
	// fnRunAlert("kod_kat=$_SESSION[kod_kat]");
	// fnRunAlert("tajuk=$_SESSION[cl_tajuk_dokumen]");
	// fnRunAlert("tahun=$_SESSION[cl_tahun_dokumen]");
	// fnRunAlert("kod_sektor=$_SESSION[kod_sektor]");
	// fnRunAlert("kod_bah=$_SESSION[kod_bah]");
	// fnRunAlert("kod_status=$_SESSION[kod_status]");
	# 63 kombinasi carian
	# set kepada 0
	// $_SESSION['kombinasi_cl_dok'] = 0;
	## kombinasi 01 - 000001
	if ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 1;
		// fnRunAlert("kombinasi yg sesuai 1");
	}
	## kombinasi 02 - 000010
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 2;
		// fnRunAlert("kombinasi yg sesuai 2");
	}
	## kombinasi 03 - 000011
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 3;
		// fnRunAlert("kombinasi yg sesuai 3");
	}
	## kombinasi 04 - 000100
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 4;
		// fnRunAlert("kombinasi yg sesuai 4");
	}
	## kombinasi 05 - 000101
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 5;
		// fnRunAlert("kombinasi yg sesuai 5");
	}
	## kombinasi 06 - 000110
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 6;
		// fnRunAlert("kombinasi yg sesuai 6");
	}
	## kombinasi 07 - 000111
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 7;
		// fnRunAlert("kombinasi yg sesuai 7");
	}
	## kombinasi 08 - 001000
	elseif ($_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 8;
		// fnRunAlert("kombinasi yg sesuai 8");
	}
	## kombinasi 09 - 001001
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 9;
		// fnRunAlert("kombinasi yg sesuai 9");
	}
	## kombinasi 10 - 001010
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 10;
		// fnRunAlert("kombinasi yg sesuai 10");
	}
	## kombinasi 11 - 001011
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 11;
		// fnRunAlert("kombinasi yg sesuai 11");
	}
	## kombinasi 12 - 001100
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 12;
		// fnRunAlert("kombinasi yg sesuai 12");
	}
	## kombinasi 13 - 001101
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 13;
		// fnRunAlert("kombinasi yg sesuai 13");
	}
	## kombinasi 14 - 001110
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 14;
		// fnRunAlert("kombinasi yg sesuai 14");
	}
	## kombinasi 15 - 001111
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 15;
		// fnRunAlert("kombinasi yg sesuai 15");
	}
	## kombinasi 16 - 010000
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 16;
		// fnRunAlert("kombinasi yg sesuai 16");
	}
	## kombinasi 17 - 010001
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 17;
		// fnRunAlert("kombinasi yg sesuai 17");
	}
	## kombinasi 18 - 010010
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 18;
		// fnRunAlert("kombinasi yg sesuai 18");
	}
	## kombinasi 19 - 010011
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 19;
		// fnRunAlert("kombinasi yg sesuai 19");
	}
	## kombinasi 20 - 010100
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 20;
		// fnRunAlert("kombinasi yg sesuai 20");
	}
	## kombinasi 21 - 010101
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 21;
		// fnRunAlert("kombinasi yg sesuai 21");
	}
	## kombinasi 22 - 010110
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 22;
		// fnRunAlert("kombinasi yg sesuai 22");
	}
	## kombinasi 30 - 010111
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 23;
		// fnRunAlert("kombinasi yg sesuai 23");
	}
	## kombinasi 20 - 011000
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 24;
		// fnRunAlert("kombinasi yg sesuai 24");
	}
	## kombinasi 30 - 011001
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 25;
		// fnRunAlert("kombinasi yg sesuai 25");
	}
	## kombinasi 30 - 011010
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 26;
		// fnRunAlert("kombinasi yg sesuai 26");
	}
	## kombinasi 30 - 011011
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 27;
		// fnRunAlert("kombinasi yg sesuai 27");
	}
	## kombinasi 30 - 011100
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 28;
		// fnRunAlert("kombinasi yg sesuai 28");
	}
	## kombinasi 30 - 011101
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 29;
		// fnRunAlert("kombinasi yg sesuai 29");
	}
	## kombinasi 30 - 011110
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 30;
		// fnRunAlert("kombinasi yg sesuai 30");
	}
	## kombinasi 04 - 011111
	elseif ($_SESSION['cl_tajuk_dokumen'] == "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 31;
		// fnRunAlert("kombinasi yg sesuai 31");
	}
	## kombinasi 08 - 100000
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 32;
		// fnRunAlert("kombinasi yg sesuai 32");
	}
	## kombinasi 19 - 100001
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 33;
		// fnRunAlert("kombinasi yg sesuai 33");
	}
	## kombinasi 18 - 100010
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 34;
		// fnRunAlert("kombinasi yg sesuai 34");
	}
	## kombinasi 30 - 100011
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 35;
		// fnRunAlert("kombinasi yg sesuai 35");
	}
	## kombinasi 17 - 100100
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 36;
		// fnRunAlert("kombinasi yg sesuai 36");
	}
	## kombinasi 30 - 100101
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 37;
		// fnRunAlert("kombinasi yg sesuai 37");
	}
	## kombinasi 30 - 100110
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 38;
		// fnRunAlert("kombinasi yg sesuai 38");
	}
	## kombinasi 30 - 100111
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 39;
		// fnRunAlert("kombinasi yg sesuai 39");
	}
	## kombinasi 16 - 101000
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 40;
		// fnRunAlert("kombinasi yg sesuai 40");
	}
	## kombinasi 30 - 101001
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 41;
		// fnRunAlert("kombinasi yg sesuai 41");
	}
	## kombinasi 30 - 101010
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 42;
		// fnRunAlert("kombinasi yg sesuai 42");
	}
	## kombinasi 30 - 101011
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 43;
		// fnRunAlert("kombinasi yg sesuai 43");
	}
	## kombinasi 30 - 101100
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 44;
		// fnRunAlert("kombinasi yg sesuai 44");
	}
	## kombinasi 30 - 101101
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 45;
		// fnRunAlert("kombinasi yg sesuai 45");
	}
	## kombinasi 30 - 101110
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 46;
		// fnRunAlert("kombinasi yg sesuai 46");
	}
	## kombinasi 03 - 101111
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] == "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 47;
		// fnRunAlert("kombinasi yg sesuai 47");
	}
	## kombinasi 15 - 110000
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 48;
		// fnRunAlert("kombinasi yg sesuai 48");
	}
	## kombinasi 30 - 110001
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 49;
		// fnRunAlert("kombinasi yg sesuai 49");
	}
	## kombinasi 30 - 110010
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 50;
		// fnRunAlert("kombinasi yg sesuai 50");
	}
	## kombinasi 30 - 110011
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 51;
		// fnRunAlert("kombinasi yg sesuai 51");
	}
	## kombinasi 30 - 110100
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 52;
		// fnRunAlert("kombinasi yg sesuai 52");
	}
	## kombinasi 30 - 110101
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 53;
		// fnRunAlert("kombinasi yg sesuai 53");
	}
	## kombinasi 30 - 110110
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 54;
		// fnRunAlert("kombinasi yg sesuai 54");
	}
	## kombinasi 02 - 110111
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] == 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 55;
		// fnRunAlert("kombinasi yg sesuai 55");
	}
	## kombinasi 30 - 111000
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 56;
		// fnRunAlert("kombinasi yg sesuai 56");
	}
	## kombinasi 30 - 111001
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 57;
		// fnRunAlert("kombinasi yg sesuai 57");
	}
	## kombinasi 30 - 111010
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 58;
		// fnRunAlert("kombinasi yg sesuai 58");
	}
	## kombinasi 05 - 111011
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] == 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 59;
		// fnRunAlert("kombinasi yg sesuai 59");
	}
	## kombinasi 30 - 111100
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 60;
		// fnRunAlert("kombinasi yg sesuai 60");
	}
	## kombinasi 06 - 111101
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] == 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 61;
		// fnRunAlert("kombinasi yg sesuai 61");
	}
	## kombinasi 07 - 111110
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] == 1) {
		$_SESSION['kombinasi_cl_dok'] = 62;
		// fnRunAlert("kombinasi yg sesuai 62");
	}
	## kombinasi 01 - 111111
	elseif ($_SESSION['cl_tajuk_dokumen'] != "" AND $_SESSION['cl_tahun_dokumen'] != "" AND $_SESSION['kod_kat'] != 1 AND $_SESSION['kod_sektor'] != 1 AND $_SESSION['kod_bah'] != 1 AND $_SESSION['kod_status'] != 1) {
		$_SESSION['kombinasi_cl_dok'] = 63;
		// fnRunAlert("kombinasi yg sesuai 63");
	}
	else {
		$_SESSION['kombinasi_cl_dok'] = 0;
		// fnRunAlert("kombinasi yg sesuai 0");
	}
	# start checking form
	$_SESSION['verifiedOK'] = 3; # beri nilai awal
	if ($_SESSION['verifiedOK'] == 3) {
		# 1. user logged in?
		# semak jika ada loggedinid; user yang sah telah log masuk
		$_SESSION['field_filled_counter'] = 0;
		if ($_SESSION['loggedinid'] != 0) {
			$_SESSION['verifiedOK'] = 1;
		}
		else {
			$_SESSION['verifiedOK'] = 0;
			fnRunAlert("Maaf, borang tidak dapat diproses kerana pengguna tidak log masuk dengan sah.");
		}
	    # semak katakunci diisi
		if ($_SESSION['verifiedOK'] == 1) {
			if ($_SESSION['cl_tahun_dokumen'] != "") {
				$_SESSION['field_filled_counter'] += 1;
			}
			if ($_SESSION['cl_tajuk_dokumen'] != "") {
				$_SESSION['field_filled_counter'] += 1;
			}
			if ($_SESSION['kod_kat'] != 1) {
				$_SESSION['field_filled_counter'] += 1;
			}
			if ($_SESSION['kod_sektor'] != 1) {
				$_SESSION['field_filled_counter'] += 1;
			}
			if ($_SESSION['kod_bah'] != 1) {
				$_SESSION['field_filled_counter'] += 1;
			}
			if ($_SESSION['kod_status'] != 1) {
				$_SESSION['field_filled_counter'] += 1;
			}
		}
		if ($_SESSION['field_filled_counter'] > 0) {
			// fnRunAlert("field filled=$_SESSION[field_filled_counter]");
			$_SESSION['kombinasi_cl_dok_utk_ulang'] = $_SESSION['kombinasi_cl_dok'];
			fnSearchDocAdvanced();
			fnClearAdvancedDocSearchSessions(); // komenkan sementara 20180808 1710
            // fnRunAlert("$_SESSION[bil_dok_carian_lengkap] bil hasil carian lengkap selepas function.php");
		}
		else {
			fnRunAlert("Sila isi medan carian atau pilih salah satu kriteria rekod.");
		}
	}
} // sbmt_cari_lengkap

# When a user clicks the 'show doc detail'
if (isset($_POST['btn_papar_perincian_dokumen'])) {
	// fnRunAlert("$_POST[btn_papar_perincian_dokumen]");
	// $_SESSION['updateDocOK'] = 0;
	// $_SESSION['status_papar_perincian_dokumen'] = 0;
	$_SESSION['status_papar_perincian_dokumen'] = 1;
	// $_SESSION['status_buka_borang_kemaskini_dokumen'] = "";
	// $_SESSION['status_buka_borang_kemaskini_dokumen'] = 0;
	// $_SESSION['kod_dok_untuk_dikemaskini'] = $_POST['btn_papar_perincian_dokumen'];
	$_SESSION['kod_dok_untuk_dipapar'] = $_POST['btn_papar_perincian_dokumen'];
}

/* kalau pengguna klik butang/ikon KEMAS KINI untuk buka borang kemas kini */
if (isset($_POST['btn_papar_borang_kemaskini'])) {
	$_SESSION['kombinasi_cl_dok'] = $_SESSION['kombinasi_cl_dok_utk_ulang'];
	fnSearchDocAdvanced();
	$_SESSION['bil_dok_carian_lengkap'] = $_SESSION['bil_dok_carian_lengkap_utk_ulang'];
	// fnRunAlert($_SESSION['bil_dok_carian_lengkap']);
	$_SESSION['status_buka_borang_kemaskini_dokumen'] = 1;
	$_SESSION['kod_dok_untuk_dikemaskini'] = $_POST['btn_papar_borang_kemaskini'];
	$_SESSION['kod_dok_langkah_2'] = $_POST['btn_papar_borang_kemaskini'];
	$_SESSION['langkah_kemas_kini'] = 1;
	// $_SESSION['kod_dok_langkah_2'] = $_SESSION['kod_dok_untuk_dikemaskini'];
}
else {
	$_SESSION['status_buka_borang_kemaskini_dokumen'] = 0;
	$_SESSION['kod_dok_untuk_dikemaskini'] = "";
}

/* bila pengguna ketik pada butang kemas kini SAHAJA dalam BORANG KEMAS KINI */
if (isset($_POST['btn_simpan_dok_dikemaskini'])) {
	// fnRunAlert("Dah ketik butang Kemas Kini!");
	fnClearSessionNewDoc();
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
	$_SESSION['kod_jab_asal'] = $_POST['kod_jab_asal'];
	$_SESSION['kod_jab_asal'] = checkAndRevalue($_SESSION['kod_jab_asal']);
	$_SESSION['kod_jab_baharu'] = $_POST['kod_jab_baharu'];
	$_SESSION['kod_jab_baharu'] = checkAndRevalue($_SESSION['kod_jab_baharu']);
	$_SESSION['tag_dokumen'] = $_POST['tag_dokumen'];
	$_SESSION['tag_dokumen'] = checkAndRevalue($_SESSION['tag_dokumen']);
	$_SESSION['catatan_dokumen'] = $_POST['catatan_dokumen'];
	$_SESSION['catatan_dokumen'] = checkAndRevalue($_SESSION['catatan_dokumen']);
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
	fnSetTarikhStatusDoc();
	$_SESSION['tarikh_dok'] = date("Y-m-d");
	$_SESSION['tarikh_dok'] = checkAndRevalue($_SESSION['tarikh_dok']);
	$_SESSION['tarikh_kemaskini'] = date("Y-m-d");
	$_SESSION['tarikh_kemaskini'] = checkAndRevalue($_SESSION['tarikh_kemaskini']);

	# start verifying form
	$_SESSION['verifiedOK'] = 3; // initial value
	if ($_SESSION['verifiedOK'] != 0) {
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
		}
	    # if file is uploaded, save record
		if ($_SESSION['verifiedOK'] == 1) {
			// fnUpdateCheckedTerasForSearchModule_v2($DBServer,$DBUser,$DBPass,$DBName);
			fnInsertCheckedTerasForUpdatingInSearchModule($DBServer,$DBUser,$DBPass,$DBName);
			fnUpdateDocNoUpload($DBServer,$DBUser,$DBPass,$DBName);
			// fnRunAlert("Rekod BERJAYA dikemaskini.");
			# kosongkan sessions
			// fnClearSessionNewDoc();
			$_SESSION['updateDocOK'] = 1;
		}
	    # jika fail tidak dapat dimuatnaik, input akan dipaparkan semula
		elseif ($_SESSION['verifiedOK'] == 0 OR $_SESSION['uploadOk'] == 0) {
			fnRunAlert("Rekod TIDAK dikemaskini.");
			$_SESSION['updateDocOK'] = 0;
		}
	}
//}

/* bila pengguna ketik pada butang kemas kini DAN MUAT NAIK dalam BORANG KEMAS KINI */
if (isset($_POST['btn_simpan_dok_muatnaik'])) {
	// fnRunAlert("Dah ketik butang Kemas Kini!");
	fnClearSessionNewDoc();
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
	$_SESSION['kod_jab_asal'] = $_POST['kod_jab_asal'];
	$_SESSION['kod_jab_asal'] = checkAndRevalue($_SESSION['kod_jab_asal']);
	$_SESSION['kod_jab_baharu'] = $_POST['kod_jab_baharu'];
	$_SESSION['kod_jab_baharu'] = checkAndRevalue($_SESSION['kod_jab_baharu']);
	$_SESSION['tag_dokumen'] = $_POST['tag_dokumen'];
	$_SESSION['tag_dokumen'] = checkAndRevalue($_SESSION['tag_dokumen']);
	$_SESSION['catatan_dokumen'] = $_POST['catatan_dokumen'];
	$_SESSION['catatan_dokumen'] = checkAndRevalue($_SESSION['catatan_dokumen']);
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
	fnSetTarikhStatusDoc();
	$_SESSION['tarikh_dok'] = date("Y-m-d");
	$_SESSION['tarikh_dok'] = checkAndRevalue($_SESSION['tarikh_dok']);
	$_SESSION['tarikh_kemaskini'] = date("Y-m-d");
	$_SESSION['tarikh_kemaskini'] = checkAndRevalue($_SESSION['tarikh_kemaskini']);

	# start verifying form
	$_SESSION['verifiedOK'] = 3; // initial value
	if ($_SESSION['verifiedOK'] != 0) {
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
		}
		if ($_SESSION['verifiedOK'] == 1) {
			// fnRunAlert("(2) kod_dok_langkah_2 = ".$_SESSION['kod_dok_langkah_2']);
			$_SESSION['kod_dok_untuk_dikemaskini'] = $_SESSION['kod_dok_langkah_2'];
			$_SESSION['kod_dok_to_be_updated'] = $_SESSION['kod_dok_untuk_dikemaskini'];
			// fnRunAlert("(965) kod_dok_untuk_dikemaskini = ".$_SESSION['kod_dok_untuk_dikemaskini']);
			// fnUpdateCheckedTerasForSearchModule_v2($DBServer,$DBUser,$DBPass,$DBName);
			fnInsertCheckedTerasForUpdatingInSearchModule($DBServer,$DBUser,$DBPass,$DBName);
			fnUpdateDocNoUpload($DBServer,$DBUser,$DBPass,$DBName);
			// fnRunAlert("Rekod BERJAYA dikemaskini.");
			# kosongkan sessions
			// fnClearSessionNewDoc();
			$_SESSION['updateDocOK'] = 1;
			$_SESSION['langkah_kemas_kini'] = 2;
		}
    # jika fail tidak dapat dimuatnaik, input akan dipaparkan semula
		elseif ($_SESSION['verifiedOK'] == 0) {
			fnRunAlert("Rekod TIDAK dikemaskini.");
			$_SESSION['updateDocOK'] = 0;
		}
	}
//}

// when 'btn_simpan_dok_sokongan_baharu' is pressed/clicked
if ((isset($_POST['btn_simpan_dok_sokongan_baharu']) OR isset($_POST['btn_simpan_dok_sokongan_baharu_akhir']))) {
	if (isset($_POST['btn_simpan_dok_sokongan_baharu_akhir'])) {
		$_SESSION['langkah_kemas_kini'] = 1;
	}
  fnClearSessionNewDoc();
  # dapatkan kategori dok yang telah disimpan
  $_SESSION['kod_kat_step2'];
  # dapatkan bil_dok yang telah disimpan
  $_SESSION['bil_dok_step2'];
  # dapatkan tahun dok yang telah disimpan
  $_SESSION['tahun_dok_step2'];
  # dapatkan  kod_dok menggunakan maklumat 3 medan di atas

  // fnGetDocCodeUsingAvailableInfo();
  $_SESSION['kod_dok_step2'] = $_SESSION['kod_dok_langkah_2'];
  // fnRunAlert("(3) kod_dok_langkah_2 = ".$_SESSION['kod_dok_langkah_2']);
  // fnRunAlert("kod_dok_step2 = ".$_SESSION['kod_dok_step2']);

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
      fnPreUploadFilesRename_v2();
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
      // fnRunAlert("slot01_OK = ".$_SESSION['slot01_OK']);
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
      fnInsertNewSupportDocForUpdate_v2($DBServer,$DBUser,$DBPass,$DBName);
      if ($_SESSION['slot01_OK'] == 1) {
        fnRunAlert("Rekod BERJAYA dikemaskini.");
        if (isset($_POST['btn_simpan_dok_sokongan_baharu_akhir'])) {
          $_SESSION['langkah_kemas_kini'] = 1;
        } else {
          $_SESSION['langkah_kemas_kini'] = 2;
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
	$_SESSION['langkah_kemas_kini'] = 1;
}

if ($_SESSION['search_form_opened'] == "simple") {
	$_SESSION['simple_form_search_status'] = "";
	$_SESSION['advanced_form_search_status'] = "hidden";
	$_SESSION['select_menu_none'] = "";
	$_SESSION['select_menu_simple'] = "selected";
	$_SESSION['select_menu_advanced'] = "";
}
elseif ($_SESSION['search_form_opened'] == "advanced") {
	$_SESSION['simple_form_search_status'] = "hidden";
	$_SESSION['advanced_form_search_status'] = "";
	$_SESSION['select_menu_none'] = "";
	$_SESSION['select_menu_simple'] = "";
	$_SESSION['select_menu_advanced'] = "selected";
}
else {
	$_SESSION['simple_form_search_status'] = "hidden";
	$_SESSION['advanced_form_search_status'] = "hidden";
	$_SESSION['select_menu_none'] = "selected";
	$_SESSION['select_menu_simple'] = "";
	$_SESSION['select_menu_advanced'] = "";
}
?>
<!-- page content -->
<div class="right_col" role="main">
	<div class="">
		<div class="page-title">
			<div class="title_left">
				<h3><?php echo $_SESSION['page_title']; ?></h3>
				<?php $_GET['s'] = ''; ?>
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
		<div hidden class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_panel">
					<select class="form-control selectpicker" id="kod_pilihan_carian" name="kod_pilihan_carian" required="required">
						<option value="1" <?php echo $_SESSION['select_menu_none']; ?>>Sila pilih...</option>
						<option value="2" <?php echo $_SESSION['select_menu_simple']; ?>>Carian Mudah</option>
						<option value="3" <?php echo $_SESSION['select_menu_advanced']; ?>>Carian Lengkap</option>
					</select>
				</div>
			</div>
		</div>
		<?php  
		# mesej 'tiada pilihan dibuat'
		if ($_SESSION['search_form_opened'] == "none") {
			?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12" id="divmsgpilihan">
					<div class="x_panel">
						<h2>Sila pilih jenis carian untuk meneruskan operasi.</h2>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<!-- paparan session penting -->
		<div class="row"><!-- div to display the value for each of the selected sessions -->
			<div class="col-md-12 col-sm-12 col-xs-12" hidden>
				<div class="x_panel">
					<table>
						<caption>SESSIONS</caption>
						<thead>
							<tr>
								<th width="50">sfo</th>
								<th width="50">sfss</th>
								<th width="50">afss</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php echo $_SESSION['search_form_opened']; ?></td>
								<td><?php echo $_SESSION['simple_form_search_status']; ?></td>
								<td><?php echo $_SESSION['advanced_form_search_status']; ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<!-- borang carian mudah -->
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12" id="divsimplesearch" <?php echo $_SESSION['simple_form_search_status']; ?>>
				<div class="x_panel">
					<div class="x_title">
						<h2><?php echo $_SESSION['addnew_form_title']; ?><small><?php echo $_SESSION['addnew_form_action']; ?></small></h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<form id="form-data-baharu" action="<?php echo $actionfilename."?s=sf"; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kata_kunci_mudah">Kata Kunci Carian <span class="required">*</span>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<input type="text" id="kata_kunci_mudah" name="kata_kunci_mudah" required="required" autofocus class="form-control col-md-7 col-xs-12">
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
									<input type="submit" id="sbmt_cari_mudah" name="sbmt_cari_mudah" class="btn btn-success" value="Cari">
									<button type="reset" class="btn btn-danger">Kosongkan</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php  
    if ($_SESSION['langkah_kemas_kini']==2) {
      $_SESSION['pageSource'] = '';
      // fnRunAlert("langkah_kemas_kini = ".$_SESSION['langkah_kemas_kini']);
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
              <form id="form-dok-sokongan-baharu" data-parsley-validate class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data" action="searchdoc_o.php?s=ud">
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
                    <input type="submit" class="btn btn-danger" id="btn_selesai_muatnaik" name="btn_selesai_muatnaik" title="Selesai Muat Naik & Kembali ke Borang Daftar Dokumen Baharu" value="Keluar">
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
                      // fnRunAlert("(4) kod_dok_langkah_2 = ".$_SESSION['kod_dok_langkah_2']);
                      if (!isset($_SESSION['kod_dok_langkah_2']) || $_SESSION['kod_dok_langkah_2'] == "") {
                      	$_SESSION['kod_dok_langkah_2'] = $_SESSION['kod_dok_untuk_dikemaskini'];
                      }
                      $sql="SELECT * FROM dok_sokongan WHERE kod_dok_fk = ".$_SESSION['kod_dok_langkah_2'];

                      $rs=$conn->query($sql);

                      if($rs === false) {
                          trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                      } else {
                          $rows_returned = $rs->num_rows;
                          // fnRunAlert("Bil. rekod ditemui = ".$rows_returned);
                      }

                      if ($rows_returned > 0) {
                        $rs=$conn->query($sql);

                        if($rs === false) {
                            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr = $rs->fetch_all(MYSQLI_ASSOC);
                        }

                        $table_number = 1;
                        $matching_file_counter = 0;
                        $bil_fail_wujud = 0;
                        foreach($arr as $row) {
                          $target_dir = "../papers/";
                          $target_file = "$target_dir" . $row['nama_dok_disimpan'];
                          if (file_exists($target_file)) {
                            // fnRunAlert("File ni ada dlm folder.".$target_file);
                            $bil_fail_wujud++;
                            ?>
                            <tr class="table table-striped">
                              <th scope="row"><?= $table_number; ?></th>
                              <td><?= $row['nama_dok_asal'] ?></td>
                              <td><?= $row['nama_dok_disimpan'] ?></td>
                              <td>
                                <form id="form-dok-baharu" data-parsley-validate class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data" action="searchdoc_o.php?s=s2">
                                  <button type="submit" name="btn_hapus_dok_sokongan" id="btn_hapus_dok_sokongan" title="Hapus Dokumen <?= $row['nama_dok_disimpan'] ?>" value="<?= $row['nama_dok_disimpan'] ?>" onclick="return confirm('Anda pasti untuk hapuskan dokumen sokongan ini?')" class="btn btn-danger"><span class="fa fa-trash"></span></button>
                                  </form>
                              </td>
                            </tr>
                            <?php
                            $table_number++;  
                            $matching_file_counter++;
                          }
                        }
                        if ($matching_file_counter == 0 OR $bil_fail_wujud == 0) {
	                        ?>
	                        <tr class="table table-striped" align="right">
	                          <th scope="row" colspan="4">Dokumen sokongan tiada dalam direktori.</th>
	                        </tr>
	                        <?php
                        }
                      }
                      elseif($rows_returned == 0) {
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
		<!-- borang carian lengkap -->
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12" id="divadvancedsearch" <?php echo $_SESSION['advanced_form_search_status']; ?>>
				<div class="x_panel">
					<div class="x_title">
						<h2><?php echo $_SESSION['update_form_title']; ?><small><?php echo $_SESSION['update_form_action']; ?></small></h2>
						<div class="clearfix"></div>
					</div>
					<div class="x_content">
						<form id="form-data-baharu" action="<?php echo $actionfilename."?s=f"; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
							<!-- kategori, tahun, tajuk, kementerian, agensi, sektor, bahagian, status -->
			                <?php  
			                ?>
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="cl_tajuk_dokumen">Tajuk & Deskripsi Dokumen <span class="required" hidden>*</span>
								</label>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<input type="text" id="cl_tajuk_dokumen" name="cl_tajuk_dokumen" class="form-control col-md-7 col-xs-12" maxlength="150">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3 col-sm-3 col-xs-12" for="cl_tahun_dokumen">Tahun Dokumen <span class="required" hidden>*</span>
								</label>
								<div class="col-md-2 col-sm-2 col-xs-12">
									<?php /*<input value="" type="text" id="cl_tahun_dokumen" name="cl_tahun_dokumen" class="form-control col-md-7 col-xs-12" maxlength="4" pattern="\d{1,4}">*/ ?>
									<select class="form-control col-md-7 col-xs-12" id="cl_tahun_dokumen" name="cl_tahun_dokumen">
										<option value="">Sila pilih...</option>
										<?php  
										$tahunTerawal = 1940;
										$tahunSemasa = date("Y");
										// fnRunAlert("Tahun Semasa = ".$tahunSemasa);
										$tahunDipaparkan = $tahunTerawal;
										while ($tahunDipaparkan <= $tahunSemasa) {
											?>
											<option value="<?= $tahunDipaparkan; ?>"><?= $tahunDipaparkan; ?></option>
											<?php
											$tahunDipaparkan++;
										}
										?>
									</select>								
								</div>
								<label class="control-label col-md-2 col-sm-2 col-xs-12" for="cl_tahun_dokumen_hingga">(hingga) <span class="required" hidden>*</span>
								</label>
								<div class="col-md-2 col-sm-2 col-xs-12">
									<select class="form-control col-md-7 col-xs-12" id="cl_tahun_dokumen_hingga" name="cl_tahun_dokumen_hingga">
										<option value="">Sila pilih...</option>
										<?php  
										$tahunTerawal = 1940;
										$tahunSemasa = date("Y");
										// fnRunAlert("Tahun Semasa = ".$tahunSemasa);
										$tahunDipaparkan = $tahunSemasa;
										while ($tahunDipaparkan >= $tahunTerawal) {
											?>
											<option value="<?= $tahunDipaparkan; ?>"><?= $tahunDipaparkan; ?></option>
											<?php
											$tahunDipaparkan--;
										}
										?>
									</select>
									<?php /*<input value="" type="text" id="_hingga" name="cl_tahun_dokumen_hingga" class="form-control col-md-7 col-xs-12" maxlength="4" pattern="\d{1,4}">*/ ?>
								</div>
							</div>
							<?php  
			                fnDropdownKategoriForSearch($DBServer,$DBUser,$DBPass,$DBName); /*tambah ForSearch untuk borang carian lengkap 20180420 syedfaizal*/
			                // fnDropdownKem($DBServer,$DBUser,$DBPass,$DBName);
			                // fnDropdownJab($DBServer,$DBUser,$DBPass,$DBName,'kod_jab');
			                fnDropdownSektorForSearch($DBServer,$DBUser,$DBPass,$DBName); 
			                fnDropdownBahagianForSearch($DBServer,$DBUser,$DBPass,$DBName); 
			                fnDropdownStatusDokForSearch($DBServer,$DBUser,$DBPass,$DBName);
							?>
							<div class="form-group">
								<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
									<input type="submit" id="sbmt_cari_lengkap" name="sbmt_cari_lengkap" class="btn btn-success" value="Cari">
									<button type="reset" class="btn btn-danger">Kosongkan</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php  
		# mesej hasil carian lengkap
		// if ((isset($_SESSION['bil_dok_carian_mudah']) AND $_SESSION['bil_dok_carian_mudah'] == "z") OR (isset($_SESSION['bil_dok_carian_lengkap']) AND $_SESSION['bil_dok_carian_lengkap'] == "z")) {
			?>
            <!-- <br>&nbsp;<br>&nbsp; -->
            <?php
		// }
		if (isset($_SESSION['search_form_opened']) AND $_SESSION['search_form_opened'] == "simple") {
			if (!isset($_SESSION['bil_dok_carian_mudah'])) {
				$_SESSION['bil_dok_carian_mudah'] = "z";
				// fnRunAlert("bil hasil carian mudah a = ".$_SESSION['bil_dok_carian_mudah']);
			}
			// fnRunAlert("bil hasil carian mudah b = ".$_SESSION['bil_dok_carian_mudah']);
			// fnRunAlert("Form yang dibuka $_SESSION[search_form_opened]");
			// fnRunAlert("$_SESSION[bil_dok_carian_mudah] carian mudah");
			if ($_SESSION['bil_dok_carian_mudah'] >= 0 AND $_SESSION['bil_dok_carian_mudah'] <> "z") {
				// fnRunAlert("Ada bil hasil carian mudah.");
				// fnRunAlert("bil hasil carian mudah c = ".$_SESSION['bil_dok_carian_mudah']);
				?>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12" id="divmsgbilcarianmudah" >
						<div class="x_panel">
							<!-- <h4><?php // echo $_SESSION['bil_dok_carian_mudah']; ?> rekod "<?php // echo $_SESSION['kata_kunci_mudah']; ?>" rekod carian mudah telah ditemui.</h2> --><!-- kod asal pada 20180420 -->
							<h4><?php echo $_SESSION['bil_dok_carian_mudah']; ?> rekod telah ditemui.</h2><!-- kod selepas mengikut permintaan BDT pada 20180420 -->
						</div>
						<?php  
						// unset($_SESSION['bil_dok_carian_mudah']);
						?>
					</div>
				</div>
				<?php
			}
			else {
				// fnRunAlert("bil hasil carian mudah d = ".$_SESSION['bil_dok_carian_mudah']);
				?>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12" id="divmsgbilcarianmudah" >
						<div class="x_panel">
							<!-- <h4><?php // echo $_SESSION['bil_dok_carian_mudah']; ?> rekod "<?php // echo $_SESSION['kata_kunci_mudah']; ?>" rekod carian mudah telah ditemui.</h2> --><!-- kod asal pada 20180420 -->
							<h4><?php echo $_SESSION['bil_dok_carian_mudah']; ?> rekod telah ditemui.</h2><!-- kod selepas mengikut permintaan BDT pada 20180420 -->
						</div>
						<?php  
						// unset($_SESSION['bil_dok_carian_mudah']);
						?>
					</div>
				</div>
				<?php
			}
		}
		if (isset($_SESSION['search_form_opened']) AND $_SESSION['search_form_opened'] == "advanced") {
			if (!isset($_SESSION['bil_dok_carian_lengkap'])) {
				$_SESSION['bil_dok_carian_lengkap'] = "z";
			}
			// fnRunAlert("Form yang dibuka $_SESSION[search_form_opened]");
			// fnRunAlert("$_SESSION[bil_dok_carian_lengkap] carian lengkap");
			if (isset($_SESSION['bil_dok_carian_lengkap']) AND $_SESSION['bil_dok_carian_lengkap'] > 0 AND $_SESSION['bil_dok_carian_lengkap'] != "z" AND $_SESSION['bil_dok_carian_lengkap'] <> "") {
				// fnRunAlert("bil_dok_carian_lengkap = ".$_SESSION['bil_dok_carian_lengkap']);
				// fnRunAlert($_SESSION['bil_dok_carian_lengkap']);
				// fnRunAlert("paparkan mesej hasil carian lengkap");
				$_SESSION['bil_dok_carian_lengkap_utk_ulang'] = $_SESSION['bil_dok_carian_lengkap'];
				?>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12" id="divmsgbilcarianlengkap" > 
						<div class="x_panel">
							<!-- <h4><?php // echo $_SESSION['bil_dok_carian_lengkap']; ?> rekod carian lengkap telah ditemui.</h2> --><!-- kod asal sebelum diubah pada 20180420 -->
							<h4><?php echo $_SESSION['bil_dok_carian_lengkap']; ?> rekod telah ditemui.</h2><!-- kod selepas diubah pada 20180420 oleh syedfaizal mengikut permintaan BDT -->
						</div>
						<?php  
						// $_SESSION['bil_dok_carian_lengkap'] = -1; // tak tahu kenapa ini ada di sini
						// unset($_SESSION['bil_dok_carian_lengkap']); // tak tahu kenapa ini ada di sini
						?>
					</div>
				</div>
				<?php
			}
			elseif (isset($_SESSION['bil_dok_carian_lengkap']) AND $_SESSION['bil_dok_carian_lengkap'] == 0 AND $_SESSION['bil_dok_carian_lengkap'] != "z" AND $_SESSION['bil_dok_carian_lengkap'] <> "") {
				// fnRunAlert("paparkan mesej hasil carian lengkap");
				?>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12" id="divmsgbilcarianlengkap" > 
						<div class="x_panel">
							<!-- <h4><?php echo $_SESSION['bil_dok_carian_lengkap']; ?> rekod carian lengkap telah ditemui.</h2> --><!-- kod asal sebelum diubah pada 20180420 -->
							<h4><?php echo $_SESSION['bil_dok_carian_lengkap']; ?> rekod telah ditemui.</h2><!-- kod selepas diubah pada 20180420 oleh syedfaizal mengikut permintaan BDT -->
						</div>
						<?php  
						// $_SESSION['bil_dok_carian_lengkap'] = -1; // tak tahu kenapa ini ada di sini
						// unset($_SESSION['bil_dok_carian_lengkap']); // tak tahu kenapa ini ada di sini
						?>
					</div>
				</div>
				<?php
			}
			else {
				?>
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12" id="divmsgbilcarianlengkap" > 
						<div class="x_panel">
							<!-- <h4><?php echo $_SESSION['bil_dok_carian_lengkap']; ?> rekod carian lengkap telah ditemui.</h2> --><!-- kod asal sebelum diubah pada 20180420 -->
							<h2>Tiada hasil carian.&nbsp;<!-- <?php echo $_SESSION['bil_dok_carian_lengkap']; ?> rekod telah ditemui. --></h2><!-- kod selepas diubah pada 20180420 oleh syedfaizal mengikut permintaan BDT -->
						</div>
						<?php  
						// $_SESSION['bil_dok_carian_lengkap'] = -1; // tak tahu kenapa ini ada di sini
						// unset($_SESSION['bil_dok_carian_lengkap']); // tak tahu kenapa ini ada di sini
						?>
					</div>
				</div>
				<?php
			}
		}
		# buffer
		if ((isset($_SESSION['bil_dok_carian_lengkap']) < 0 AND $_SESSION['search_form_opened'] == "advanced") OR (isset($_SESSION['bil_dok_carian_mudah']) < 0 AND $_SESSION['search_form_opened'] == "simple")) {
			?>
            <br>&nbsp;<br>&nbsp;
            <?php
		}
		?>
		<div class="clearfix"></div>
		<?php  
		# paparan senarai rekod hasil carian mudah
		// if (!isset($_SESSION['bil_dok_carian_mudah']) > 0 AND $_SESSION['search_form_opened'] == "simple") {
		if (isset($_SESSION['bil_dok_carian_mudah']) AND $_SESSION['bil_dok_carian_mudah'] >= 0 AND $_SESSION['bil_dok_carian_mudah'] != "z" AND $_SESSION['search_form_opened'] == "simple") {
			# paparkan jadual rekod hasil daripada carian mudah
			?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12" id="divhasilcarianmudah" >
					<div class="x_panel">
						<div class="x_title">
							<h2><?php echo $_SESSION['ss_table_title']; ?> <small><?php echo $_SESSION['ss_table_action']; ?></small></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<p class="text-muted font-13 m-b-30">
								<!-- The Buttons extension for DataTables provides a common set of options, API methods and styling to display buttons on a page that will interact with a DataTable. The core library provides the based framework upon which plug-ins can built. -->
							</p>
							<form id="form-jadual-data" action="<?php echo $actionfilename."?s=sfla"; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
								<div class="title_right">
								</div>
								<table id="myTable" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th width="40">Bil</th>
											<th width="100" hidden>Kod</th>
											<th>Tajuk</th>
											<th width="160">Tindakan</th>
										</tr>
									</thead>


									<tbody id="myTableBody">
										<?php 
						                // fnShowDocTableContent($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name); 
										fnShowDocTableContentForSimpleSearch($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name); 
										?>
									</tbody>
								</table>
					            <div class="col-md-12 text-center">
					              <ul class="pagination pagination-lg pager" id="myPager"></ul>
					            </div>
							</form>
						</div>
					</div>
				</div>
			</div>
            <br>&nbsp;<br>&nbsp;<br>&nbsp;
			<?php
		}
		?>
		<div class="clearfix"></div>
		<?php  
		// untuk ujian sahaja
		// $_SESSION['bil_dok_carian_lengkap'] = 2;
		// $_SESSION['search_form_opened'] = "advanced";
		if (isset($_SESSION['bil_dok_carian_lengkap'])) {
					# code...
			// fnRunAlert("$_SESSION[bil_dok_carian_lengkap] hasil carian lengkap");
		}		
		else {
			// fnRunAlert("Tiada nilai bagi bil hasil carian lengkap.");
		}
		// fnRunAlert("Borang $_SESSION[search_form_opened] dibuka");
		/* paparkan borang kemas kini rekod MULA */
	    # if update button in table is clicked
	    if ($_SESSION['status_buka_borang_kemaskini_dokumen'] != 0) {
			$kod_dok_untuk_dikemaskini = $_SESSION['kod_dok_untuk_dikemaskini'];
			?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<div class="x_panel">
						<div class="x_title">
							<h2><?php echo "Borang Kemas Kini"; ?><small><?php echo "Hasil Carian"; ?></small></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<br />
							<form id="form-kemaskini-data" action="<?php echo $actionfilename."?s=u"; ?>" enctype="multipart/form-data" method="POST" data-parsley-validate class="form-horizontal form-label-left">
								<?php 
								fnClearTerasDokSessionForUpdateForm();
								if (isset($_SESSION['kod_dok_untuk_dikemaskini']) AND $_SESSION['kod_dok_untuk_dikemaskini'] != "") {
									$_SESSION['kod_dok_to_be_updated'] = $_SESSION['kod_dok_untuk_dikemaskini'];
									// fnShowUpdateDocFormContentForSearch($DBServer,$DBUser,$DBPass,$DBName,$table01name,$field01name,$field02name); 
									fnShowUpdateDocFormContentForSearch(); 
								}
								?>
								<div class="form-group"><!-- ini adalah di dalam borang kemaskini -->
									<div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3" align="center">
										<!-- ini adalah di dalam borang kemaskini rekod dokumen -->
										<!-- butang kemaskini tanpa muat naik baharu -->
										<input type="submit" id="btn_simpan_dok_dikemaskini" name="btn_simpan_dok_dikemaskini" class="btn btn-success" title="Kemas Kini Data" value="Kemas Kini">
										<!-- butang kemaskini dengan muat naik baharu -->
										<input type="submit" id="btn_simpan_dok_muatnaik" name="btn_simpan_dok_muatnaik" class="btn btn-warning" title="Kemas Kini dan Muat Naik" value="Kemas Kini dan Muat Naik">
										<input type="submit" id="btn_batal_kemaskini" name="btn_batal_kemaskini" class="btn btn-danger" title="Batal" value="Batal">
										<?php  
										if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
											//fnRunAlert("(1653) kod_dok_to_delete = ".$_SESSION['kod_dok_to_delete']);
											?>
											<a href="delete.php?id=<?php echo $_SESSION['kod_dok_to_delete']; ?>&source=l" title="Hapus Rekod <?php echo $_SESSION['kod_dok_to_delete']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')">Hapus Rekod</a>
											<?php
											$button_delete = "<button type='submit' id='btn_hapus_dokumen' name='btn_hapus_dokumen' class='btn btn-danger' title='Hapuskan Rekod Ini' value='$_SESSION[kod_dok_to_delete]'>Hapus Rekod <i class='fa fa-trash'></i></button>";
										}
										else {
											$button_delete = "";
										}
	                    				// echo $button_delete;
										?>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- <br>&nbsp;<br>&nbsp;<br>&nbsp; -->
			<?php
			fnSearchDocAdvancedRepeat();
	    }
		/* paparkan borang kemas kini rekod TAMAT */
	    # papar perincian rekod untuk lihat sahaja
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
	              <form id="form-kemaskini-data" action="<?php echo $actionfilename."?s=v"; ?>" enctype="multipart/form-data" method="POST" data-parsley-validate class="form-horizontal form-label-left">
	                <?php 
	                fnClearTerasDokSessionForUpdateForm();
	                fnShowViewDocContent($DBServer,$DBUser,$DBPass,$DBName,"dokumen","kod_dok","tajuk_dok"); 
	                ?>
	                <div class="form-group">
	                  <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3" align="center">
	                    <input type="submit" id="btn_papar_borang_kemaskini_dari_perincian_dokumen" name="btn_papar_borang_kemaskini_dari_perincian_dokumen" class="btn btn-success" title="Buka Borang Kemaskini Dokumen" value="Buka Borang Kemaskini" hidden>
	                    <input type="submit" id="btn_tutup_perincian_dokumen" name="btn_tutup_perincian_dokumen" class="btn btn-danger" title="Tutup" value="Tutup">
	                  </div>
	                </div>
	              </form>
	            </div>
	          </div>
	        </div>
	      </div>
            <!-- <br>&nbsp;<br>&nbsp;<br>&nbsp; -->
	      <?php
	      fnSearchDocAdvancedRepeat();
	    }

		# paparan senarai dokumen hasil carian lengkap
		// fnRunAlert("bil_dok_carian_lengkap = ".$_SESSION['bil_dok_carian_lengkap']);
		if (isset($_SESSION['bil_dok_carian_lengkap']) AND $_SESSION['bil_dok_carian_lengkap'] >= 0 AND $_SESSION['bil_dok_carian_lengkap'] != "z" AND $_SESSION['bil_dok_carian_lengkap'] <> "" AND $_SESSION['search_form_opened'] == "advanced") {
			# paparkan jadual rekod hasil daripada carian lengkap
			// fnRunAlert("paparkan hasil carian lengkap");
			?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12" id="divhasilcarianlengkap" >
					<div class="x_panel">
						<div class="x_title">
							<h2><?php echo $_SESSION['as_table_title']; ?> <small><?php echo $_SESSION['as_table_action']; ?></small></h2>
							<div class="clearfix"></div>
						</div>
						<div class="x_content">
							<p class="text-muted font-13 m-b-30">
								<!-- The Buttons extension for DataTables provides a common set of options, API methods and styling to display buttons on a page that will interact with a DataTable. The core library provides the based framework upon which plug-ins can built. -->
							</p>
							<form id="form-jadual-data" action="<?php echo $actionfilename."?s=cfl"; ?>" method="POST" data-parsley-validate class="form-horizontal form-label-left">
								<div class="title_right">
								</div>
								<table id="myTable" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th width="40">Bil</th>
											<th width="100" hidden>Kod</th>
											<th>Tajuk</th>
											<th width="200">Tindakan</th>
										</tr>
									</thead>


									<tbody id="myTableBody">
										<?php 
										fnShowDocTableContentForAdvancedSearchOriginal($DBServer,$DBUser,$DBPass,$DBName); 
										?>
									</tbody>
								</table>
					            <div class="col-md-12 text-center">
					              <ul class="pagination pagination-lg pager" id="myPager"></ul>
					            </div>
							</form>
						</div>
					</div>
				</div>
			</div>
            <br>&nbsp;<br>&nbsp;<br>&nbsp;
			<?php
			// fnClearAdvancedDocSearchResult();

		}
		?>
		<!-- /page content -->
	    <script src="../vendors/jquery/dist/jquery.min.js"></script>
	    <script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
	    <script src="../engine/bootstrap.tablesorter.js"></script>
	    <script>
	    	$(document).ready(function(){
	    		$('#kod_pilihan_carian').on('change', function () {
	    			switch ($(this).val()) {
	    				case '1':
	    				$('#divmsgpilihan').prop('hidden', false);
	    				$('#divsimplesearch').prop('hidden', true);
	    				$('#divadvancedsearch').prop('hidden', true);
	    				$('#divmsgbilcarianmudah').prop('hidden', true);
	    				$('#divmsgbilcarianlengkap').prop('hidden', true);
	    				$('#divhasilcarianmudah').prop('hidden', true);
	    				$('#divhasilcarianlengkap').prop('hidden', true);
	    				<?php 
	    				$_SESSION['search_form_opened'] = "none"; 
						fnClearSimpleDocSearchSessions();
						fnClearAdvancedDocSearchSessions();
						// fnRefreshPgMeta();
	    				?>
	    				// $('.selectpicker').selectpicker('refresh');
	    				break;
	    				case '2':
	    				$('#divmsgpilihan').prop('hidden', true);
	    				$('#divsimplesearch').prop('hidden', false);
	    				$('#divadvancedsearch').prop('hidden', true);
	    				$('#divmsgbilcarianmudah').prop('hidden', false);
	    				$('#divmsgbilcarianlengkap').prop('hidden', true);
	    				$('#divhasilcarianmudah').prop('hidden', false);
	    				$('#divhasilcarianlengkap').prop('hidden', true);
	    				<?php 
	    				$_SESSION['search_form_opened'] = "simple"; 
						fnClearAdvancedDocSearchSessions();
						// include ("searchdoc_simple.php");
						// fnRefreshPgMeta();
	    				?>
	    				// $('.selectpicker').selectpicker('refresh');
	    				break;
	    				case '3':
	    				$('#divmsgpilihan').prop('hidden', true);
	    				$('#divsimplesearch').prop('hidden', true);
	    				$('#divadvancedsearch').prop('hidden', false);
	    				$('#divmsgbilcarianmudah').prop('hidden', true);
	    				$('#divmsgbilcarianlengkap').prop('hidden', false);
	    				$('#divhasilcarianmudah').prop('hidden', true);
	    				$('#divhasilcarianlengkap').prop('hidden', false);
	    				<?php 
	    				$_SESSION['search_form_opened'] = "advanced"; 
						fnClearSimpleDocSearchSessions();
						// include ("searchdoc_advanced.php");
						// fnRefreshPgMeta();
	    				?>
	    				// $('.selectpicker').selectpicker('refresh');
	    				break;
	    			}
	    		}); 
	    	});
			$('#myTableBody').pageMe({pagerSelector:'#myPager',showPrevNext:true,hidePageNumbers:false,perPage:10});
	    </script>
		<?php require "../layouts/lay_adminmainbottom.php"; ?>