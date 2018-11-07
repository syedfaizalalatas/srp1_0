<?php 
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
    require '../../init.php';

    header("Content-Type: application/json");

    $rec = new LookupQuery;
    $myTeras = json_decode($rec->showLookupRecords("teras_strategik"), true); 
	
	$items = array(
    		"teras" => $myTeras
    	);

    echo json_encode($items);