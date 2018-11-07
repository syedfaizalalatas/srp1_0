<?php 
	//error_reporting(E_ALL);
	//ini_set('display_errors', 'On');
    require '../init.php';

    $pageLimit = 5;
    $doSearch = false;

    $page       = htmlentities(isset($_GET['page']) ? $_GET['page'] : 1); 
    $search     = htmlentities(isset($_GET['q']) ? $_GET['q'] : $_POST['searchtext']); 
    $tahun      = htmlentities(isset($_GET['thn']) ? $_GET['thn'] : $_POST['tahun']); 
    $sektor     = htmlentities(isset($_GET['sktr']) ? $_GET['sktr'] : $_POST['sektor']); 
    $kategori   = htmlentities(isset($_GET['kat']) ? $_GET['kat'] : $_POST['kategori']);    
    $teras      = htmlentities(isset($_GET['trs']) ? $_GET['trs'] : $_POST['teras']); 
    $status     = htmlentities(isset($_GET['sts']) ? $_GET['sts'] : $_POST['status']);

    $start_from = ($page - 1) * $pageLimit;

    $items = array(
        'searchtext'    => $search,
        'tahun'         => $tahun,
        'kategori'      => $kategori,
        'sektor'        => $sektor,
        'teras'         => $teras,
        'status'        => $status,
        'start_from'    => $start_from,
        'pageLimit'     => $pageLimit,
    );

    if ($search == '' && $tahun == '')
    {
        if($sektor == 1 && $kategori == 1 && $teras == 1 && $status == 1)
        {
            $doSearch = false;
        }
        else
        {
            $doSearch = true; 
        }
    }
    else
    {
       $doSearch = true; 
    }

    if ($doSearch)
    {
        $rec = new Search;
        $myResult = $rec->docSearch($items);

        $paging = new Search;
        $resPage = $paging->pageNav1($items, $page);

        $katakunci = New Search;
        $myKataKunci = $katakunci->updateKataKunci($search);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sistem Repositori Pekeliling MAMPU : Carian</title>

	<!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Theme and Custom CSS
    <link href="css/creative.css" rel="stylesheet"> -->
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="wrapper-search">
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="../repositori/views/external/login.php"><i class="fa fa-user"></i> Pentadbir sistem</a></li>
                    <!--<li><a href="../latihan/views/external/login.php"><i class="fa fa-user"></i> Latihan</a></li>-->
                </ul>
            </div>
        </div>
    </nav>
    <div>
        <div class="container">            
            <div class="col-md-8">
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
                <div class="row">
                    <div class="result-area" id="result-area">
                        <p style="margin-bottom: 15px;">Hasil carian: <?php echo ((!$doSearch) ? 'Tiada rekod untuk dipaparkan' : $resPage[0].' rekod ditemui') ; ?></p>
                        <?php
                            if($doSearch)
                            {
                                foreach ($myResult as $key => $val)
                                {                    
									$max_size = 250;
									if (strlen(strip_tags($val['des_dok'])) > $max) {
										//$des_dok = substr(strip_tags($val['des_dok']), 0, $max_size).'.....';
										$des_dok = substr(strip_tags($val['des_dok']), 0, strrpos(substr(strip_tags($val['des_dok']), 0, $max_size), ' ')).' ....';
									} else {
										$des_dok = $val['des_dok'];
									}
									
									if ($val['bil_dok'] == 0) {
                                        $bil_dok = '';
                                    } else {
                                        $bil_dok = 'Bil '.$val['bil_dok'];
                                    }
									
                                    $date_wujud = DateTime::createFromFormat('Y-m-d', $val['tarikh_wujud']);
                                    $date_mansuh = DateTime::createFromFormat('Y-m-d', $val['tarikh_mansuh']);
                                    $date_pinda = DateTime::createFromFormat('Y-m-d', $val['tarikh_pinda']);
                                    echo '<p class="result-title"><a target="_blank" href="../repositori/views/papers/'.$val['nama_dok_disimpan'].'">'.$val['tajuk_dok'].'</a></p>';
                                    echo '<p class="result-title-2">Kategori : <a href="search.php?q=&kat='.urlencode($val['kod_kat']).'&thn=&trs=1&sktr=1&sts=1">'.$val['nama_kat'].'</a></p>';
                                    echo '<p class="result-title-2">Teras : ';
                                    foreach ($val['teras'] as $key2 => $row) 
                                    {
                                        echo '<a href="search.php?q=&kat=1&thn=&trs='.urlencode($row['kod_teras']).'&sktr=1&sts=1">'.$row['nama_teras'].'</a>';
                                    }

                                    if ($val['kod_status'] == 2) { 
                                        echo '<p class="result-title-2">Status : Masih Berkuat Kuasa'; 
                                    } elseif ($val['kod_status'] == 3) {
                                        echo '<p class="result-title-2">Status : Telah Dimansuhkan pada '.$date_mansuh->format('d-m-Y');  
                                    } elseif ($val['kod_status'] == 4) {
                                        $res = new LookupQuery;
                                        $myJab = json_decode($res->showFilterJabatan($val['kod_dok']), true); 

                                        $date_serah = DateTime::createFromFormat('Y-m-d', $myJab[0]['tarikh_serah']);
                                        echo '<p class="result-title-2">Status : Telah Diserah kepada '.$myJab[0]['nama_jab'].' pada '.$date_serah->format('d-m-Y');  
                                    } elseif ($val['kod_status'] == 5) {
                                        echo '<p class="result-title-2">Status : Telah Dipinda pada '.$date_pinda->format('d-m-Y'); 
                                    }
                                    echo '</p>';
                                    echo '<div class="result-desc">'.$des_dok; //.$val['des_dok'];
                                    //echo '<p class="result-info">Dokumen Bil '.$val['bil_dok'] .' Tahun '.$val['tahun_dok'].' berkuatkuasa '.$date_wujud->format('d-m-Y').'</div>';
									echo '<p class="result-info">Dokumen '.$bil_dok.' Tahun '.$val['tahun_dok'].' berkuatkuasa '.$date_wujud->format('d-m-Y').'</div>';
                                    
                                }
                            }                          
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                            <?php
                                if($doSearch) {

                                    echo $resPage[1];
                                }
                            ?>
                            </div>
                        </div>     
                    </div>
                </div>
            </div>

            <div class="col-md-4 info-menu info-search">                                    
                <div class="panel-group" id="accordion">
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
                                            <?php 
                                                echo '<a href="search.php?q=&kat=1&thn=&trs=1&sktr='.urlencode($bilSektor[$x]['kod']).'&sts=1">'.$bilSektor[$x]['nama'].'</a>';

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
                                                echo '<a href="search.php?q=&kat='.urlencode($bilKat[$x]['kod']).'&thn=&trs=1&sktr=1&sts=1">'.$bilKat[$x]['nama'].'</a>';

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
                                                echo '<a href="search.php?q=&kat=1&thn=&trs='.urlencode($bilTeras[$x]['kod']).'&sktr=1&sts=1">'.$bilTeras[$x]['nama'].'</a>';

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
                                                echo '<a href="search.php?q=&kat=1&thn=&trs=1&sktr=1&sts='.urlencode($bilStatus[$x]['kod']).'">'.$bilStatus[$x]['nama'].'</a>';

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
                <div style="padding-left: 5px;">
                    <br/><h4 style="margin-top: 2px;">Kata kunci pilihan...</h4>
                    <hr class="new-style">
                    <?php
                        $keyword = New Search;
                        $myKeyword = $keyword->searchKataKunci();

                        echo '<p class="katakunci">';
                        foreach ($myKeyword as $key => $val) 
                        {                                    
                            echo '<a href="search.php?q='.$val['kataKunci'].'&kat=1&thn=&trs=1&sktr=1&sts=1">'.$val['kataKunci'].'</a>';                                  
                        }
                        echo '</p>';
                    ?>
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