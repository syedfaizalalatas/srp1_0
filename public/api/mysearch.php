<?php 
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
    require '../../init.php';

	header("Content-Type: application/json");
    //header("Content-type: application/pdf");
    //header("Content-Disposition: attachment; filename='document.pdf'");

    $doSearch = false;

    $search     = htmlentities($_POST['searchtext']); 
    $tahun      = htmlentities($_POST['tahun']); 
    $sektor     = htmlentities($_POST['sektor']); 
    $kategori   = htmlentities($_POST['kategori']);    
    $teras      = htmlentities($_POST['teras']); 
    $status     = htmlentities($_POST['status']);

    $items = array(
        'searchtext'    => $search,
        'tahun'         => $tahun,
        'kategori'      => $kategori,
        'sektor'        => $sektor,
        'teras'         => $teras,
        'status'        => $status
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
        $myResult = $rec->docSearch_api($items);
        
        $counter = new Search;
        $resPage = $counter->countResult($items);
        
        if (!empty($myResult)) {
            $myItems = array (
                    "result" => "success",
                    "total" => $resPage,
                    "mysearch" => $myResult
                );
        } else {
            $myItems = array (
                    "result" => "failed"
                );
        }
        
            
        echo json_encode($myItems);

        // update katakunci carian
        $katakunci = New Search;
        $myKataKunci = $katakunci->updateKataKunci($search);
    } else {
        $myItems = array (
                    "result" => "failed"
                );

        echo json_encode($myItems);
    }