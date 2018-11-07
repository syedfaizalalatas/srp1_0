<?php 
/**
* Sistem Repositori Pekeliling
* Search
* @ Author			shu4mi
* @ Date			14 Nov 2016
*
**/

class Search
{
	// Declare initial sql query to empty
	private $qSearch  = '';
	private $kod  = '';

	public function __construct($rec = array())
	{
		$this->search 			= (isset($rec['searchtext']) ? stripslashes(strip_tags($rec['searchtext'])) : '');
		$this->search_tahun 	= (isset($rec['tahun']) ? stripslashes(strip_tags($rec['tahun'])) : '');
		$this->search_kategori 	= (isset($rec['kategori']) ? stripslashes(strip_tags($rec['kategori'])) : 1);
		$this->search_sektor 	= (isset($rec['sektor']) ? stripslashes(strip_tags($rec['sektor'])) : 1);
		$this->search_teras 	= (isset($rec['teras']) ? stripslashes(strip_tags($rec['teras'])) : 1);
		$this->search_status 	= (isset($rec['status']) ? stripslashes(strip_tags($rec['status'])) : 1);
		$this->start_from 		= (isset($rec['start_from']) ? stripslashes(strip_tags($rec['start_from'])) : 0);
		$this->limit_page 		= (isset($rec['pageLimit']) ? stripslashes(strip_tags($rec['pageLimit'])) : 5);
		$this->kod 				= (isset($rec['kod']) ? stripslashes(strip_tags($rec['kod'])) : '');

		$this->sql_param = array();		
		
		// For searching
		if ($this->kod == "cari")
		{
			$this->qSearch = "SELECT * FROM dokumen A 
								INNER JOIN kategori B ON A.kod_kat = B.kod_kat 
								INNER JOIN sektor C	ON A.kod_sektor = C.kod_sektor 
								INNER JOIN status D	ON A.kod_status = D.kod_status";

			if ($this->search_teras != 1)
			{
				$this->qSearch .= " INNER JOIN teras_dok E ON E.kod_dok = A.kod_dok
									INNER JOIN teras_strategik F ON E.kod_teras = F.kod_teras 
									AND E.checked_value = 1";
			}
		
		}
		else // For page navigation
		{
			$this->qSearch = "SELECT COUNT(A.kod_dok) FROM dokumen A 
								INNER JOIN kategori B ON A.kod_kat = B.kod_kat 
								INNER JOIN sektor C	ON A.kod_sektor = C.kod_sektor 
								INNER JOIN status D	ON A.kod_status = D.kod_status";

			if ($this->search_teras != 1)
			{
				$this->qSearch .= " INNER JOIN teras_dok E ON E.kod_dok = A.kod_dok
									INNER JOIN teras_strategik F ON E.kod_teras = F.kod_teras 
									AND E.checked_value = 1";
			}
		}
		
		// If input search not null
		if ($this->search != '')
		{
			// Do full text search
			$this->qSearch .= " WHERE 
								(MATCH (A.tajuk_dok, A.des_dok, A.tag_dokumen) AGAINST (:searchVal1 IN NATURAL LANGUAGE MODE)
								OR MATCH (B.nama_kat) AGAINST (:searchVal2 IN NATURAL LANGUAGE MODE))";
			
			// Push search variable into array
			$this->sql_param += [':searchVal1' => $this->search];
			$this->sql_param += [':searchVal2' => $this->search];

		}
		// If tahun input search not null
		if ($this->search_tahun != '')
		{
			$this->qSearch .= " AND A.tahun_dok = :tahun";
			$this->sql_param += [':tahun' => $this->search_tahun];
		}
		// If sektor drop down selected
		if ($this->search_sektor != 1)
		{
			$this->qSearch .= " AND A.kod_sektor = :sektor";
			$this->sql_param += [':sektor' => $this->search_sektor];
		}
		// If kategori drop down selected
		if ($this->search_kategori != 1)
		{
			$this->qSearch .= " AND A.kod_kat = :kategori";
			$this->sql_param += [':kategori' => $this->search_kategori];
		}
		// If teras drop down selected
		if ($this->search_teras != 1)
		{
			$this->qSearch .= " AND F.kod_teras = :teras";
			$this->sql_param += [':teras' => $this->search_teras];
		}
		// If status drop down selected
		if ($this->search_status != 1)
		{
			$this->qSearch .= " AND D.kod_status = :status";
			$this->sql_param += [':status' => $this->search_status];
		}		
	}

