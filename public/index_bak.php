<?php 
require '../init.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sistem Repositori Pekeliling MAMPU</title>

	<!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Theme and Custom CSS -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="wrapper">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="../repositori/views/external/login.php"><i class="fa fa-user"></i> Pentadbir sistem</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div>
        <div class="container">
            <div class="col-sm-6 col-md-7">
                <div class="row">
                    <div class="logo">                
                        <a href="index.php"><img src="img/logo.png" class="img-responsive center-block" data-toggle="tooltip" data-placement="bottom" title="SRP v1"></a>
                    </div>
                    <div class="searchForm"> 
                        <form name="form_search" id="form_search" method="post" action="search.php">                            
                            <div class="searchDetailBox">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="input-group">                                    
                                                    <input type="text" name="searchtext" id="searchtext" class="inputDetail input-sm form-control" placeholder="Kata kunci carian...">
                                                    <span class="input-group-btn"><button type="submit" id="btnsearch" class="btn btnSearch btn-primary" type="button" data-toggle="tooltip" data-placement="bottom" title="Cari dokumen"><i class="fa fa-search"></i></button></span>                               
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <label for="tahun">Tahun</label>
                                                <input type="text" class="form-control inputDetail input-sm" id="tahun" name="tahun" placeholder="Tahun">
                                            </div>
                                            <div class="col-sm-8">
                                                <label for="sektor">Sektor</label>
                                                <?php
                                                    $rec = new LookupQuery;
                                                    $mySektor = json_decode($rec->showLookupRecords("sektor"), true); 
                                                ?>
                                                <select class="form-control inputDetail input-sm" id="sektor" name="sektor">
                                                    <?php foreach ($mySektor as $key => $val): ?>
                                                        <option value="<?php echo $val['kod_sektor']; ?>"><?php echo $val['nama_sektor']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="kategori">Kategori</label>
                                                <?php
                                                    $rec = new LookupQuery;
                                                    $myKategori = json_decode($rec->showLookupRecords("kategori"), true); 
                                                ?>                                 
                                                <select class="form-control inputDetail input-sm" id="kategori" name="kategori">
                                                    <?php foreach ($myKategori as $key => $val): ?>
                                                        <option value="<?php echo $val['kod_kat']; ?>"><?php echo $val['nama_kat']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>                      
                                        </div>
                                    </div>   

                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="teras">Teras Strategik</label>
                                                <?php
                                                    $rec = new LookupQuery;
                                                    $myTeras = json_decode($rec->showLookupRecords("teras_strategik"), true); 
                                                ?>
                                                <select class="form-control inputDetail input-sm" id="teras" name="teras">
                                                    <?php foreach ($myTeras as $key => $val): ?>
                                                        <option value="<?php echo $val['kod_teras']; ?>"><?php echo $val['nama_teras']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="status">Status Dokumen</label>
                                                <?php
                                                    $rec = new LookupQuery;
                                                    $myStatus = json_decode($rec->showLookupRecords("status"), true); 
                                                ?>
                                                <select class="form-control inputDetail input-sm" id="status" name="status">
                                                    <?php foreach ($myStatus as $key => $val): ?>
                                                        <option value="<?php echo $val['kod_status']; ?>"><?php echo $val['nama_status']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>                                 
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row" style="margin-top: 15px;;">
                    <div class="info-menu">                                              
                        <div class="col-md-7 panel-group" id="accordion" style="padding-left: 0;padding-right: 0;">
                            <div class="panel panel-default">
                                <?php
                                    $sektor = new LookupQuery;
                                    $bilSektor = json_decode($sektor->countRecords("sektor", "kod_sektor"), true);
                                ?>
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
                                    <div class="panel-heading">
                                        <span class="glyphicon glyphicon-equalizer">
                                        </span>Sektor<span class="label label-info pull-right"><?php echo $bilSektor[0]['bil']; ?></span>
                                    </div>
                                </a>
                                <div id="collapse1" class="panel-collapse collapse">
                                    <div class="panel-body">                                    
                                        <table class="table">
                                            <?php for ($x=1; $x<=$bilSektor[0]['bil']; $x++) { ?>
                                            <tr>
                                                <td>
                                                    <a href="">
                                                    <?php 
                                                        echo '<a href="search.php?qstr=&kat=1&thn=&trs=1&sktr='.urlencode($bilSektor[$x]['kod']).'&sts=1">'.$bilSektor[$x]['nama'].'</a>';

                                                        $total = new LookupQuery;
                                                        $no = $total->countNo("sektor", $bilSektor[$x]['kod']);
                                                    ?>
                                                    <span class="label label-info pull-right"><?php echo $no[0]; ?></span>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>                    
                             <div class="panel panel-default">
                                <?php
                                    $kat = new LookupQuery;
                                    $bilKat = json_decode($kat->countRecords("kategori", "kod_kat"), true);
                                ?>
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
                                    <div class="panel-heading">
                                        <span class="glyphicon glyphicon-level-up">
                                        </span>Kategori<span class="label label-info pull-right"><?php echo $bilKat[0]['bil']; ?></span>
                                    </div>
                                </a>
                                <div id="collapse2" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <table class="table">
                                            <?php for ($x=1; $x<=$bilKat[0]['bil']; $x++) { ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                        echo '<a href="search.php?qstr=&kat='.urlencode($bilKat[$x]['kod']).'&thn=&trs=1&sktr=1&sts=1">'.$bilKat[$x]['nama'].'</a>';

                                                        $total = new LookupQuery;
                                                        $no = $total->countNo("kategori", $bilKat[$x]['kod']);
                                                    ?>
                                                    <span class="label label-info pull-right"><?php echo $no[0]; ?></span>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <?php
                                    $teras = new LookupQuery;
                                    $bilTeras = json_decode($teras->countRecords("teras_strategik", "kod_teras"), true);
                                ?>
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">
                                    <div class="panel-heading">
                                        <span class="glyphicon glyphicon-copy">
                                        </span>Teras Strategik<span class="label label-info pull-right"><?php echo $bilTeras[0]['bil']; ?></span>
                                    </div>
                                </a>
                                <div id="collapse3" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <table class="table">
                                            <?php for ($x=1; $x<=$bilTeras[0]['bil']; $x++) { ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                        echo '<a href="search.php?qstr=&kat=1&thn=&trs='.urlencode($bilTeras[$x]['kod']).'&sktr=1&sts=1">'.$bilTeras[$x]['nama'].'</a>';

                                                        $total = new LookupQuery;
                                                        $no = $total->countNo("teras_strategik", $bilTeras[$x]['kod']);
                                                    ?>
                                                    <span class="label label-info pull-right"><?php echo $no[0]; ?></span>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <?php
                                    $status = new LookupQuery;
                                    $bilStatus = json_decode($status->countRecords("status", "kod_status"), true);
                                ?>
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
                                    <div class="panel-heading">
                                        <span class="glyphicon glyphicon-file">
                                        </span>Status Dokumen<span class="label label-info pull-right"><?php echo $bilStatus[0]['bil']; ?></span>
                                    </div>
                                </a>
                                <div id="collapse4" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <table class="table">
                                            <?php for ($x=1; $x<=$bilStatus[0]['bil']; $x++) { ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                        echo '<a href="search.php?qstr=&kat=1&thn=&trs=1&sktr=1&sts='.urlencode($bilStatus[$x]['kod']).'">'.$bilStatus[$x]['nama'].'</a>';

                                                        $total = new LookupQuery;
                                                        $no = $total->countNo("status", $bilStatus[$x]['kod']);
                                                    ?>
                                                    <span class="label label-info pull-right"><?php echo $no[0]; ?></span>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>                  
                        </div>

                        <div class="col-md-5">
                            <h4 style="margin-top: 2px;">Kata kunci pilihan...</h4>
                            <hr class="new-style">
                            <?php
                                $katakunci = New Search;
                                $myKataKunci = $katakunci->searchKataKunci();

                                echo '<p class="katakunci">';
                                foreach ($myKataKunci as $key => $val) 
                                {   
                                    echo '<a href="search.php?qstr='.$val['kataKunci'].'&kat=1&thn=&trs=1&sktr=1&sts=1">'.$val['kataKunci'].'</a>';                                  
                                }
                                echo '</p>';
                            ?>
                        </div>
                    </div>

                </div>                
            </div>
            <div class="col-sm-6 col-md-5">
                <div class="infoBox">
                    <p class="lead text-center">Repositori Pekeliling</p>
                    <p class="infoText">
                         Mewujudkan repositori pekeliling ini adalah selaras dengan keputusan Mesyuarat Panel Merakyatkan Perkhidmatan Awam (MPA) Bil. 1 tahun 2016 yang dipengerusikan oleh Ketua Setiausaha Negara (KSN) pada 30 Mac 2016 mengenai rasionalisasi pekeliling MAMPU yang meliputi keperluan dan tindakan yang perlu dilaksanakan agar penyediaan pekeliling yang bakal dikeluarkan oleh MAMPU menjadi lebih jelas, seragam, teratur, relevan dan terkawal.
					</p>
					<p class="infoText">
						Rasionalisasi pekeliling MAMPU ini dilaksanakan melalui lima peringkat rantaian nilai, iaitu peringkat semakan semula, penyediaan semula, percetakan, pengedaran serta promosi, repositori, pemantauan dan penambahbaikan. Pekeliling yang telah dimuktamadkan dan mendapat kelulusan untuk edaran perlu didaftarkan oleh Bahagian Dasar Transformasi (BDT) mengikut pengelasan dan kategori tertentu dan turut dimuat naik ke dalam satu repositori maklumat dalam bentuk aplikasi bagi mempercepatkan capaian oleh para pelanggan MAMPU. 
					</p>
					<p class="infoText">
					Oleh itu, Repositori Pekeliling ini dibangunkan supaya dapat menyediakan penawaran perkhidmatan untuk para pelanggan dan juga warga MAMPU supaya dapat mencapai maklumat berhubung pekeliling, surat pekeliling, surat arahan dan perkara-perkaitan dengan lebih cepat dan tepat. Maklumat untuk Repositori Pekeliling ini akan dikemaskini oleh Bahagian Dasar Transformasi, MAMPU . 
                    </p>
                </div>
            </div>



                    
        </div>

        <footer class="footer">
            <p class="footerText">Hakcipta terpelihara <a href="http://www.mampu.gov.my/" target="_blank">MAMPU</a> &copy;2016</p>
        </footer>
    </div>
        

    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>

    <!-- Custom JavaScript -->
    <script src="js/custom.js"></script>
</body>
</html>