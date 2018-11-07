<?php 
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
    require '../../init.php';

    header("Content-Type: application/json");

    $rec = new LookupQuery;
    $myStatus = json_decode($rec->showLookupRecords("status"), true); 
	
	$items = array(
    		"status" => $myStatus
    	);

    echo json_encode($items);