<?php 
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
    require '../../init.php';

    header("Content-Type: application/json");

    $rec = new LookupQuery;
    $mySektor = json_decode($rec->showLookupRecords("sektor"), true); 
	
	$items = array(
    		"sektor" => $mySektor
    	);

    echo json_encode($items);
                                                    