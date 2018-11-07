<?php 
	
/**
* Sistem Repositori Pekeliling
* LookupQuery
* @ Author			shu4mi
* @ Date			14 Nov 2016
*
**/

class LookupQuery
{
	/**
	 * Show lookup table records
	 */
	public function apiLookupRecords($tableName,$item1,$item2)
	{
		$this->lookupTable = $tableName;
		$this->item1 = $item1;
		$this->item2 = $item2;
		$sql = "SELECT $this->item1, $this->item2 FROM $this->lookupTable WHERE papar_data IS NULL OR papar_data = 1";

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			//return json_encode($result);
			return $result;
			//$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
			//die($e->getMessage());
		}
	}
	
	/**
	 * Show lookup table records
	 */
	public function showLookupRecords($tableName)
	{
		$this->lookupTable = $tableName;
		$sql = "SELECT * FROM $this->lookupTable WHERE papar_data IS NULL OR papar_data = 1";

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return json_encode($result);
			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
			//die($e->getMessage());
		}
	}

	/**
	 * Select all and Count records (category) from lookup table
	 */
	public function countRecords($tableName, $code)
	{
		$this->lookupTable = $tableName;
		$this->lookupCode = $code;

		$sql = "SELECT mytable.countno, $this->lookupTable.* FROM  $this->lookupTable,
					(SELECT COUNT(*) AS countno FROM $this->lookupTable
						WHERE $this->lookupCode <> 1 AND papar_data IS NULL OR papar_data = 1) AS mytable
							WHERE $this->lookupCode <> 1
								AND papar_data IS NULL OR papar_data = 1";

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute(); 

			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$res[] = array("bil" => $result[0]['countno']);
			foreach ($result as $key=>$row) 
			{ 	
				if ($this->lookupTable == 'sektor')
				{
					$res[] = array("kod" => $row['kod_sektor'], "nama" => $row['nama_sektor']);
				}
				else if ($this->lookupTable == 'kategori')
				{
					$res[] = array("kod" => $row['kod_kat'], "nama" => $row['nama_kat']);
				}
				else if ($this->lookupTable == 'status')
				{
					$res[] = array("kod" => $row['kod_status'], "nama" => $row['nama_status']);
				}
				else if ($this->lookupTable == 'teras_strategik')
				{
					$res[] = array("kod" => $row['kod_teras'], "nama" => $row['nama_teras']);
				}					
			}

			return json_encode($res); 

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
			//die($e->getMessage());
		}
	}

	/**
	 * Count records : No of records
	 */
	public function countNo($codeType, $code)
	{
		$this->jenis = $codeType;
		$this->kod = $code;

		$sql_sektor = '';
		$sql_kat = '';
		$sql_teras = '';
		$sql_status = '';

		$sql = "SELECT COUNT(dokumen.kod_dok) FROM dokumen";

		if ($this->jenis == 'sektor')
		{
			$sql .= " WHERE kod_sektor = :kod";
		}
		else if ($this->jenis == 'kategori')
		{
			$sql .= " WHERE kod_kat = :kod";
		}
		else if ($this->jenis == 'teras_strategik')
		{
			$sql .= " INNER JOIN kategori B ON dokumen.kod_kat = B.kod_kat 
						   INNER JOIN sektor C	ON dokumen.kod_sektor = C.kod_sektor 
						   INNER JOIN status D	ON dokumen.kod_status = D.kod_status
						   INNER JOIN teras_dok E ON E.kod_dok = dokumen.kod_dok
						   INNER JOIN teras_strategik F ON E.kod_teras = F.kod_teras AND E.checked_value = 1
						WHERE F.kod_teras = :kod";
		}
		else if ($this->jenis == 'status')
		{
			$sql .= " WHERE kod_status = :kod";
		}

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute([':kod' => $this->kod]); 
			$bil = $stmt->fetch(PDO::FETCH_NUM);

			return $bil;

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
		}
	}

	public function showFilterJabatan($code)
	{
		$this->kod = $code;

		$sql = "SELECT DOK.kod_dok,DOK.kod_jab_baharu, DOK.tarikh_serah, JAB.kod_jab, JAB.nama_jab
					FROM dokumen DOK
						INNER JOIN jabatan JAB
							ON DOK.kod_jab_baharu = JAB.kod_jab
					WHERE DOK.kod_dok = :kod";

		try
		{
			$_pdo = New Dbase;
			$stmt = $_pdo->getDb()->prepare($sql);
			$stmt->execute([':kod' => $this->kod]); 
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return json_encode($result); 

			$this->_pdo = null;
		}
		catch(PDOException $e)
		{
			die("Failed to run query");
		}
	}
}