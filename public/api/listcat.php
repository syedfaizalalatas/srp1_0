<?php 
    header("Content-Type: application/json");

	if ($_SERVER['REQUEST_METHOD'] === 'GET'){

		//$items[] = ['id' => 1001, 'nama' => 'Pekeliling Perkhidmatan', 'tahun' => 2017, 'status' => 'Aktif'];
		$items = array(['bahagian' => 'Pembangunan Aplikasi'],['bahagian' => 'Pembangunan Strategik'],['bahagian' => 'Gunasama ICT']);
		
		if ($items)
		{
			echo json_encode($items); 
			exit;
		}
		else
		{
			echo json_encode(array('error' => 'Tiada rekod.'));
			exit;    
		}
		   
	}
	else
	{
	   echo json_encode(array('error' => 'Invalid Action.'));
	   exit; 
	}
	
	//103.8.160.10
	