	/**
	 * Search document
	 */
	function docSearch($params)
	{
		$params += ['kod' => 'cari'];
		$this->__construct($params);

		$this->qSearch .= " LIMIT :start_from, :limit_page";
        $this->sql_param += [':start_from' => $this->start_from];
        $this->sql_param += [':limit_page' => $this->limit_page];

		try 
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($this->qSearch);
			$stmt->execute($this->sql_param);		
			$result = $stmt->fetchAll();

			$resArray = array();
			$resArray2 = array();

			foreach ($result as $key => $row) 
			{ 	
				$resArray[] = array(
						"kod_dok"			=> $row['kod_dok'],
						"tajuk_dok" 		=> $row['tajuk_dok'],
						"tahun_dok" 		=> $row['tahun_dok'],
						"tarikh_wujud" 		=> $row['tarikh_wujud'],
						"nama_dok_disimpan" => $row['nama_dok_disimpan'],
						"des_dok" 			=> $row['des_dok'],
						"bil_dok" 			=> $row['bil_dok'],
						"kod_kat" 			=> $row['kod_kat'],
						"nama_kat" 			=> $row['nama_kat'],
						"tarikh_mansuh" 	=> $row['tarikh_mansuh'],
						"tarikh_pinda" 		=> $row['tarikh_pinda'],
						"tajuk_dok_asal" 	=> $row['tajuk_dok_asal'],
						"tajuk_dok_baharu" 	=> $row['tajuk_dok_baharu'],
						"tarikh_serah" 		=> $row['tarikh_serah'],
						"kod_jab_asal" 		=> $row['kod_jab_asal'],
						"kod_jab_baharu" 	=> $row['kod_jab_baharu'],
						"kod_status"		=> $row['kod_status']
						);		

				foreach ($resArray as $key1 => $row) 
				{
					$sql = "SELECT * FROM dokumen D
								INNER JOIN teras_dok TD ON D.kod_dok = TD.kod_dok
								INNER JOIN teras_strategik TS ON TS.kod_teras = TD.kod_teras
									AND TD.checked_value = 1
							WHERE D.kod_dok =:kod";

					$sql_query = array(':kod' => $row['kod_dok']);

					$_pdo2 = New Dbase;
					$stmt2 = $_pdo2->getDb()->prepare($sql);
					$stmt2->execute($sql_query);		
					$result2 = $stmt2->fetchAll();

					foreach ($result2 as $key2 => $row2) 
					{ 	
						$resArray2[$key2] = array(
									"kod_teras"  => $row2['kod_teras'],
									"nama_teras" => $row2['nama_teras']);
					}
				}			

				$resArray[$key]['teras'] = $resArray2;
			}

			return $resArray; 

            $this->_pdo = null;
		}
		catch(PDOException $e) 
		{
			die("Failed to run query"); 
			//die($e->getMessage());
		}
	}
	
	/**
	 * Search document (API)
	 */
	function docSearch_api($params)
	{
		$params += ['kod' => 'cari'];
		$this->__construct($params);

		//$this->qSearch .= " LIMIT :start_from, :limit_page";
        //$this->sql_param += [':start_from' => $this->start_from];
        //$this->sql_param += [':limit_page' => $this->limit_page];

		try 
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($this->qSearch);
			$stmt->execute($this->sql_param);		
			$result = $stmt->fetchAll();

			$resArray = array();
			$resArray2 = array();

			foreach ($result as $key => $row) 
			{ 	
				$resArray[] = array(
						"kod_dok"			=> $row['kod_dok'],
						"tajuk_dok" 		=> $row['tajuk_dok'],
						"tahun_dok" 		=> $row['tahun_dok'],
						"tarikh_wujud" 		=> $row['tarikh_wujud'],
						"nama_dok_disimpan" => $row['nama_dok_disimpan'],
						"des_dok" 			=> $row['des_dok'],
						"bil_dok" 			=> $row['bil_dok'],
						"kod_kat" 			=> $row['kod_kat'],
						"nama_kat" 			=> $row['nama_kat'],
						"tarikh_mansuh" 	=> $row['tarikh_mansuh'],
						"tarikh_pinda" 		=> $row['tarikh_pinda'],
						"tajuk_dok_asal" 	=> $row['tajuk_dok_asal'],
						"tajuk_dok_baharu" 	=> $row['tajuk_dok_baharu'],
						"tarikh_serah" 		=> $row['tarikh_serah'],
						"kod_jab_asal" 		=> $row['kod_jab_asal'],
						"kod_jab_baharu" 	=> $row['kod_jab_baharu'],
						"kod_status"		=> $row['kod_status']
						);		

				foreach ($resArray as $key1 => $row1) 
				{
					$sql = "SELECT * FROM dokumen D
								INNER JOIN teras_dok TD ON D.kod_dok = TD.kod_dok
								INNER JOIN teras_strategik TS ON TS.kod_teras = TD.kod_teras
									AND TD.checked_value = 1
							WHERE D.kod_dok =:kod";

					$sql_query = array(':kod' => $row1['kod_dok']);

					$_pdo2 = New Dbase;
					$stmt2 = $_pdo2->getDb()->prepare($sql);
					$stmt2->execute($sql_query);		
					$result2 = $stmt2->fetchAll();

					foreach ($result2 as $key2 => $row2) 
					{ 	
						$resArray2[$key2] = array(
									"kod_teras"  => $row2['kod_teras'],
									"nama_teras" => $row2['nama_teras']);
					}
				}			

				$resArray[$key]['teras'] = $resArray2;
			}

			return $resArray; 

            $this->_pdo = null;
		}
		catch(PDOException $e) 
		{
			die("Failed to run query"); 
			//die($e->getMessage());
		}
	}
	
	/**
	 * Page count result (API)
	 */
	public function countResult($params)
	{
		$params += ['kod' => 'nav'];
		$this->__construct($params);

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($this->qSearch);
			$stmt->execute($this->sql_param); 
			$result = $stmt->fetch(PDO::FETCH_NUM);

			$total_records = $result[0];

			return $total_records;

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
		}
	}

	
	/**
	 * Page navigation
	 */
	public function pageNav($params)
	{
		$params += ['kod' => 'nav'];
		$this->__construct($params);

		try
		{
			$starttime = microtime(true);

			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($this->qSearch);
			$stmt->execute($this->sql_param); 
			$result = $stmt->fetch(PDO::FETCH_NUM); // return total rows as number

			$endtime = microtime(true);
			$duration = $endtime - $starttime;

			$total_records = $result[0];
			$total_pages = ceil($total_records / $this->limit_page); 
			$res = array($total_records, $total_pages, $duration);

			return $res;

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
			//die($e->getMessage());
		}
	}

	/**
	 * Update Kata Kunci Pilihan
	 */
	public function updateKataKunci($param)
	{
		$this->katakunci = (isset($param) ? stripslashes(strip_tags($param)) : '');

		if ($this->katakunci != '')
		{
			$this->existKataKunci = $this->checkKataKunci($this->katakunci);

			if ($this->existKataKunci[0]['stat'])
			{
				$sql_query = 'UPDATE katakunci SET bil = bil + 1 WHERE id = :id';
				$sql_param = array(':id' => $this->existKataKunci[0]['id']);
			}
			else
			{
				$sql_query = 'INSERT INTO katakunci(kataKunci, bil) VALUES(:katakunci, :bil)';
				$sql_param = array(':katakunci' => $this->katakunci, ':bil' => 0);
			}

			try
			{
				$_pdo = New Dbase;
				$stmt = $_pdo->getDb()->prepare($sql_query);
				$stmt->execute($sql_param);

				$this->_pdo = null;
			}
			catch(PDOException $e)
			{
				die("Failed to run query");
				//die($e->getMessage());
			}			
		}
	}

	/**
	 * Check table katakunci
	 */
	public function checkKataKunci($param)
	{
		$this->valKataKunci = $param;

		$sql = "SELECT * FROM katakunci 
					WHERE kataKunci LIKE :val1
						AND katakunci LIKE :val2 
						AND katakunci LIKE :val3";

		$sql_param = array( 
            ':val1' => '%'.$this->valKataKunci,
        	':val2' => $this->valKataKunci.'%',
        	':val3'	=> '%'.$this->valKataKunci.'%'
        );

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute($sql_param);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$res = array();

			if($result)
			{	
        		$res[] = array("id" => $result[0]['id'],"stat" => true);
        		return $res;
			}
			else
			{
				return false;
			}

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
			//die($e->getMessage());
		}
	}

	/**
	 * Select top 5 from table katakunci
	 */
	public function searchKataKunci()
	{
		$sql = "SELECT * FROM katakunci ORDER BY bil DESC LIMIT 5";

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll();

			return $result;

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
			//die($e->getMessage());
		}
	}
	
	/**
	 * Page navigation
	 */
	public function pageNav1($params, $page)
	{
		$params += ['kod' => 'nav'];
		$this->__construct($params);
		$adjacents = 1;
		$targetpage = "search.php";

		try {
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($this->qSearch);
			$stmt->execute($this->sql_param); 
			$result = $stmt->fetch(PDO::FETCH_NUM);

			$total_records = $result[0];

			if($page) 
			    $this->start_from = ($page - 1) * $this->limit_page;
			else
			    $this->start_from = 0;

			/* Setup page vars for display. */
			if ($page == 0) $page = 1;
			
			$prev = $page - 1; 
			$next = $page + 1; 
			$lastpage = ceil($total_records/$this->limit_page);
			$lpm1 = $lastpage - 1;

			$pagination = "";
			if($lastpage > 1)
			{   
			    $pagination .= "<ul class=\"pagination\">";
			    //previous button
			    if ($page > 1) 
			        $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$prev\">&laquo;</a></li>";
			    else
			        $pagination.= "<li class=\"disabled\"><a href=\"\">&laquo;</a></li>"; 

			    //pages 
			    if ($lastpage < 7 + ($adjacents * 2))    //not enough pages to bother breaking it up
			    {   
			        for ($counter = 1; $counter <= $lastpage; $counter++)
			        {
			            if ($counter == $page)
			            	$pagination.= "<li class=\"active\"><a href=\"#\"><span>$counter</span></a></li>";
			            else
			                $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$counter\">$counter</a></li>";                 
			        }
			    }
			    elseif($lastpage > 7 + ($adjacents * 2)) //enough pages to hide some
			    {
			        //close to beginning; only hide later pages
			        if($page < 1 + ($adjacents * 2))     
			        {
			            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
			            {
			                if ($counter == $page)
			                	$pagination.= "<li class=\"active\"><a href=\"#\"><span>$counter</span></a></li>";
			                else
			                    $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$counter\">$counter</a></li>";                 
			            }
			            $pagination.= "<li><a href=\"\">...</a></li>";
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$lpm1\">$lpm1</a></li>";
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$lastpage\">$lastpage</a></li>";       
			        }
			        //in middle; hide some front and some back
			        elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			        {
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=1\">1</a></li>";
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=2\">2</a></li>";
			            $pagination.= "<li><a href=\"\">...</a></li>";
			            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
			            {
			                if ($counter == $page)
			                	$pagination.= "<li class=\"active\"><a href=\"#\"><span>$counter</span></a></li>";
			                else
			                    $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$counter\">$counter</a></li>";                 
			            }
			            $pagination.= "<li><a href=\"\">...</a></li>";
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$lpm1\">$lpm1</a></li>";
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$lastpage\">$lastpage</a></li>";       
			        }
			        //close to end; only hide early pages
			        else
			        {
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=1\">1</a></li>";
			            $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=2\">2</a></li>";
			            $pagination.= "<li><a href=\"\">...</a></li>";
			            for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
			            {
			                if ($counter == $page)
			                	$pagination.= "<li class=\"active\"><a href=\"#\"><span>$counter</span></a></li>";
			                else
			                    $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$counter\">$counter</a></li>";                 
			            }
			        }
			    }

			    //next button
			    if ($page < $counter - 1) 
			        $pagination.= "<li><a href=\"$targetpage?q=".urlencode($this->search)."&kat=".urlencode($this->search_kategori)."&thn=".urlencode($this->search_tahun)."&trs=".urlencode($this->search_teras)."&sktr=".urlencode($this->search_sektor)."&sts=".urlencode($this->search_status)."&page=$next\">&raquo;</a></li>";
			    else
			        $pagination.= "<li class=\"disabled\"><a href=\"\">&raquo;</a></li>";
			    $pagination.= "</ul>\n";     
			}

			//return $pagination;
			$res = array($total_records, $pagination);

			return $res;

			$this->_pdo = null;

		} catch (PDOException $e) {
			die("Failed to run query");
		}
	}

}