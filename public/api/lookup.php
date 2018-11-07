<?php 
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    require '../../init.php';

    header("Content-Type: application/json");

    $rec = new LookupQuery;
    $myKategori = json_decode($rec->showLookupRecords("kategori"), true);
    $mySektor = json_decode($rec->showLookupRecords("sektor"), true); 
    $myStatus = json_decode($rec->showLookupRecords("status"), true); 
    $myTeras = json_decode($rec->showLookupRecords("teras_strategik"), true); 
    
    echo json_encode(
        ["kategori" => $myKategori,
         "sektor" => $mySektor,
         "status" => $myStatus,
         "teras" => $myTeras]);