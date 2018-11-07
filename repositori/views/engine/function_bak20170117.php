<?php 
# required in lay_adminmaintop.php
# --> how a function looks like
## function functionName() { 
##     code to be executed;
## }

# **** Database Operations ****

# searching for one name
function searchnameloginmysqli(){
	$name = $_SESSION['slashedtxtnama'];
	$sql = "SELECT name FROM users WHERE name = '$name'";
	if (!$result = $mysqli->query($sql)) {
    // Oh no! The query failed. 
		echo "Maaf, sistem mengalami sedikit masalah.";

    // Again, do not do this on a public site, but we'll show you how
    // to get the error information
		echo "Error: Our query failed to execute and here is why: \n";
		echo "Query: " . $sql . "\n";
		echo "Errno: " . $mysqli->errno . "\n";
		echo "Error: " . $mysqli->error . "\n";
		exit;
	}
	
	// Phew, we made it. We know our MySQL connection and query 
	// succeeded, but do we have a result?
	if ($result->num_rows === 0) {
    // Oh, no rows! Sometimes that's expected and okay, sometimes
    // it is not. You decide. In this case, maybe actor_id was too
    // large? 
		echo "Maaf, sistem tidak menemui ID $name. Sila cuba lagi.";
		$_SESSION['loginerror'] = "Maaf, sistem tidak menemui nama login seperti yang dimasukkan.";
		exit;
	}
	elseif ($result->num_rows === 1) {
		$_SESSION['loginnamecount'] = $data;
		$_SESSION['loginerror'] = "";
		exit;
	}
	else {
		$_SESSION['loginerror'] = "Maaf, sistem mendapati nama login seperti yang dimasukkan mempunyai duplikasi. Sila hubungi pentadbir sistem untuk bantuan.";
		exit;
	}
}

// searching for one name
function searchnameloginmysqli01($inputname){
	$name = $inputname;
	$statement = $db->prepare("SELECT `name` FROM `users` WHERE `name` = ?");
	$statement->bind_param('s', $name);
	$statement->execute();
	$statement->bind_result($returned_name);
	while($statement->fetch()){
		echo $returned_name .'<br />';
	}	
	$statement->free_result();
	// if(!$result = $db->query($sql)) {
	// 	die('There was an error running the query [' . $db->error . ']');
	// }
	// echo 'Total results: ' . $result->num_rows;

	$_SESSION['loginnamecount'] = $data;
}


# PBKDF2 Generator
/*
 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
 * $algorithm - The hash algorithm to use. Recommended: SHA256
 * $password - The password.
 * $salt - A salt that is unique to the password.
 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
 * $key_length - The length of the derived key in bytes.
 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
 * Returns: A $key_length-byte key derived from the password and salt.
 *
 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
 *
 * This implementation of PBKDF2 was originally created by https://defuse.ca
 * With improvements by http://www.variations-of-shadow.com
 */
function fnPBKDF2($algorithm, $password, $salt, $count, $key_length, $raw_output = false){
	$algorithm = strtolower($algorithm);
	if(!in_array($algorithm, hash_algos(), true))
		trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
	if($count <= 0 || $key_length <= 0)
		trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

	if (function_exists("hash_pbkdf2")) {
        // The output length is in NIBBLES (4-bits) if $raw_output is false!
		if (!$raw_output) {
			$key_length = $key_length * 2;
		}
		return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
	}

	$hash_length = strlen(hash($algorithm, "", true));
	$block_count = ceil($key_length / $hash_length);

	$output = "";
	for($i = 1; $i <= $block_count; $i++) {
        // $i encoded as 4 bytes, big endian.
		$last = $salt . pack("N", $i);
        // first iteration
		$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
        // perform the other $count - 1 iterations
		for ($j = 1; $j < $count; $j++) {
			$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
		}
		$output .= $xorsum;
	}

	if($raw_output)
		return substr($output, 0, $key_length);
	else
		return bin2hex(substr($output, 0, $key_length));
}

# this function will generate a dropdown list of kementerian 
# used in newdoc.php, newuser.php, listuser.php, listdoc.php
function fnDropdownKem($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_kem, nama_kem FROM kementerian WHERE nama_kem = "Jabatan Perdana Menteri" AND kod_kem != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kem">Kementerian <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="kod_kem" name="kod_kem" required="required">
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($_SESSION['kod_kem'] == "") {
                        $_SESSION['kod_kem'] = $_SESSION['loggedin_kod_kem'];
                    }
                    if ($row['kod_kem'] == $_SESSION['kod_kem']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_kem'].">".$row['nama_kem']."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownKemForAgencyMgmt($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_kem, nama_kem FROM kementerian WHERE nama_kem = "Jabatan Perdana Menteri" AND kod_kem != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kem">Kementerian <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="kod_kem" name="kod_kem" required="required" autofocus>
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($_SESSION['kod_kem'] == "") {
                        $_SESSION['kod_kem'] = $_SESSION['loggedin_kod_kem'];
                    }
                    if ($row['kod_kem'] == $_SESSION['kod_kem']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_kem'].">".$row['nama_kem']." (".$row['kod_kem'].")</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownKemForView($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_kem, nama_kem FROM kementerian WHERE nama_kem = "Jabatan Perdana Menteri" AND kod_kem != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
    	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kem">Kementerian <span class="required">*</label>
    	<div class="col-md-6 col-sm-6 col-xs-12">
    		<!-- <select class="form-control" id="kod_kem" name="kod_kem" required="required"> -->
    			<!-- <option value="1">Sila pilih...</option> -->
    			<?php  
    			$rs=$conn->query($sql);

    			if($rs === false) {
    				trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    			} else {
    				$arr = $rs->fetch_all(MYSQLI_ASSOC);
    			}
    			foreach($arr as $row) {
                    if ($_SESSION['kod_kem'] == "") {
                        $_SESSION['kod_kem'] = $_SESSION['loggedin_kod_kem'];
                    }
                    if ($row['kod_kem'] == $_SESSION['kod_kem']) {
                        $dropdownselected="selected";
                        $_SESSION['nama_kem_for_view'] = $row['nama_kem'];
                    }
                    else {
                        $dropdownselected="";
                    }
    				// echo "<option ".$dropdownselected." value=".$row['kod_kem'].">".$row['nama_kem']."</option>";
    			}
    			?>
    		<!-- </select> -->
            <p>
                <?php echo $_SESSION['nama_kem_for_view']; ?>
            </p>
    	</div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

# this function will generate a dropdown list of kategori dokumen 
# used in newdoc.php, listdoc.php
function fnDropdownKategoriForView($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_kat, nama_kat FROM kategori WHERE kod_kat != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kat">Kategori <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <!-- <select class="form-control" id="kod_kat" name="kod_kat" required="required" autofocus disabled> -->
                <!-- <option value="1">Sila pilih...</option> -->
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_kat'] == $_SESSION['kod_kat']) {
                        $dropdownselected="selected";
                        $_SESSION['kod_kat_for_doc_view'] = $row['kod_kat'];
                        $_SESSION['nama_kat_for_doc_view'] = $row['nama_kat'];
                    }
                    else {
                        $dropdownselected="";
                    }
                    // echo "<option ".$dropdownselected." value=".$row['kod_kat'].">".$row['nama_kat']."</option>";
                }
                ?>
            <!-- </select> -->
            <p>
                <?php echo $_SESSION['nama_kat_for_doc_view']; ?>
            </p>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownKategori($a,$b,$c,$d){
    // $DBServer = $a;
    // $DBUser   = $b;
    // $DBPass   = $c;
    // $DBName   = $d;
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_kat, nama_kat FROM kategori WHERE kod_kat != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
    	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kat">Kategori <span class="required">*</label>
    	<div class="col-md-6 col-sm-6 col-xs-12">
    		<select class="form-control" id="kod_kat" name="kod_kat" required="required" autofocus>
    			<option value="1">Sila pilih...</option>
    			<?php  
    			$rs=$conn->query($sql);

    			if($rs === false) {
    				trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    			} else {
    				$arr = $rs->fetch_all(MYSQLI_ASSOC);
    			}
    			foreach($arr as $row) {
                    if ($row['kod_kat'] == $_SESSION['kod_kat']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
    				echo "<option ".$dropdownselected." value=".$row['kod_kat'].">".$row['nama_kat']."</option>";
    			}
    			?>
    		</select>
    	</div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownGelaranNama($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_gelaran_nama, gelaran_nama FROM gelaran_nama WHERE kod_gelaran_nama != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_gelaran_nama">Gelaran Nama <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="kod_gelaran_nama" name="kod_gelaran_nama" required="required">
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_gelaran_nama'] == $_SESSION['kod_gelaran_nama']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_gelaran_nama'].">".stripslashes($row['gelaran_nama'])."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownSektor($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_sektor, nama_sektor FROM sektor WHERE kod_sektor != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_sektor">Sektor <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="kod_sektor" name="kod_sektor" required="required">
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_sektor'] == $_SESSION['kod_sektor']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_sektor'].">".$row['nama_sektor']."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownSektorForView($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_sektor, nama_sektor FROM sektor WHERE kod_sektor != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_sektor">Sektor <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <!-- <select class="form-control" id="kod_sektor" name="kod_sektor" required="required"> -->
                <!-- <option value="1">Sila pilih...</option> -->
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_sektor'] == $_SESSION['kod_sektor']) {
                        $dropdownselected="selected";
                        $_SESSION['nama_sektor_for_view'] = $row['nama_sektor'];
                    }
                    else {
                        $dropdownselected="";
                    }
                    // echo "<option ".$dropdownselected." value=".$row['kod_sektor'].">".$row['nama_sektor']."</option>";
                }
                ?>
            <!-- </select> -->
            <p>
                <?php echo $_SESSION['nama_sektor_for_view']; ?>
            </p>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownBahagian($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_bah, nama_bahagian FROM bahagian WHERE kod_bah != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_bah">Bahagian <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="kod_bah" name="kod_bah" required="required">
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_bah'] == $_SESSION['kod_bah']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_bah'].">".$row['nama_bahagian']."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownBahagianForView($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_bah, nama_bahagian FROM bahagian WHERE kod_bah != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
    	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_bah">Bahagian <span class="required">*</label>
    	<div class="col-md-6 col-sm-6 col-xs-12">
    		<!-- <select class="form-control" id="kod_bah" name="kod_bah" required="required"> -->
    			<!-- <option value="1">Sila pilih...</option> -->
    			<?php  
    			$rs=$conn->query($sql);

    			if($rs === false) {
    				trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    			} else {
    				$arr = $rs->fetch_all(MYSQLI_ASSOC);
    			}
    			foreach($arr as $row) {
                    if ($row['kod_bah'] == $_SESSION['kod_bah']) {
                        $dropdownselected="selected";
                        $_SESSION['nama_bahagian_for_view'] = $row['nama_bahagian'];
                    }
                    else {
                        $dropdownselected="";
                    }
    				//                                                                                                                                                          echo "<option ".$dropdownselected." value=".$row['kod_bah'].">".$row['nama_bahagian']."</option>";
    			}
    			?>
    		<!-- </select> -->
            <p>
                <?php echo $_SESSION['nama_bahagian_for_view']; ?>
            </p>
    	</div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

# this function will generate a dropdown list of jabatan 
# used in newdoc.php, newuser.php, listuser.php, listdoc.php
function fnDropdownJab($a,$b,$c,$d,$e){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $inputidname    = $e;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_jab, nama_jab FROM jabatan WHERE kod_kem = "101" AND kod_jab != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="<?php echo $inputidname; ?>">Jabatan/Agensi <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="<?php echo $inputidname; ?>" name="<?php echo $inputidname; ?>" required="required">
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($_SESSION['kod_jab'] == "") {
                        $_SESSION['kod_jab'] = $_SESSION['loggedin_kod_jab'];
                    }
                    if ($row['kod_jab'] == $_SESSION['kod_jab']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_jab'].">".$row['nama_jab']."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownJabForView($a,$b,$c,$d,$e){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $inputidname    = $e;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_jab, nama_jab FROM jabatan WHERE kod_kem = "101" AND kod_jab != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="<?php echo $inputidname; ?>">Jabatan/Agensi <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <!-- <select class="form-control" id="<?php echo $inputidname; ?>" name="<?php echo $inputidname; ?>" required="required"> -->
                <!-- <option value="1">Sila pilih...</option> -->
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($_SESSION['kod_jab'] == "") {
                        $_SESSION['kod_jab'] = $_SESSION['loggedin_kod_jab'];
                    }
                    if ($row['kod_jab'] == $_SESSION['kod_jab']) {
                        $dropdownselected="selected";
                        $_SESSION['nama_jab_for_view'] = $row['nama_jab'];
                    }
                    else {
                        $dropdownselected="";
                    }
                    // echo "<option ".$dropdownselected." value=".$row['kod_jab'].">".$row['nama_jab']."</option>";
                }
                ?>
            <!-- </select> -->
            <p>
                <?php echo $_SESSION['nama_jab_for_view']; ?>
            </p>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownJabStatSerah($a,$b,$c,$d,$e,$f){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $inputidname    = $e;
    $labelkhas      = $f;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_jab, nama_jab FROM jabatan WHERE kod_jab != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    /* label */

    ?>
    <!-- <div class="form-group"> -->
    	<label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="<?php echo $inputidname; ?>">Jabatan/Agensi <?php echo $labelkhas; ?> <span class="required">*</label>
    	<div class="col-md-4 col-sm-4 col-xs-7">
    		<select class="form-control" id="<?php echo $inputidname; ?>" name="<?php echo $inputidname; ?>" required="required">
    			<option value="1">Sila pilih...</option>
    			<?php  
    			$rs=$conn->query($sql);

    			if($rs === false) {
    				trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    			} else {
    				$arr = $rs->fetch_all(MYSQLI_ASSOC);
    			}
    			foreach($arr as $row) {
                    if ($row['kod_jab'] == $_SESSION['kod_jab']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
    				echo "<option ".$dropdownselected." value=".$row['kod_jab'].">".$row['nama_jab']."</option>";
    			}
    			?>
    		</select>
    	</div>
    <!-- </div> -->
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownStatusDok($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_status, nama_status FROM status WHERE kod_status != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_status">Status Dokumen <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control selectpicker" id="kod_status" name="kod_status" required="required">
                <option value="1">Sila pilih...</option>
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_status'] == $_SESSION['kod_status']) {
                        $dropdownselected="selected";
                    }
                    else {
                        $dropdownselected="";
                    }
                    echo "<option ".$dropdownselected." value=".$row['kod_status'].">".$row['nama_status']."</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownStatusDokForView($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_status, nama_status FROM status WHERE kod_status != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
    	<label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_status">Status Dokumen <span class="required">*</label>
    	<div class="col-md-6 col-sm-6 col-xs-12">
    		<!-- <select class="form-control selectpicker" id="kod_status" name="kod_status" required="required"> -->
    			<!-- <option value="1">Sila pilih...</option> -->
    			<?php  
    			$rs=$conn->query($sql);

    			if($rs === false) {
    				trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    			} else {
    				$arr = $rs->fetch_all(MYSQLI_ASSOC);
    			}
    			foreach($arr as $row) {
                    if ($row['kod_status'] == $_SESSION['kod_status']) {
                        $dropdownselected="selected";
                        $_SESSION['nama_status_for_view'] = $row['nama_status'];
                    }
                    else {
                        $dropdownselected="";
                    }
    				// echo "<option ".$dropdownselected." value=".$row['kod_status'].">".$row['nama_status']."</option>";
    			}
    			?>
    		<!-- </select> -->
            <p>
                <?php echo $_SESSION['nama_status_for_view']; ?>
            </p>
    	</div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnCountTerasStrategik($a,$b,$c,$d){
    $DBServer = $a;
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'] = $rows_returned;
    }

    $rs->free();
    $conn->close();
}

function fnCheckboxTeras($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'] = $rows_returned;
    }


    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_teras">Teras Strategik <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <?php  
            $rs=$conn->query($sql);

            if($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr = $rs->fetch_all(MYSQLI_ASSOC);
            }

            $terascounter = 0;
            foreach($arr as $row) {
                $sessionname = "teras_".$terascounter;
                $checked_value_for_update = "";
                if ($_SESSION[$sessionname]["kod_teras"] == $row['kod_teras']) {
                    if ($_SESSION[$sessionname]["checked_value"] == 1) {
                        $checked_value_for_update = "checked";
                        // echo "hey...............................";
                    }
                    else {
                        $checked_value_for_update = "";
                        // echo "hoo...............................";
                    }
                }
                ?>
                <div class="checkbox">
                    <label>
                        <!--
                        id      : teras.index
                        name    : teras.index
                        value   : kod_teras
                        display : nama_teras
                        -->
                        <input type="checkbox" id="teras_<?php echo $terascounter; ?>" name="teras_<?php echo $terascounter; ?>" title="teras_<?php echo $terascounter; ?>" value="<?php echo $row['kod_teras']; ?>" class="flat" <?php echo $checked_value_for_update; ?> /> <?php echo $row['nama_teras']; ?>
                    </label>
                </div>

                <?php 
                $terascounter++;
            }
            ?>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnCheckboxTerasForUpdate($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $selected_kod_dok = $_SESSION['kod_dok_to_be_updated'];
    $sql="SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'];
    }


    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_teras">Teras Strategik <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <?php  
            $rs=$conn->query($sql);

            if($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr = $rs->fetch_all(MYSQLI_ASSOC);
            }

            $DBServer = 'localhost';
			$DBUser   = 'myadmin';            $DBPass   = 'zaq!12345@qwerty#';
            $DBName   = 'srp1_0';
            fnGetTerasDocForUpdateForm($DBServer, $DBUser, $DBPass, $DBName);
            $terascounter = 0;
            foreach($arr as $row) {
                $sessionname = "teras_dok_recorded".$terascounter;
                // $_SESSION[$sessionname] = array();
                # 0:kod_dok
                # 1:kod_teras
                # 2:checked_value
                $temprowkodteras = $row["kod_teras"];
                // fnRunAlert("$temprowkodteras");
                $tempkodterassession = $_SESSION["$sessionname"]["$terascounter"]["1"];
                // fnRunAlert("$tempkodterassession");
                // fnRunAlert("$sessionname");
                // fnRunAlert("$terascounter");
                if ($_SESSION["$sessionname"]["$terascounter"]["1"] == $row["kod_teras"]) {
                    if ($_SESSION["$sessionname"]["$terascounter"]["2"] == 1) {
                        $checked_value_for_update = "checked";
                    }
                    else {
                        $checked_value_for_update = "";
                    }
                }
                ?>
                <div class="checkbox">
                    <label>
                        <!--
                        id      : teras.index
                        name    : teras.index
                        value   : kod_teras
                        display : nama_teras
                        -->
                        <input type="checkbox" id="teras_<?php echo $terascounter; ?>" name="teras_<?php echo $terascounter; ?>" title="teras_<?php echo $terascounter; ?>" value="<?php echo $row['kod_teras']; ?>" class="flat" <?php echo $checked_value_for_update; ?> /> <?php echo $row['nama_teras']; ?>
                    </label>
                </div>

                <?php
                $terascounter++;
            }
            ?>
        </div>
    </div>
    <?php

    // fnClearTerasDokSessionForUpdateForm();

    $rs->free();
    $conn->close();
}

function fnCheckboxTerasForView($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $selected_kod_dok = $_SESSION['kod_dok_to_be_updated'];
    $sql="SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'];
    }


    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_teras">Teras Strategik <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <?php  
            $rs=$conn->query($sql);

            if($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr = $rs->fetch_all(MYSQLI_ASSOC);
            }

            $DBServer = 'localhost';
			$DBUser   = 'myadmin';            $DBPass   = 'zaq!12345@qwerty#';
            $DBName   = 'srp1_0';
            fnGetTerasDocForUpdateForm($DBServer, $DBUser, $DBPass, $DBName);
            $terascounter = 0;
            foreach($arr as $row) {
                $sessionname = "teras_dok_recorded".$terascounter;
                // $_SESSION[$sessionname] = array();
                # 0:kod_dok
                # 1:kod_teras
                # 2:checked_value
                $temprowkodteras = $row["kod_teras"];
                // fnRunAlert("$temprowkodteras");
                $tempkodterassession = $_SESSION["$sessionname"]["$terascounter"]["1"];
                // fnRunAlert("$tempkodterassession");
                // fnRunAlert("$sessionname");
                // fnRunAlert("$terascounter");
                if ($_SESSION["$sessionname"]["$terascounter"]["1"] == $row["kod_teras"]) {
                    if ($_SESSION["$sessionname"]["$terascounter"]["2"] == 1) {
                        $checked_value_for_update = "checked";
                    }
                    else {
                        $checked_value_for_update = "";
                    }
                }
                ?>
                <div class="checkbox">
                    <label>
                        <!--
                        id      : teras.index
                        name    : teras.index
                        value   : kod_teras
                        display : nama_teras
                        -->
                        <input type="checkbox" id="teras_<?php echo $terascounter; ?>" name="teras_<?php echo $terascounter; ?>" title="teras_<?php echo $terascounter; ?>" value="<?php echo $row['kod_teras']; ?>" class="flat" <?php echo $checked_value_for_update; ?> disabled /> <?php echo $row['nama_teras']; ?>
                    </label>
                </div>

                <?php
                $terascounter++;
            }
            ?>
        </div>
    </div>
    <?php

    // fnClearTerasDokSessionForUpdateForm();

    $rs->free();
    $conn->close();
}

function fnGetTerasDocForUpdateForm($a,$b,$c,$d){
    $DBServer = $a;
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;
    # get doc id 
    $kod_dok_to_be_updated = $_SESSION['kod_dok_to_be_updated'];
    // fnRunAlert("$kod_dok_to_be_updated");

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT * FROM teras_dok WHERE kod_dok = $kod_dok_to_be_updated";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['num_of_selected_teras_dok'] = $rows_returned;
        // fnRunAlert("$rows_returned");
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }

    $rowindex = 0;
    foreach($arr as $row) {
        $sessionname = "teras_dok_recorded".$rowindex;
        $_SESSION["$sessionname"] = array();
        for ($col=0; $col < 3; $col++) { 
            if ($col == 0) {
                $tablefield = "kod_dok";
                $_SESSION["$sessionname"]["$rowindex"]["$col"] = $row["$tablefield"];
                $tempvar = $_SESSION["$sessionname"]["$rowindex"]["$col"];
                // fnRunAlert("$rowindex");
                // fnRunAlert("$tempvar");
            }
            elseif ($col == 1) {
                $tablefield = "kod_teras";
                $_SESSION["$sessionname"]["$rowindex"]["$col"] = $row["$tablefield"];
                $tempvar = $_SESSION["$sessionname"]["$rowindex"]["$col"];
                // fnRunAlert("$rowindex");
                // fnRunAlert("$tempvar");
            }
            elseif ($col == 2) {
                $tablefield = "checked_value";
                $_SESSION["$sessionname"]["$rowindex"]["$col"] = $row["$tablefield"];
                $tempvar = $_SESSION["$sessionname"]["$rowindex"]["$col"];
                // fnRunAlert("$rowindex");
                // fnRunAlert("$tempvar");
            }
        }
        $rowindex++;
    }

    $rs->free();
    $conn->close();
}

function fnClearTerasDokSessionForUpdateForm(){
    if (isset($_SESSION['num_of_selected_teras_dok'])) {
        for ($rowindex=0; $rowindex < $_SESSION['num_of_selected_teras_dok']; $rowindex++) { 
            $sessionname = "teras_dok_recorded".$rowindex;
            for ($col=0; $col < 3; $col++) { 
                unset($_SESSION["$sessionname"]["$rowindex"]["$col"]);
            }
        }
    }
}

function fnCountCheckedTeras($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'] = $rows_returned;
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    /*
    $_SESSION["teras_$index"]["kod_teras"]["checked_value"]
    $_SESSION["teras_0"]["kod_teras"]
    $_SESSION["teras_0"]["checked_value"]
    $_SESSION["teras_1"]["kod_teras"]
    $_SESSION["teras_1"]["checked_value"]
    $_SESSION["teras_2"]["kod_teras"]
    $_SESSION["teras_2"]["checked_value"]
    $_SESSION["teras_3"]["kod_teras"]
    $_SESSION["teras_3"]["checked_value"]
    */
    $teras_index = 0;
    $_SESSION['checked_teras'] = 0;
    $checked_teras = 0;
    foreach($arr as $row) {
        $sessionname = "teras_".$teras_index;
        $_SESSION[$sessionname] = array();
        # capture teras_index
        $_SESSION['teras_index'] = $teras_index;
        # capture checked value (1 or 0)
        if (isset($_POST["teras_".$teras_index]) != "") {
            $_SESSION[$sessionname]["kod_teras"] = $_POST["teras_".$teras_index];
            $_SESSION[$sessionname]["checked_value"] = 1;
            $_SESSION['checked_value'] = 1;
            $checked_teras++;
        }
        else {
            // $_SESSION[$sessionname]["kod_teras"] = $_POST["teras_$teras_index"];
            $_SESSION[$sessionname]["checked_value"] = 0;
            $_SESSION['checked_value'] = 0;
        }
        $_SESSION['checked_teras'] = $checked_teras;

        $teras_index++;
    }

    $rs->free();
    $conn->close();
}

function fnInsertCheckedTeras($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'];
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }

    $teras_index = 0;
    foreach($arr as $row) {
        # capture teras_index
        $_SESSION['teras_index'] = $teras_index;
        # capture checked value (1 or 0)
        if ($_POST["teras_$teras_index"] != 0) {
            $_SESSION['checked_value'] = 1;
        }
        else {
            $_SESSION['checked_value'] = 0;
        }
        # capture kod_teras
        $_SESSION['kod_teras'] = $row['kod_teras'];
        # capture kod_dok
        # $_SESSION['new_doc_id'] - generated in fnUploadFilesRename

        $sql='INSERT INTO teras_dok (teras_index, checked_value, kod_teras, kod_dok) VALUES (?,?,?,?)';

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('iiii',$_SESSION['teras_index'],$_SESSION['checked_value'],$_SESSION['kod_teras'],$_SESSION['new_doc_id']);

        /* Execute statement */
        $stmt->execute();

        // echo $stmt->insert_id;
        // echo $stmt->affected_rows;

        $stmt->close();

        $teras_index++;
    }

    $rs->free();
    $conn->close();
}

function fnUpdateCheckedTeras($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
        $_SESSION['bil_teras'];
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }

    $teras_index = 0;
    foreach($arr as $row) {
        # capture teras_index
        $_SESSION['teras_index'] = $teras_index;
        # capture checked value (1 or 0)
        if (isset($_POST["teras_$teras_index"]) != 0) {
            $_SESSION['checked_value'] = 1;
        }
        else {
            $_SESSION['checked_value'] = 0;
        }
        # capture kod_teras
        $_SESSION['kod_teras'] = $row['kod_teras'];
        # capture kod_dok
        # $_SESSION['new_doc_id'] - generated in fnUploadFilesRename

        $sql='UPDATE teras_dok SET checked_value=?  WHERE kod_dok=? AND kod_teras=?';

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('iii',$_SESSION['checked_value'],$_SESSION['kod_dok_to_be_updated'],$_SESSION['kod_teras']);

        /* Execute statement */
        $stmt->execute();

        // echo $stmt->insert_id;
        // echo $stmt->affected_rows;

        $stmt->close();

        $teras_index++;
    }

    $rs->free();
    $conn->close();
}

function fnInsertNewUser($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='INSERT INTO pengguna (nama_penuh, kod_gelaran_nama, nama_pengguna, kata_laluan, garam, emel, kod_kem, kod_jab, pentadbir_sistem, pentadbir_dokumen, pentadbir_pengguna, jum_mata_peranan, status_pengguna, id_pendaftar, tarikh_daftar, id_pengemaskini, tarikh_kemaskini) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('sissssiiiiiiiisis',$_SESSION['nama_penuh'],$_SESSION['kod_gelaran_nama'],$_SESSION['nama_pengguna'],$_SESSION['kata_laluan'],$_SESSION['garam'],$_SESSION['emel'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['pentadbir_sistem'],$_SESSION['pentadbir_dokumen'],$_SESSION['pentadbir_pengguna'],$_SESSION['jum_mata_peranan'],$_SESSION['status_pengguna'],$_SESSION['id_pendaftar'],$_SESSION['tarikh_daftar'],$_SESSION['id_pengemaskini'],$_SESSION['tarikh_kemaskini']);

    /* Execute statement */
    $stmt->execute();

    $stmt->close();
    $conn->close();
    fnClearSessionNewUser();
}

function fnUpdateUser($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="UPDATE pengguna SET nama_penuh=?, kod_gelaran_nama=?, nama_pengguna=?, kata_laluan=?, garam=?, emel=?, kod_kem=?, kod_jab=?, pentadbir_sistem=?, pentadbir_dokumen=?, pentadbir_pengguna=?, jum_mata_peranan=?, status_pengguna=?,  id_pengemaskini=?, tarikh_kemaskini=? WHERE id_pengguna=?";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('sissssiiiiiiiisi',$_SESSION['nama_penuh'],$_SESSION['kod_gelaran_nama'],$_SESSION['nama_pengguna'],$_SESSION['kata_laluan'],$_SESSION['garam'],$_SESSION['emel'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['pentadbir_sistem'],$_SESSION['pentadbir_dokumen'],$_SESSION['pentadbir_pengguna'],$_SESSION['jum_mata_peranan'],$_SESSION['status_pengguna'],$_SESSION['id_pengemaskini'],$_SESSION['tarikh_kemaskini'],$_SESSION['id_pengguna_utk_dikemaskini']);

    /* Execute statement */
    $stmt->execute();

    $stmt->close();
    $conn->close();
    fnClearSessionListUser();
    $_SESSION['updateUserOK'] = 1;
}

function fnCompareNewPasswords(){
    # this is to compare new passwords entered, before saving a new user record
    # get the inputs
    if (isset($_SESSION['kata_laluan']) AND isset($_SESSION['kata_laluan2'])) {
        # only compare if both inputs are NOT empty
        if ($_SESSION['kata_laluan'] == $_SESSION['kata_laluan2']) {
            # set the OK flag = 1
            $_SESSION['newpasswordcompareOK'] = 1;
        }
        else {
            # set the OK flag = 0
            $_SESSION['newpasswordcompareOK'] = 0;
        }
    }
    else {
        # send message to fill the fields
        ?>
        <script>
            alert("Sila isikan ruang Kata Laluan dan Ulang Kata Laluan.");
        </script>
        <?php
    }
}

function fnUpdateDoc($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    /* an example
        $product_name = '52 inch TV';
        $product_code = '9879798';
        $find_id = 1;

        $statement = $mysqli->prepare("UPDATE products SET product_name=?, product_code=? WHERE ID=?");

        //bind parameters for markers, where (s = string, i = integer, d = double,  b = blob)
        $statement->bind_param('ssi', $product_name, $product_code, $find_id);
        $results =  $statement->execute();
        if($results){
            print 'Success! record updated'; 
        }else{
            print 'Error : ('. $mysqli->errno .') '. $mysqli->error;
        }
    */

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $kod_dok_selected = $_SESSION['kod_dok_to_be_updated'];
    $sql="UPDATE dokumen SET tajuk_dok=?, bil_dok=?, tahun_dok=?, des_dok=?, kod_kat=?, kod_sektor=?, kod_bah=?, kod_kem=?, kod_jab=?, kod_status=?, id_pendaftar=?, tarikh_wujud=?, tarikh_dok=?, nama_dok_asal=?, nama_dok_disimpan=?, tarikh_kemaskini=?, tarikh_mansuh=?, tarikh_pinda=?, tarikh_serah=?, kod_jab_asal=?, kod_jab_baharu=?, tag_dokumen=?, tajuk_dok_asal=?, tajuk_dok_baharu=? WHERE kod_dok=?";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('siisiiiiiiissssssssiisssi',$_SESSION['tajuk_dok'],$_SESSION['bil_dok'],$_SESSION['tahun_dok'],$_SESSION['des_dok'],$_SESSION['kod_kat'],$_SESSION['kod_sektor'],$_SESSION['kod_bah'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['kod_status'],$_SESSION['id_pendaftar'],$_SESSION['tarikh_wujud'],$_SESSION['tarikh_dok'],$_SESSION['nama_fail_asal'],$_SESSION['nama_fail_disimpan'],$_SESSION['tarikh_kemaskini'],$_SESSION['tarikh_mansuh'],$_SESSION['tarikh_pinda'],$_SESSION['tarikh_serah'],$_SESSION['kod_jab_asal'],$_SESSION['kod_jab_baharu'],$_SESSION['tag_dokumen'],$_SESSION['tajuk_dok_asal'],$_SESSION['tajuk_dok_baharu'],$_SESSION['kod_dok_to_be_updated']);

    /* Execute statement */
    $stmt->execute();

    $stmt->close();
    $conn->close();
    fnClearSessionListDoc();
}

function fnInsertNewDoc($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='INSERT INTO dokumen (tajuk_dok, bil_dok, tahun_dok, des_dok, kod_kat, kod_sektor, kod_bah, kod_teras, kod_kem, kod_jab, kod_status, id_pendaftar, tarikh_wujud, tarikh_dok, nama_dok_asal, nama_dok_disimpan, tarikh_kemaskini, tarikh_mansuh, tarikh_pinda, tarikh_serah, kod_jab_asal, kod_jab_baharu, tag_dokumen, tajuk_dok_asal, tajuk_dok_baharu) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    // $_SESSION['tajuk_dok'];
    // $_SESSION['bil_dok'];
    // $_SESSION['tahun_dok']
    // $_SESSION['des_dok']
    // $_SESSION['kod_kat']
    // $_SESSION['kod_sektor']
    // $_SESSION['kod_teras']
    // $_SESSION['kod_kem']
    // $_SESSION['kod_jab']
    // $_SESSION['kod_status']
    // $_SESSION['id_pendaftar']
    // $_SESSION['tarikh_dok']

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('siisiiiiiiiissssssssiisss',$_SESSION['tajuk_dok'],$_SESSION['bil_dok'],$_SESSION['tahun_dok'],$_SESSION['des_dok'],$_SESSION['kod_kat'],$_SESSION['kod_sektor'],$_SESSION['kod_bah'],$_SESSION['kod_teras'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['kod_status'],$_SESSION['id_pendaftar'],$_SESSION['tarikh_wujud'],$_SESSION['tarikh_dok'],$_SESSION['nama_fail_asal'],$_SESSION['nama_fail_disimpan'],$_SESSION['tarikh_kemaskini'],$_SESSION['tarikh_mansuh'],$_SESSION['tarikh_pinda'],$_SESSION['tarikh_serah'],$_SESSION['kod_jab_asal'],$_SESSION['kod_jab_baharu'],$_SESSION['tag_dokumen'],$_SESSION['tajuk_dok_asal'],$_SESSION['tajuk_dok_baharu']);

    /* Execute statement */
    $stmt->execute();

    // echo $stmt->insert_id;
    // echo $stmt->affected_rows;

    $stmt->close();
    $conn->close();
    fnClearSessionNewDoc();
}

# check saved agency codes for duplicates
function fnCheckSavedAgencyCodeToAdd($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_jab = $_SESSION['kod_jab'];
    $nama_jab = $_SESSION['nama_jab'];
    $kod_kem = $_SESSION['kod_kem'];

    $sql="SELECT * FROM jabatan WHERE kod_jab = '$kod_jab' AND kod_kem = '$kod_kem'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicateagencycode'] = 1;
        fnRunAlert("Kod Jabatan / Agensi telah digunakan.");
    }
    else {
        $_SESSION['duplicatedoc'] = 0;
        // fnRunAlert("Maaf, nama dan/atau kata laluan tidak sah ATAU pengguna tidak aktif.");
    }
    $rs->free();
    $conn->close();
}

# check saved agency names for duplicates
function fnCheckSavedAgencyNameToAdd($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_jab = $_SESSION['kod_jab'];
    $nama_jab = $_SESSION['nama_jab'];
    $kod_kem = $_SESSION['kod_kem'];

    $sql="SELECT * FROM jabatan WHERE nama_jab LIKE '$nama_jab' AND kod_kem = '$kod_kem'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicateagencyname'] = 1;
        fnRunAlert("Nama Jabatan / Agensi telah digunakan.");
    }
    else {
        $_SESSION['duplicatedoc'] = 0;
        // fnRunAlert("Maaf, nama dan/atau kata laluan tidak sah ATAU pengguna tidak aktif.");
    }
    $rs->free();
    $conn->close();
}

# check saved docs for duplicates
function fnCheckSavedDoc($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_kat = $_SESSION['kod_kat'];
    $bil_dok = $_SESSION['bil_dok'];
    $tahun_dok = $_SESSION['tahun_dok'];

    $sql="SELECT * FROM dokumen WHERE kod_kat LIKE '$kod_kat' AND bil_dok = '$bil_dok' AND tahun_dok = '$tahun_dok'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicatedoc'] = 1;
        fnRunAlert("Dokumen telah wujud.");
    }
    else {
        $_SESSION['duplicatedoc'] = 0;
        // fnRunAlert("Maaf, nama dan/atau kata laluan tidak sah ATAU pengguna tidak aktif.");
    }
    $rs->free();
    $conn->close();
}

# check saved docs for duplicates
function fnCheckSavedDocToUpdate($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_kat = $_SESSION['kod_kat'];
    $bil_dok = $_SESSION['bil_dok'];
    $tahun_dok = $_SESSION['tahun_dok'];
    $kod_dok_to_be_updated = $_SESSION['kod_dok_to_be_updated'];
    $sql="SELECT * FROM dokumen WHERE kod_kat LIKE '$kod_kat' AND bil_dok = '$bil_dok' AND tahun_dok = '$tahun_dok' AND kod_dok != '$kod_dok_to_be_updated'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicatedoc'] = 1;
        fnRunAlert("Dokumen telah wujud.");
    }
    else {
        $_SESSION['duplicatedoc'] = 0;
        // fnRunAlert("Maaf, nama dan/atau kata laluan tidak sah ATAU pengguna tidak aktif.");
    }
    $rs->free();
    $conn->close();
}

function fnUploadFilesAsIs(){
    $target_dir = "../papers/";
    $_SESSION['nama_fail_asal'] = basename($_FILES["nama_dok"]["name"]);
    $target_file = $target_dir . basename($_FILES["nama_dok"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["nama_dok"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["nama_dok"]["size"] > 2000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" ) {
        echo "Sorry, only PDF, DOC & DOCX files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["nama_dok"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["nama_dok"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
    if ($uploadOk == 0) {
        $_SESSION['uploadOk'] = 0;
    }
    elseif ($uploadOk == 1) {
        $_SESSION['uploadOk'] = 1;
    }
}

function fnUploadFilesUpdateDoc(){
    /* Getting file info and separating the name */
    if (isset($_FILES["nama_dok"]["name"])) {
        $filename = $_FILES["nama_dok"]["name"];
    }
    if (isset($filename)) {
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    }
    # Check if any file is uploaded. If none, ok but end if. If not, continue to check.
    if (empty($file_basename)) {
        fnRunAlert("Tiada pengemaskinian ke atas fail yang telah dimuatnaik.");
        $uploadOk = 1;
        $_SESSION['uploadOk'] = 1;
    }
    else {
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension

        /* Create new name for file */
        $new_id=$_SESSION['kod_dok_to_be_updated']; // add quote mark for 1. removed quote mark 20161013 1720.
        $_SESSION['new_doc_id'] = $new_id;
        $new_base_name = "srp_doc".$new_id; // removed .'_' 20161013 1713

        /* Rename file */
        $new_full_file_name = "$new_base_name"."$file_ext"; // added quote marks 20161013 1722.
        $_SESSION['nama_fail_disimpan'] = "$new_full_file_name"; // added this line 20161013 1716.

        /* Setting the target directory */
        $target_dir = "../papers/";
        $full_file_name = basename($_FILES["nama_dok"]["name"]);
        $_SESSION['nama_fail_asal'] = basename($_FILES["nama_dok"]["name"]);
        $target_file = "$target_dir" . "$new_full_file_name"; // add quotation marks 20161013 1546. changed full_file_name to new_full_file_name 20161013 1701. removed full_file_name, new_base_name & added new_full_file_name 20161013 1713
        $uploadOk = 1;
        $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
        # Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["nama_dok"]["tmp_name"]);
            if($check !== false) {
                ?>
                <script>
                    alert("<?php echo "Fail imej - " . $check["mime"] . "."; ?>");
                </script>
                <?php
                $uploadOk = 0; // changed 1 to 0 20161013 1731
                $_SESSION['uploadOk'] = 0;
            } else {
                ?>
                <script>
                    alert("<?php echo "Fail bukan imej."; ?>");
                </script>
                <?php
                $uploadOk = 1; // changed 0 to 1 20161013 1731
                $_SESSION['uploadOk'] = 1;
            }
        }
        # Check if file already exists
        if (file_exists($target_file)) {
            ?>
            <script>
                alert("<?php echo "Maaf, fail telah wujud."; ?>");
            </script>
            <?php
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
        # Check file size
        if ($_FILES["nama_dok"]["size"] > 2000000) {
            ?>
            <script>
                alert("<?php echo "Maaf, fail anda terlalu besar."; ?>");
            </script>
            <?php
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
        # Allow certain file formats
        if($imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" ) {
            ?>
            <script>
                alert("<?php echo "Maaf, cuma fail PDF, DOC atau DOCX sahaja yang dibenarkan."; ?>");
            </script>
            <?php
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
        # Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            ?>
            <script>
                alert("<?php echo "Maaf, fail anda tidak dimuatnaik."; ?>");
            </script>
            <?php
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        } 
        # if everything is ok, try to upload file
        else {
            if (move_uploaded_file($_FILES["nama_dok"]["tmp_name"], "$target_file")) { // add quote marks for target_file 20161013 1550
                ?>
                <script>
                    alert("<?php echo "Fail ".basename($_FILES["nama_dok"]["name"])." telah dimuatnaik sebagai ".$new_full_file_name."."; ?>");
                </script>
                <?php
                $uploadOk = 1;
                $_SESSION['uploadOk'] = 1;
            } 
            else {
                ?>
                <script>
                    alert("<?php echo "Maaf, terdapat kesilapan memuatnaik fail anda."; ?>");
                </script>
                <?php
                $uploadOk = 0;
                $_SESSION['uploadOk'] = 0;
            }
        }
        if ($uploadOk == 0) {
            $_SESSION['uploadOk'] = 0;
        }
        elseif ($uploadOk == 1) {
            $_SESSION['uploadOk'] = 1;
        }
    }
}

function fnUploadFilesRename(){
    /* Getting file info and separating the name */
    $filename = $_FILES["nama_dok"]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Find biggest doc id/code */
    $DBServer = 'localhost';
	$DBUser   = 'myadmin';    $DBPass   = 'zaq!12345@qwerty#';
    $DBName   = 'srp1_0';
    fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);

    /* Create new name for file */
    $new_id=$_SESSION['biggest_doc_id']+1; // add quote mark for 1. removed quote mark 20161013 1720.
    $_SESSION['new_doc_id'] = $new_id;
    $new_base_name = "srp_doc".$new_id; // removed .'_' 20161013 1713

    /* Rename file */
    $new_full_file_name = "$new_base_name"."$file_ext"; // added quote marks 20161013 1722.
    $_SESSION['nama_fail_disimpan'] = "$new_full_file_name"; // added this line 20161013 1716.
    // echo $_SESSION['nama_fail_disimpan'];

    /* Setting the target directory */
    $target_dir = "../papers/";
    $full_file_name = basename($_FILES["nama_dok"]["name"]);
    $_SESSION['nama_fail_asal'] = basename($_FILES["nama_dok"]["name"]);
    $target_file = "$target_dir" . "$new_full_file_name"; // add quotation marks 20161013 1546. changed full_file_name to new_full_file_name 20161013 1701. removed full_file_name, new_base_name & added new_full_file_name 20161013 1713
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["nama_dok"]["tmp_name"]);
        if($check !== false) {
            ?>
            <script>
                alert("<?php echo "Fail imej - " . $check["mime"] . "."; ?>");
            </script>
            <?php
            $uploadOk = 0; // changed 1 to 0 20161013 1731
            $_SESSION['uploadOk'] = 0;
        } else {
            ?>
            <script>
                alert("<?php echo "Fail bukan imej."; ?>");
            </script>
            <?php
            $uploadOk = 1; // changed 0 to 1 20161013 1731
            $_SESSION['uploadOk'] = 1;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail telah wujud."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    // Check file size
    if ($_FILES["nama_dok"]["size"] > 2000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda terlalu besar."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    // Allow certain file formats
    if($imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PDF, DOC atau DOCX sahaja yang dibenarkan."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda tidak dimuatnaik."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    } 
    // if everything is ok, try to upload file
    else {
        if (move_uploaded_file($_FILES["nama_dok"]["tmp_name"], "$target_file")) { // add quote marks for target_file 20161013 1550
            ?>
            <script>
                alert("<?php echo "Fail ".basename($_FILES["nama_dok"]["name"])." telah dimuatnaik sebagai ".$new_full_file_name."."; ?>");
            </script>
            <?php
            $uploadOk = 1;
            $_SESSION['uploadOk'] = 1;
        } 
        else {
            ?>
            <script>
                alert("<?php echo "Maaf, terdapat kesilapan memuatnaik fail anda."; ?>");
            </script>
            <?php
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
    }
    if ($uploadOk == 0) {
        $_SESSION['uploadOk'] = 0;
    }
    elseif ($uploadOk == 1) {
        $_SESSION['uploadOk'] = 1;
    }
    /*

    */
}

function fnUploadFilesLong  (){
    /* Getting file info and separating the name */
    $filename = $_FILES["nama_dok"]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Find biggest doc id/code */
    fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);

    /* Create new name for file */
    $new_id=$_SESSION['biggest_doc_id']+1;
    $new_base_name = "srp_doc".$new_id."_";

    /* Rename file */
    $new_full_file_name = $new_base_name . $file_ext;

    /* Setting the target directory */
    $target_dir = "../papers/";
    $full_file_name = basename($_FILES["nama_dok"]["name"]);
    $target_file = $target_dir . $new_base_name . $full_file_name;
    $uploadOk = 1;
    $_SESSION['uploadOk'] = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

    /* Check if image file is a actual image or fake image */
    if(isset($_POST["btn_simpan_dok_baru"])) {
        $check = getimagesize($_FILES["nama_dok"]["tmp_name"]);
        if($check !== false) {
            // echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        } 
        else {
            // echo "File is not an image.";
            $uploadOk = 1;
            $_SESSION['uploadOk'] = 1;
        }
    }
    

    /* Check if file already exists */
    if (file_exists($target_file)) {
        // echo "Sorry, file already exists.";
        ?>
        <script>
            alert("Maaf, fail telah wujud.");
        </script>
        <?php
        $uploadOk = 0; // tak perlu sebab dah beri nama baru
        $_SESSION['uploadOk'] = 0;
    }
    
    /* Check file size */
    if ($_FILES["nama_dok"]["size"] > 2000000) {
        // echo "Sorry, your file is too large.";
        ?>
        <script>
            alert("Maaf, fail melebihi 2MB.");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    
    /* Allow certain file formats */
    if($imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" ) {
        // echo "Sorry, only PDF, DOC & DOCX files are allowed.";
        ?>
        <script>
            alert("Maaf, hanya fail PDF, DOC & DOCX sahaja yang dibenarkan.");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    
    /* Check if $uploadOk is set to 0 by an error */
    if ($uploadOk == 0) {
        // echo "Sorry, your file was not uploaded.";
        ?>
        <script>
            alert("Maaf, fail tidak dimuatnaik.");
        </script>
        <?php
        // if everything is ok, try to upload file
    } 
    else {
        if (move_uploaded_file($_FILES["nama_dok"]["tmp_name"], $target_file)) {
            // echo "The file ". basename( $_FILES["nama_dok"]["name"]). " has been uploaded.";
            ?>
            <script>
            alert("<?php echo 'Fail '. basename( $_FILES['nama_dok']['name']). ' telah dimuatnaik.' ?>");
            </script>
            <?php
            // $_SESSION['nama_fail_asal'] = basename( $_FILES["nama_dok"]["name"]);
            // $_SESSION['nama_fail_baru'] = $new_base_name.basename( $_FILES["nama_dok"]["name"]);
        } 
        else {
            // echo "Sorry, there was an error uploading your file.";
            ?>
            <script>
                alert("Maaf, terdapat masalah memuatnaik fail anda.");
            </script>
            <?php
        }
    }
}

function fnUploadFiles(){
    // added on 20161011 2300
    /* Getting file info */
    $filename = $_FILES["nama_dok"]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension
    $filesize = $_FILES["nama_dok"]["size"]; // get file size
    $tmp_name = $_FILES["nama_dok"]["tmp_name"]; // set the tmp_name
    $allowed_file_types = array('.doc','.docx','.pdf'); // set allowed extension
    $allowed_file_size = 2000000;
    if (in_array($file_ext,$allowed_file_types) && ($filesize <= $allowed_file_size))
    {   
        // Find biggest doc id/code
        fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);
        // Create new name for file
        $new_id=$_SESSION['biggest_doc_id']+1;
        // echo $new_id; // uncomment for debugging only
        $new_base_name = "srp_doc".$new_id;
        // echo $new_base_name; // uncomment for debugging only
        // Rename file
        $new_full_file_name = $new_base_name . $file_ext;
        // Set target path
        $target_dir = "../papers/";
        if (file_exists($target_dir . $new_full_file_name))
        {
            // file already exists error
            echo "You have already uploaded this file.";
            $_SESSION['uploadOk'] = 0;
        }
        else
        {       
            move_uploaded_file($_FILES["nama_dok"]["tmp_name"], "$target_dir"."$new_full_file_name");
            echo "File uploaded successfully.";  
            $_SESSION['uploadOk'] = 1;
        }
    }
    elseif (empty($file_basename))
    {   
        // file selection error
        echo "Please select a file to upload.";
        $_SESSION['uploadOk'] = 0;
    } 
    elseif ($filesize > $allowed_file_size) // max size 2MB
    {   
        // file size error
        echo "The file you are trying to upload is too large.";
        $_SESSION['uploadOk'] = 0;
    }
    else
    {
        // file type error
        echo "Only these file types are allowed for upload: " . implode(', ',$allowed_file_types);
        unlink($_FILES["nama_dok"]["tmp_name"]);
        $_SESSION['uploadOk'] = 0;
    }
}

function fnFindBiggestDocID($a,$b,$c,$d){
    $DBServer = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   = $b;
    $DBPass   = $c;
    $DBName   = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_dok FROM dokumen WHERE kod_dok=(SELECT max(kod_dok) FROM dokumen)';
    // $sql='SELECT kod_dok FROM dokumen ORDER BY kod_dok DESC LIMIT 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    // echo $rows_returned; // uncomment for debugging only
    // echo $arr['kod_dok']; // uncomment for debugging only
    foreach($arr as $row) {
        $_SESSION['biggest_doc_id'] = $row['kod_dok'];
    }

    $conn->close();
}

function fnInsertNewData($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="INSERT INTO $table01name ($field02name, tkh_kemaskini, id_pengemaskini, papar_data) VALUES (?,?,?,?)";
    // $sql="INSERT INTO $table01name ($field01name, $field02name, tkh_kemaskini, id_pengemaskini, papar_data) VALUES (?,?,?,?,?)";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('ssii',$_SESSION['nama_data'],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data']);
    // $stmt->bind_param('ssii',$_SESSION[$field01name],$_SESSION[$field02name],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data']);

    /* Execute statement */
    $stmt->execute();

    ?>
    <script>
        alert("Rekod berjaya disimpan!");
    </script>
    <?php

    $stmt->close();
    $conn->close();
}

function fnInsertNewAgency($a,$b,$c,$d,$e,$f,$g){
    $DBServer 		= $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   		= $b;
    $DBPass   		= $c;
    $DBName   		= $d;
    $table01name 	= $e;
    $field01name 	= $f;
    $field02name 	= $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="INSERT INTO jabatan (kod_jab, nama_jab, kod_kem, tkh_kemaskini, id_pengemaskini, papar_data) VALUES (?,?,?,?,?,?)";
    // $sql="INSERT INTO $table01name ($field01name, $field02name, tkh_kemaskini, id_pengemaskini, papar_data) VALUES (?,?,?,?,?)";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('isisii',$_SESSION['kod_jab'],$_SESSION['nama_jab'],$_SESSION['kod_kem'],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data']);

    /* Execute statement */
    $stmt->execute();

    ?>
    <script>
        alert("Rekod berjaya disimpan!");
    </script>
    <?php

    $stmt->close();
    $conn->close();
}

function fnUpdateData($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer 		= $a;
    $DBUser   		= $b;
    $DBPass   		= $c;
    $DBName   		= $d;
    $table01name 	= $e;
    $field01name 	= $f;
    $field02name 	= $g;
    $idtoupdate		= $h;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="UPDATE $table01name SET $field02name = ?, tkh_kemaskini = ?, id_pengemaskini = ?, papar_data = ? WHERE $field01name = ?";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('ssiii',$_SESSION['nama_data'],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data'],$idtoupdate);

    /* Execute statement */
    $stmt->execute();

    ?>
    <script>
        alert("Rekod berjaya dikemaskini!");
    </script>
    <?php

    $stmt->close();
    $conn->close();
}

function fnShowDocTableContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['doclist_search_keyword']) != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok FROM dokumen WHERE (tajuk_dok LIKE '%$doclist_search_keyword%' OR tahun_dok LIKE '%$doclist_search_keyword%' OR bil_dok LIKE '%$doclist_search_keyword%') ORDER BY tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    else {
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, kod_kat FROM dokumen ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
        echo "
        <tr>
            <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
        </tr>
        ";
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    $counter = 1;
    foreach($arr as $row) {
        echo "
        <tr>
            <td>".$counter.".</td>
            <td hidden>".$row[$field01name]."</td>
            <td>".stripslashes(strtoupper($row['tajuk_dok']))." BIL. ".$row['bil_dok']."/".$row['tahun_dok']."</td>
            <td style='align-content: center;' align='center'>
                <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='".$row['kod_dok']."'><i class='fa fa-edit'></i></button>
                <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='".$row['kod_dok']."'><i class='fa fa-eye'></i></button>
                
            </td>
        </tr>
        ";
        $counter++;
    }
    ?>
    <?php
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowDocTableContentForSimpleSearch($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['kata_kunci_mudah'])) {
        $katakuncimudah = $_SESSION['kata_kunci_mudah'];
        $kkm = $katakuncimudah;
        if ($kkm != "") {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$kkm%' OR tahun_dok = '$kkm' OR bil_dok = '$kkm'";
        }
        else {
            ?>
            <tr>
                <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
            </tr>
            <?php
            echo "
            ";
        }
    }
    if (isset($sql)) {
        $rs=$conn->query($sql);

        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
        }

        if ($rows_returned == 0) {
            ?>
            <tr>
                <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
            </tr>
            <?php
        }
        else {
            $rs=$conn->query($sql);

            if($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr = $rs->fetch_all(MYSQLI_ASSOC);
            }
            $counter = 1;
            foreach($arr as $row) {
                echo "
                <tr>
                    <td>".$counter.".</td>
                    <td>".stripslashes(strtoupper($row['tajuk_dok']))." BIL. ".$row['bil_dok']."/".$row['tahun_dok']."</td>
                    <td style='align-content: center;' align='center' hidden>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='".$row['kod_dok']."'><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='".$row['kod_dok']."'><i class='fa fa-eye'></i></button>
                        
                    </td>
                </tr>
                ";
                $counter++;
            }
        }
        $rs->free();
    }
    ?>
    <?php

    $conn->close();
}

function fnShowDocTableContentForAdvancedSearch($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['sqlforadvanceddocsearch'])) {
        $sql = $_SESSION['sqlforadvanceddocsearch'];
    }
    else {
        ?>
        <tr>
            <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
        </tr>
        <?php
        echo "
        ";
    }

    if (isset($sql)) {
        $rs=$conn->query($sql);

        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
        }

        if ($rows_returned == 0) {
            ?>
            <tr>
                <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
            </tr>
            <?php
        }
        else {
            $rs=$conn->query($sql);

            if($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr = $rs->fetch_all(MYSQLI_ASSOC);
            }
            $counter = 1;
            // fnRunAlert("sepatutnya keluar table");
            foreach($arr as $row) {
                echo "
                <tr>
                    <td>".$counter.".</td>
                    <td>".stripslashes(strtoupper($row['tajuk_dok']))." BIL. ".$row['bil_dok']."/".$row['tahun_dok']."</td>
                    <td style='align-content: center;' align='center' hidden>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='".$row['kod_dok']."'><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='".$row['kod_dok']."'><i class='fa fa-eye'></i></button>
                        
                    </td>
                </tr>
                ";
                $counter++;
            }
        }
        $rs->free();
    }
    ?>
    <?php

    $conn->close();
}

# displays a table listing users registered in the system
# used in listuser.php
function fnShowUserTableContent($a,$b,$c,$d){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    // $sql="SELECT * FROM dokumen";
    $sql="SELECT * FROM pengguna ORDER BY jum_mata_peranan DESC, nama_penuh ASC";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
        echo "
        <tr>
            <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
        </tr>
        ";
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    $counter = 1;
    foreach($arr as $row) {
        if ($row['pentadbir_sistem'] == 1) {
            $ikon_pent_sistem = "<i class='fa fa-gear'>&nbsp;</i>";
        }
        else {
            $ikon_pent_sistem = "";
        }
        if ($row['pentadbir_dokumen'] == 2) {
            $ikon_pent_dokumen = "<i class='fa fa-book'>&nbsp;</i>";
        }
        else {
            $ikon_pent_dokumen = "";
        }
        if ($row['pentadbir_pengguna'] == 3) {
            $ikon_pent_pengguna = "<i class='fa fa-users'>&nbsp;</i>";
        }
        else {
            $ikon_pent_pengguna = "";
        }
        if ($row['status_pengguna'] == 1) {
            $ikon_status_pengguna = "&nbsp;<i class='fa fa-thumbs-up btn-success'></i>";
        }
        else {
            $ikon_status_pengguna = "&nbsp;<i class='fa fa-thumbs-down btn-danger'></i>";
        }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td>".stripslashes($row['nama_penuh'])."</td>
            <td>".$ikon_status_pengguna."&nbsp;".stripslashes($row['nama_pengguna'])."</td>
            <td>".$ikon_pent_sistem.$ikon_pent_dokumen.$ikon_pent_pengguna."</td>
            <td style='align-content: center;' align='center'>
                <button type='submit' id='btn_papar_borang_kemaskini_pengguna' name='btn_papar_borang_kemaskini_pengguna' class='btn btn-success' title='Kemaskini' value='".$row['id_pengguna']."'><i class='fa fa-edit'></i></button>
                <!-- <button type='submit' id='btn_papar_popup_dok' name='btn_papar_popup_dok' class='btn btn-success' title='Papar' value='".$row['id_pengguna']."'><i class='fa fa-eye'></i></button> -->
                
            </td>
        </tr>
        ";
        $counter++;
    }
    ?>
    <?php

    $rs->free();
    $conn->close();
}

# get the record for a particular user to be updated
# used in listuser.php
function fnGetUserRecForUpdate($a,$b,$c,$d,$e){
    $DBServer                       = $a;
    $DBUser                         = $b;
    $DBPass                         = $c;
    $DBName                         = $d;
    $id_pengguna_utk_dikemaskini    = $e;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT * FROM pengguna WHERE id_pengguna = '$id_pengguna_utk_dikemaskini'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 1) {
        $rs=$conn->query($sql);

        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $arr = $rs->fetch_all(MYSQLI_ASSOC);
        }

        foreach($arr as $row) {
            $_SESSION['nama_penuh']=$row['nama_penuh'];
            $_SESSION['kod_gelaran_nama']=$row['kod_gelaran_nama'];
            $_SESSION['nama_pengguna']=$row['nama_pengguna'];
            $_SESSION['kata_laluan']=$row['kata_laluan'];
            $_SESSION['kata_laluan2']=$row['kata_laluan2'];
            $_SESSION['emel']=$row['emel'];
            $_SESSION['kod_kem']=$row['kod_kem'];
            $_SESSION['kod_jab']=$row['kod_jab'];
            $_SESSION['pentadbir_sistem']=$row['pentadbir_sistem'];
            $_SESSION['pentadbir_dokumen']=$row['pentadbir_dokumen'];
            $_SESSION['pentadbir_pengguna']=$row['pentadbir_pengguna'];
            $_SESSION['status_pengguna']=$row['status_pengguna'];
        }
    }

    $rs->free();

    $conn->close();
}

function fnShowDataTableContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT $field01name, $field02name, papar_data FROM $table01name WHERE $field01name != 1";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <?php  
    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    $counter = 1;
    foreach($arr as $row) {
        if ($row['papar_data'] == 1) {
            $showicon = "<i class='fa fa-eye'></i>";
        }
        else {
            $showicon = "";
        }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td>".$row[$field01name]."</td>
            <td>".stripslashes($row[$field02name])."</td>
            <td>
                <button type='submit' id='btn_kemaskini_data_contoh1' name='btn_kemaskini_data_contoh1' class='btn btn-success' title='Kemaskini' value='".$row[$field01name]."'><i class='fa fa-edit'></i></button>
                ".$showicon."
            </td>
        </tr>
        ";
        $counter++;
    }

    $rs->free();
    $conn->close();
}

function fnShowAgencyTableContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer 		= $a;
    $DBUser   		= $b;
    $DBPass   		= $c;
    $DBName   		= $d;
    $table01name 	= $e;
    $field01name 	= $f;
    $field02name 	= $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT $field01name, $field02name FROM $table01name WHERE $field01name != 1";

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    ?>
    <?php  
    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    $counter = 1;
    foreach($arr as $row) {
    	if ($row['papar_data'] == 1) {
    		$showicon = "<i class='fa fa-eye'></i>";
    	}
    	else {
    		$showicon = "";
    	}
    	echo "
    	<tr>
    		<td>".$counter.".</td>
    		<td>".$row[$field01name]."</td>
    		<td>".stripslashes($row[$field02name])."</td>
    		<td>
    			<button type='submit' id='btn_kemaskini_data_contoh1' name='btn_kemaskini_data_contoh1' class='btn btn-success' title='Kemaskini' value='".$row[$field01name]."'><i class='fa fa-edit'></i></button>
    			".$showicon."
    		</td>
    	</tr>
    	";
    	$counter++;
    }

    $rs->free();
    $conn->close();
}

function fnShowViewDocContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $searchvalue    = $_SESSION['kod_dok_untuk_dipapar'];
    $_SESSION['kod_dok_to_be_updated'] = $_SESSION['kod_dok_untuk_dipapar'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // disabling magic quotes at runtime
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }


    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT * FROM $table01name WHERE $field01name = $searchvalue ORDER BY $field01name ASC";
    // $sql="SELECT $field01name, $field02name, papar_data FROM $table01name WHERE $field01name = $searchvalue";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <?php  
    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    foreach($arr as $row) {
        // $temp_nama_data=$row[$field02name];
        // $row[$field02name]=stripslashes("$temp_nama_data");
        $row[$field02name]=removeslashes($row[$field02name]);
        // $temp_nama_data=$row[$field02name];
        // $temp_nama_data="eh";
        ?>
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_dok'>Kod Dokumen <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <!-- <input type='text' id='kod_dok' name='kod_dok' title='Kod Dokumen' maxlength='11' class='form-control col-md-7 col-xs-12' value='<?php echo $row['kod_dok']; ?>' readonly > -->
                <p>
                    <?php echo $row['kod_dok']; ?>
                </p>
            </div>
        </div>
        <!-- copied from newdoc.php below -->
                <?php
                $_SESSION['kod_kat'] = $row['kod_kat'];
                fnDropdownKategoriForView($DBServer,$DBUser,$DBPass,$DBName);
                ?>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}" readonly> -->
                    <p>
                        <?php echo $row['bil_dok']; ?>
                    </p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tahun_dokumen">Tahun Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['tahun_dok']; ?>" type="text" id="tahun_dokumen" name="tahun_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="4" pattern="\d{1,4}" readonly> -->
                    <p>
                        <?php echo $row['tahun_dok']; ?>
                    </p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tajuk_dokumen">Tajuk Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required autofocus class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                    <p>
                        <?php echo $row['tajuk_dok']; ?>
                    </p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="des_dokumen">Deskripsi Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <textarea rows="4" id="des_dokumen" name="des_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $row['des_dok']; ?></textarea> -->
                    <p>
                        <?php echo $row['des_dok']; ?>
                    </p>
                  </div>
                </div>
                <?php  
                fnCheckboxTerasForView($DBServer,$DBUser,$DBPass,$DBName); 
                // fnDropdownList($DBServer,$DBUser,$DBPass,$DBName,"Sektor","kod_sektor","kod_sektor","nama_sektor","sektor"); // label,input name,field1,field2,table name
                ?>
                <?php 
                $_SESSION['kod_kem'] = $row['kod_kem'];
                fnDropdownKemForView($DBServer,$DBUser,$DBPass,$DBName);
                $_SESSION['kod_jab'] = $row['kod_jab'];
                fnDropdownJabForView($DBServer,$DBUser,$DBPass,$DBName,'kod_jab');
                $_SESSION['kod_sektor'] = $row['kod_sektor'];
                fnDropdownSektorForView($DBServer,$DBUser,$DBPass,$DBName); 
                $_SESSION['kod_bah'] = $row['kod_bah'];
                fnDropdownBahagianForView($DBServer,$DBUser,$DBPass,$DBName); 
                $_SESSION['kod_status'] = $row['kod_status'];
                fnDropdownStatusDokForView($DBServer,$DBUser,$DBPass,$DBName);
                ?>
                <p class="stattext" hidden></p>
                <!-- mansuh -->
                <div class="form-group" id="divmansuh" hidden>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                </div>
                <!-- serah -->
                <div class="form-group" id="divserah" hidden>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <?php  
                  $_SESSION['kod_jab_asal'] = $row['kod_jab_asal'];
                  fnDropdownJabStatSerah($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_asal','Asal');
                  $_SESSION['kod_jab_baharu'] = $row['kod_jab_baharu'];
                  fnDropdownJabStatSerah($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_baharu','Baharu');
                  ?>
                </div>
                <!-- pinda -->
                <div class="form-group" id="divpinda" hidden>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept=".pdf" class="file form-control col-md-7 col-xs-12"> -->
                    <?php 
                    if ($row['nama_dok_asal'] != "") {
                        # code...
                        echo $row['nama_dok_asal']; 
                    }
                    else {
                        echo "Tiada dokumen dimuatnaik"; 
                    }
                    ?>
                  </div>
                </div>
                <!-- <div class="form-group"> -->
                    <!-- <span class="col-md-6 col-md-offset-3 col-sm-6 col-xs-12"> -->
                    <!-- </span> -->
                <!-- </div> -->
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-3" for="tarikh_wujud">Tarikh Kuat Kuasa Dokumen <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy"> -->
                    <!-- <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span> -->
                      <p>
                          <?php echo $row['tarikh_wujud']; ?>
                      </p>
                  </div>
                </div>
                <div class="form-group" hidden>
                    <span class="col-md-6 col-md-offset-3 col-sm-6 col-xs-12">
                        <?php echo $row['tarikh_pinda'].date("Y-m-d",$row['tarikh_pinda']); ?>
                    </span>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tag_dokumen"><i>Tag</i> Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <textarea rows="4" id="tag_dokumen" name="tag_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $row['tag_dokumen']; ?></textarea> -->
                    <!-- <small>masukkan <i>tag</i> dipisahkan dengan tanda koma</small> -->
                      <p>
                          <?php echo $row['tag_dokumen']; ?>
                      </p>
                  </div>
                </div>
                <div class="ln_solid"></div>
        <!-- copied from newdoc.php above -->
        <?php

        /*
        echo "
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_data'>Kod Data <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <input type='text' id='kod_data' name='kod_data' title='kod_data' maxlength='11' class='form-control col-md-7 col-xs-12' value='".$row[$field01name]."' readonly >
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='nama_data'>Nama Data <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value="."Dato\' Sri".">
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='papar_data_form'>Papar Data? 
            </label>
            <div class='checkbox'>
                <label>
                    <input type='checkbox' id='papar_data_form' name='papar_data_form' title='papar_data_form' value='1' ".$checkedvalue." class='flat'> 
                </label>
            </div>
        </div>
        ";
        */

        /*
                <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value="."Dato\' Sri".">
        */
    }
    ?>
    <?php

    $rs->free();
    $conn->close();
}

# called in listdoc.php
function fnShowUpdateDocFormContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $searchvalue    = $_SESSION['kod_dok_untuk_dikemaskini'];
    // $searchvalue    = 2;
    $_SESSION['kod_dok_to_be_updated'] = $_SESSION['kod_dok_untuk_dikemaskini'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // disabling magic quotes at runtime
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }


    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($searchvalue) != "") {
        $sql="SELECT * FROM $table01name WHERE $field01name = '$searchvalue' ORDER BY $field01name ASC";

        $rs=$conn->query($sql);

        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
        }

        $rs=$conn->query($sql);

        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $arr = $rs->fetch_all(MYSQLI_ASSOC);
        }
        foreach($arr as $row) {
            // $temp_nama_data=$row[$field02name];
            // $row[$field02name]=stripslashes("$temp_nama_data");
            $row[$field02name]=removeslashes($row[$field02name]);
            // $temp_nama_data=$row[$field02name];
            // $temp_nama_data="eh";
            ?>
            <div class='form-group'>
                <label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_dok'>Kod Dokumen <span class='required'>*</span>
                </label>
                <div class='col-md-6 col-sm-6 col-xs-12'>
                    <input type='text' id='kod_dok' name='kod_dok' title='Kod Dokumen' maxlength='11' class='form-control col-md-7 col-xs-12' value='<?php echo $row['kod_dok']; ?>' readonly >
                </div>
            </div>
            <!-- copied from newdoc.php below -->
                    <?php
                    $_SESSION['kod_kat'] = $row['kod_kat'];
                    fnDropdownKategori($DBServer,$DBUser,$DBPass,$DBName);
                    ?>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tahun_dokumen">Tahun Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['tahun_dok']; ?>" type="text" id="tahun_dokumen" name="tahun_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="4" pattern="\d{1,4}">
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tajuk_dokumen">Tajuk Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required autofocus class="form-control col-md-7 col-xs-12" maxlength="150"/>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="des_dokumen">Deskripsi Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <textarea rows="4" id="des_dokumen" name="des_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $row['des_dok']; ?></textarea>
                      </div>
                    </div>
                    <?php  
                    fnCheckboxTerasForUpdate($DBServer,$DBUser,$DBPass,$DBName); 
                    // fnDropdownList($DBServer,$DBUser,$DBPass,$DBName,"Sektor","kod_sektor","kod_sektor","nama_sektor","sektor"); // label,input name,field1,field2,table name
                    ?>
                    <?php 
                    $_SESSION['kod_kem'] = $row['kod_kem'];
                    fnDropdownKem($DBServer,$DBUser,$DBPass,$DBName);
                    $_SESSION['kod_jab'] = $row['kod_jab'];
                    fnDropdownJab($DBServer,$DBUser,$DBPass,$DBName,'kod_jab');
                    $_SESSION['kod_sektor'] = $row['kod_sektor'];
                    fnDropdownSektor($DBServer,$DBUser,$DBPass,$DBName); 
                    $_SESSION['kod_bah'] = $row['kod_bah'];
                    fnDropdownBahagian($DBServer,$DBUser,$DBPass,$DBName); 
                    $_SESSION['kod_status'] = $row['kod_status'];
                    fnDropdownStatusDok($DBServer,$DBUser,$DBPass,$DBName);
                    ?>
                    <p class="stattext" hidden></p>
                    <!-- mansuh -->
                    <div class="form-group" id="divmansuh" hidden>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                    </div>
                    <!-- serah -->
                    <div class="form-group" id="divserah" hidden>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                      <?php  
                      $_SESSION['kod_jab_asal'] = $row['kod_jab_asal'];
                      fnDropdownJabStatSerah($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_asal','Asal');
                      $_SESSION['kod_jab_baharu'] = $row['kod_jab_baharu'];
                      fnDropdownJabStatSerah($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_baharu','Baharu');
                      ?>
                    </div>
                    <!-- pinda -->
                    <div class="form-group" id="divpinda" hidden>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept=".pdf" class="file form-control col-md-7 col-xs-12">
                      </div>
                    </div>
                    <div class="form-group">
                        <span class="col-md-6 col-md-offset-3 col-sm-6 col-xs-12">
                            <?php echo $row['nama_dok_asal']; ?>
                        </span>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-3" for="tarikh_wujud">Tarikh Kuat Kuasa Dokumen <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99/99/9999'" placeholder="dd/mm/yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                    </div>
                    <div class="form-group" hidden>
                        <span class="col-md-6 col-md-offset-3 col-sm-6 col-xs-12">
                            <?php echo $row['tarikh_pinda'].date("Y-m-d",$row['tarikh_pinda']); ?>
                        </span>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tag_dokumen"><i>Tag</i> Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <textarea rows="4" id="tag_dokumen" name="tag_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $row['tag_dokumen']; ?></textarea>
                        <small>masukkan <i>tag</i> dipisahkan dengan tanda koma</small>
                      </div>
                    </div>
                    <div class="ln_solid"></div>
            <!-- copied from newdoc.php above -->
            <?php

            /*
            echo "
            <div class='form-group'>
                <label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_data'>Kod Data <span class='required'>*</span>
                </label>
                <div class='col-md-6 col-sm-6 col-xs-12'>
                    <input type='text' id='kod_data' name='kod_data' title='kod_data' maxlength='11' class='form-control col-md-7 col-xs-12' value='".$row[$field01name]."' readonly >
                </div>
            </div>
            <div class='form-group'>
                <label class='control-label col-md-3 col-sm-3 col-xs-12' for='nama_data'>Nama Data <span class='required'>*</span>
                </label>
                <div class='col-md-6 col-sm-6 col-xs-12'>
                    <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value="."Dato\' Sri".">
                </div>
            </div>
            <div class='form-group'>
                <label class='control-label col-md-3 col-sm-3 col-xs-12' for='papar_data_form'>Papar Data? 
                </label>
                <div class='checkbox'>
                    <label>
                        <input type='checkbox' id='papar_data_form' name='papar_data_form' title='papar_data_form' value='1' ".$checkedvalue." class='flat'> 
                    </label>
                </div>
            </div>
            ";
            */

            /*
                    <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value="."Dato\' Sri".">
            */
        }
    }
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowUpdateFormContent($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer 		= $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser   		= $b;
    $DBPass   		= $c;
    $DBName   		= $d;
    $table01name 	= $e;
    $field01name 	= $f;
    $field02name 	= $g;
    $searchvalue	= $h;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // disabling magic quotes at runtime
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }


    // check connection
    if ($conn->connect_error) {
    	trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT $field01name, $field02name, papar_data FROM $table01name WHERE $field01name = $searchvalue";

    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$rows_returned = $rs->num_rows;
    }

    ?>
    <?php  
    $rs=$conn->query($sql);

    if($rs === false) {
    	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
    	$arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    foreach($arr as $row) {
    	if ($row['papar_data'] == '1') {
    		$checkedvalue = "checked";
    	}
    	else {
    		$checkedvalue = "";
    	}
        // $temp_nama_data=$row[$field02name];
        // $row[$field02name]=stripslashes("$temp_nama_data");
        $row[$field02name]=removeslashes($row[$field02name]);
        // $temp_nama_data=$row[$field02name];
        // $temp_nama_data="eh";
    	?>
    	<div class='form-group'>
    		<label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_data'>Kod Data <span class='required'>*</span>
    		</label>
    		<div class='col-md-6 col-sm-6 col-xs-12'>
    			<input type='text' id='kod_data' name='kod_data' title='kod_data' maxlength='11' class='form-control col-md-7 col-xs-12' value='<?php echo $row[$field01name]; ?>' readonly >
    		</div>
    	</div>
    	<div class='form-group'>
    		<label class='control-label col-md-3 col-sm-3 col-xs-12' for='nama_data'>Nama Data <span class='required'>*</span>
    		</label>
    		<div class='col-md-6 col-sm-6 col-xs-12'>
    			<input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value='<?php echo htmlspecialchars($row[$field02name],ENT_QUOTES); ?>'>
    		</div>
    	</div>
    	<div class='form-group'>
    		<label class='control-label col-md-3 col-sm-3 col-xs-12' for='papar_data_form'>Papar Data 
    		</label>
    		<div class='checkbox'>
    			<label>
    				<input type='checkbox' id='papar_data_form' name='papar_data_form' title='papar_data_form' value='1' <?php echo $checkedvalue; ?> class='flat'> 
    			</label>
    		</div>
    	</div>
        <?php
    	;
    	$counter++;

        /*
        echo "
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_data'>Kod Data <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <input type='text' id='kod_data' name='kod_data' title='kod_data' maxlength='11' class='form-control col-md-7 col-xs-12' value='".$row[$field01name]."' readonly >
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='nama_data'>Nama Data <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value="."Dato\' Sri".">
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='papar_data_form'>Papar Data? 
            </label>
            <div class='checkbox'>
                <label>
                    <input type='checkbox' id='papar_data_form' name='papar_data_form' title='papar_data_form' value='1' ".$checkedvalue." class='flat'> 
                </label>
            </div>
        </div>
        ";
        */

        /*
                <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value="."Dato\' Sri".">
        */
    }
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnSearchDocSimple(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # kosongkan mesej bil hasil carian
    $_SESSION['bil_dok_carian_mudah'] = "";
    unset($_SESSION['bil_dok_carian_mudah']);
    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    # sediakan pernyataan sql
    // fnRunAlert("$_SESSION[kata_kunci_mudah]");
    $katakuncimudah = $_SESSION['kata_kunci_mudah'];
    $kkm = $katakuncimudah;
    $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$kkm%' OR tahun_dok = '$kkm' OR bil_dok = '$kkm'";
    # larikan pernyataan
    $rs=$conn->query($sql);
    # jika $rs benar, kira rekod yang berpadanan
    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $_SESSION['bil_dok_carian_mudah'] = $rows_returned;
    }
}

function fnSearchDocAdvanced(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # kosongkan mesej bil hasil carian
    $_SESSION['bil_dok_carian_lengkap'] = "";
    unset($_SESSION['bil_dok_carian_lengkap']);
    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    # sediakan pernyataan sql
    ## dapatkan input
    $cl_tajuk_dokumen = $_SESSION['cl_tajuk_dokumen'];
    $cl_tahun_dokumen = $_SESSION['cl_tahun_dokumen'];
    $kod_kat        = $_SESSION['kod_kat'];
    $kod_sektor     = $_SESSION['kod_sektor'];
    $kod_bah        = $_SESSION['kod_bah'];
    $kod_status     = $_SESSION['kod_status'];
    $kod_kem        = $_SESSION['loggedin_kod_kem'];
    $kod_jab        = $_SESSION['loggedin_kod_jab'];
    $sql = "";
    $marker = 0;
    ## dapatkan kombinasi
    if (isset($_SESSION['kombinasi_cl_dok']) != "") {
        // fnRunAlert("isset($_SESSION[kombinasi_cl_dok])");
        // fnRunAlert("Ada kombinasi");
        // fnRunAlert("comb fwd=$_SESSION[kombinasi_cl_dok]");
    }
    else {
        // fnRunAlert("Tiada kombinasi");
    }
        ## pilih sql ikut kombinasi
        # 000001
        if ($_SESSION['kombinasi_cl_dok'] === 1) {
            $sql = "SELECT * FROM dokumen WHERE kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 1;
        }
        # 000010
        elseif ($_SESSION['kombinasi_cl_dok'] === 2) {
            $sql = "SELECT * FROM dokumen WHERE kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 2;
        }
        # 000011
        elseif ($_SESSION['kombinasi_cl_dok'] === 3) {
            $sql = "SELECT * FROM dokumen WHERE kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 3;
        }
        # 000100
        elseif ($_SESSION['kombinasi_cl_dok'] === 4) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 4;
        }
        # 000101
        elseif ($_SESSION['kombinasi_cl_dok'] === 5) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 5;
        }
        # 000110
        elseif ($_SESSION['kombinasi_cl_dok'] === 6) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 6;
        }
        # 000111
        elseif ($_SESSION['kombinasi_cl_dok'] === 7) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 7;
        }
        # 001000
        elseif ($_SESSION['kombinasi_cl_dok'] === 8) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 8;
        }
        # 001001
        elseif ($_SESSION['kombinasi_cl_dok'] === 9) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 9;
        }
        # 001010
        elseif ($_SESSION['kombinasi_cl_dok'] === 10) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 10;
        }
        # 001011
        elseif ($_SESSION['kombinasi_cl_dok'] === 11) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 11;
        }
        # 001100
        elseif ($_SESSION['kombinasi_cl_dok'] === 12) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 12;
        }
        # 001101
        elseif ($_SESSION['kombinasi_cl_dok'] === 13) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 13;
        }
        # 001110
        elseif ($_SESSION['kombinasi_cl_dok'] === 14) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 14;
        }
        # 001111
        elseif ($_SESSION['kombinasi_cl_dok'] === 15) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 15;
        }
        # 010000
        elseif ($_SESSION['kombinasi_cl_dok'] === 16) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 16;
        }
        # 010001
        elseif ($_SESSION['kombinasi_cl_dok'] === 17) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 17;
        }
        # 010010
        elseif ($_SESSION['kombinasi_cl_dok'] === 18) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 18;
        }
        # 010011
        elseif ($_SESSION['kombinasi_cl_dok'] === 19) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 19;
        }
        # 010100
        elseif ($_SESSION['kombinasi_cl_dok'] === 20) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 20;
        }
        # 010101
        elseif ($_SESSION['kombinasi_cl_dok'] === 21) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 21;
        }
        # 010110
        elseif ($_SESSION['kombinasi_cl_dok'] === 22) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 22;
        }
        # 010111
        elseif ($_SESSION['kombinasi_cl_dok'] === 23) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 23;
        }
        # 011000
        elseif ($_SESSION['kombinasi_cl_dok'] === 24) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 24;
        }
        # 011001
        elseif ($_SESSION['kombinasi_cl_dok'] === 25) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 25;
        }
        # 011010
        elseif ($_SESSION['kombinasi_cl_dok'] === 26) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 26;
        }
        # 011011
        elseif ($_SESSION['kombinasi_cl_dok'] === 27) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 27;
        }
        # 011100
        elseif ($_SESSION['kombinasi_cl_dok'] === 28) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 28;
        }
        # 011101
        elseif ($_SESSION['kombinasi_cl_dok'] === 29) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 29;
        }
        # 011110
        elseif ($_SESSION['kombinasi_cl_dok'] === 30) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 30;
        }
        # 011111
        elseif ($_SESSION['kombinasi_cl_dok'] === 31) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 31;
        }
        # 100000
        elseif ($_SESSION['kombinasi_cl_dok'] === 32) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 32;
        }
        # 100001
        elseif ($_SESSION['kombinasi_cl_dok'] === 33) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 33;
        }
        # 100010
        elseif ($_SESSION['kombinasi_cl_dok'] === 34) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 34;
        }
        # 100011
        elseif ($_SESSION['kombinasi_cl_dok'] === 35) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 35;
        }
        # 100100
        elseif ($_SESSION['kombinasi_cl_dok'] === 36) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 36;
        }
        # 100101
        elseif ($_SESSION['kombinasi_cl_dok'] === 37) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 37;
        }
        # 100110
        elseif ($_SESSION['kombinasi_cl_dok'] === 38) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 38;
        }
        # 100111
        elseif ($_SESSION['kombinasi_cl_dok'] === 39) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 39;
        }
        # 101000
        elseif ($_SESSION['kombinasi_cl_dok'] === 40) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 40;
        }
        # 101001
        elseif ($_SESSION['kombinasi_cl_dok'] === 41) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 41;
        }
        # 101010
        elseif ($_SESSION['kombinasi_cl_dok'] === 42) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 42;
        }
        # 101011
        elseif ($_SESSION['kombinasi_cl_dok'] === 43) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 43;
        }
        # 101100
        elseif ($_SESSION['kombinasi_cl_dok'] === 44) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 44;
        }
        # 101101
        elseif ($_SESSION['kombinasi_cl_dok'] === 45) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 45;
        }
        # 101110
        elseif ($_SESSION['kombinasi_cl_dok'] === 46) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 46;
        }
        # 101111
        elseif ($_SESSION['kombinasi_cl_dok'] === 47) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 47;
        }
        # 110000
        elseif ($_SESSION['kombinasi_cl_dok'] === 48) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 48;
        }
        # 110001
        elseif ($_SESSION['kombinasi_cl_dok'] === 49) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 49;
        }
        # 110010
        elseif ($_SESSION['kombinasi_cl_dok'] === 50) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 50;
        }
        # 110011
        elseif ($_SESSION['kombinasi_cl_dok'] === 51) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 51;
        }
        # 110100
        elseif ($_SESSION['kombinasi_cl_dok'] === 52) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 52;
        }
        # 110101
        elseif ($_SESSION['kombinasi_cl_dok'] === 53) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 53;
        }
        # 110110
        elseif ($_SESSION['kombinasi_cl_dok'] === 54) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 54;
        }
        # 110111
        elseif ($_SESSION['kombinasi_cl_dok'] === 55) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 55;
        }
        # 111000
        elseif ($_SESSION['kombinasi_cl_dok'] === 56) {        
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 56;
        }
        # 111001
        elseif ($_SESSION['kombinasi_cl_dok'] === 57) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 57;
        }
        # 111010
        elseif ($_SESSION['kombinasi_cl_dok'] === 58) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 58;
        }
        # 111011        
        elseif ($_SESSION['kombinasi_cl_dok'] === 59) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 59;
        }
        # 111100
        elseif ($_SESSION['kombinasi_cl_dok'] === 60) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 60;
        }
        # 111101
        elseif ($_SESSION['kombinasi_cl_dok'] === 61) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 61;
        }
        # 111110
        elseif ($_SESSION['kombinasi_cl_dok'] === 62) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 62;
        }
        # 111111
        elseif ($_SESSION['kombinasi_cl_dok'] === 63) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 63;
        }
    # larikan pernyataan
    $_SESSION['sqlforadvanceddocsearch'] = "";
    if (isset($sql)) {
        $rs=$conn->query($sql);
        $_SESSION['sqlforadvanceddocsearch'] = $sql;
        // fnRunAlert("sql comb=$sql");
        // fnRunAlert("marker comb=$marker");
        # jika $rs benar, kira rekod yang berpadanan
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } 
        else {
            $rows_returned = $rs->num_rows;
            $_SESSION['bil_dok_carian_lengkap'] = $rows_returned;
            // fnRunAlert("$_SESSION[bil_dok_carian_lengkap] bil hasil carian lengkap di function.php");
            // fnRunAlert("bil hasil comb=$_SESSION[bil_dok_carian_lengkap]");
        }
    }
    elseif (!isset($sql)) {
        $_SESSION['bil_dok_carian_lengkap'] = 0;
        fnRunAlert("sql0=$sql");
        // fnRunAlert("bil hasil0=$_SESSION[bil_dok_carian_lengkap]");
    }
}

function fnDashCatDisplay(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # set nilai awal

    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # sediakan pernyataan sql

    ## Cari kod-kod kategori, nama kategori
    $sql = "SELECT * FROM kategori WHERE kod_kat != '1' ORDER BY nama_kat";
    ## Bagi setiap kategori, kira bilangan

    # larikan pernyataan
    $_SESSION['sqlforadvanceddocsearch'] = "";
    if (isset($sql)) {
        $rs=$conn->query($sql);
        # jika $rs benar, kira rekod yang berpadanan
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } 
        else {
            $rows_returned = $rs->num_rows;
            $_SESSION['bil_kategori'] = $rows_returned;
        }
    }
    elseif (!isset($sql)) {
        fnRunAlert("Maaf, sistem gagal untuk mencari kategori. (fnDashCatDisplay)");
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    foreach($arr as $row) {
        $kod_kat = $row['kod_kat'];
        $nama_kat = $row['nama_kat'];
        $sql_bil_dok_dgn_kat_ini = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat'";
        $rs2=$conn->query($sql_bil_dok_dgn_kat_ini);
        if($rs2 === false) {
            trigger_error('Wrong SQL: ' . $sql_bil_dok_dgn_kat_ini . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned2 = $rs2->num_rows;
            $_SESSION['bil_dok_dgn_kat_ini'] = 0;
            $_SESSION['bil_dok_dgn_kat_ini'] = $rows_returned2;
        }
        if ($_SESSION['bil_dok_dgn_kat_ini'] != "0") {
            ?>
            <div>
              <p><?php echo $nama_kat; ?> (<?php echo $_SESSION['bil_dok_dgn_kat_ini']; ?>)</p>
              <div class="">
                <div class="progress progress_sm" style="width: 76%;">
                  <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="<?php echo $_SESSION['bil_dok_dgn_kat_ini']; ?>"></div>
                </div>
              </div>
            </div>
            <?php
        }
    }
}

function fnCountDocInRep(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # set nilai awal

    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # sediakan pernyataan sql

    ## Cari kod-kod kategori, nama kategori
    $sql = "SELECT * FROM dokumen";
    ## Bagi setiap kategori, kira bilangan

    # larikan pernyataan
    $_SESSION['sqlforadvanceddocsearch'] = "";
    if (isset($sql)) {
        $rs=$conn->query($sql);
        # jika $rs benar, kira rekod yang berpadanan
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } 
        else {
            $rows_returned = $rs->num_rows;
            $_SESSION['bil_dokumen'] = $rows_returned;
            echo $_SESSION['bil_dokumen'];
            $_SESSION['bil_dokumen'] = 0;
        }
    }
    elseif (!isset($sql)) {
        fnRunAlert("Maaf, sistem gagal untuk mencari bil dokumen. (fnCountDocInRep)");
    }
}

function fnCountActiveDocInRep(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # set nilai awal

    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # sediakan pernyataan sql

    ## Cari kod-kod kategori, nama kategori
    $sql = "SELECT * FROM dokumen WHERE kod_status = '2'";
    ## Bagi setiap kategori, kira bilangan

    # larikan pernyataan
    $_SESSION['sqlforadvanceddocsearch'] = "";
    if (isset($sql)) {
        $rs=$conn->query($sql);
        # jika $rs benar, kira rekod yang berpadanan
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } 
        else {
            $rows_returned = $rs->num_rows;
            $_SESSION['bil_dokumen'] = $rows_returned;
            echo $_SESSION['bil_dokumen'];
            $_SESSION['bil_dokumen'] = 0;
        }
    }
    elseif (!isset($sql)) {
        fnRunAlert("Maaf, sistem gagal untuk mencari bil dokumen. (fnCountDocInRep)");
    }
}

function fnCountInactiveDocInRep(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # set nilai awal

    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # sediakan pernyataan sql

    ## Cari kod-kod kategori, nama kategori
    $sql = "SELECT * FROM dokumen WHERE kod_status = '3'";
    ## Bagi setiap kategori, kira bilangan

    # larikan pernyataan
    $_SESSION['sqlforadvanceddocsearch'] = "";
    if (isset($sql)) {
        $rs=$conn->query($sql);
        # jika $rs benar, kira rekod yang berpadanan
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } 
        else {
            $rows_returned = $rs->num_rows;
            $_SESSION['bil_dokumen'] = $rows_returned;
            echo $_SESSION['bil_dokumen'];
            $_SESSION['bil_dokumen'] = 0;
        }
    }
    elseif (!isset($sql)) {
        fnRunAlert("Maaf, sistem gagal untuk mencari bil dokumen. (fnCountDocInRep)");
    }
}

function fnCountGivenDocInRep(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    # set nilai awal

    # sambung ke db
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    # semak sambungan
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # sediakan pernyataan sql

    ## Cari kod-kod kategori, nama kategori
    $sql = "SELECT * FROM dokumen WHERE kod_status = '4'";
    ## Bagi setiap kategori, kira bilangan

    # larikan pernyataan
    $_SESSION['sqlforadvanceddocsearch'] = "";
    if (isset($sql)) {
        $rs=$conn->query($sql);
        # jika $rs benar, kira rekod yang berpadanan
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } 
        else {
            $rows_returned = $rs->num_rows;
            $_SESSION['bil_dokumen'] = $rows_returned;
            echo $_SESSION['bil_dokumen'];
            $_SESSION['bil_dokumen'] = 0;
        }
    }
    elseif (!isset($sql)) {
        fnRunAlert("Maaf, sistem gagal untuk mencari bil dokumen. (fnCountDocInRep)");
    }
}

### **** Other Operations ****

# checking login status
function fnCheckLoginStatus(){
  if(!isset($_SESSION['loggedinname']) OR !isset($_SESSION['loggedinid'])){
    fnRunAlert("Maaf, anda perlu login secara sah!");
    ?>
    <script>
      var myWindow = window.open("../external/login.php", "_self");
    </script>
    <?php
  }
}

# Verify login credentials
function fnVerifyLogin($a,$b,$c,$d,$e,$f){
    $DBServer       = $a;
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $loginname      = $e;
    $loginpwd       = $f;
    // fnRunAlert($DBName);

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    // fnRunAlert($loginname." ".$loginpwd);

    $sql="SELECT * FROM pengguna WHERE nama_pengguna LIKE '$loginname' AND kata_laluan LIKE '$loginpwd' AND status_pengguna = 1";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned === 1) {
        $rs=$conn->query($sql);

        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $arr = $rs->fetch_all(MYSQLI_ASSOC);
        }
        foreach($arr as $row) {
            $_SESSION['loggedinid'] = $row['id_pengguna'];        
            $_SESSION['loggedinname'] = $row['nama_pengguna'];        
            $_SESSION['status_pentadbir_sistem'] = $row['pentadbir_sistem'];        
            $_SESSION['status_pentadbir_pengguna'] = $row['pentadbir_pengguna'];        
            $_SESSION['status_pentadbir_dokumen'] = $row['pentadbir_dokumen'];
            $_SESSION['loggedin_kod_kem'] = $row['kod_kem'];
            $_SESSION['loggedin_kod_jab'] = $row['kod_jab'];
        }
        $_SESSION['loginstatus'] = 1;
        fnRunAlert("Pengguna telah disahkan.");
        // fnRefreshPgMeta();
    }
    else {
        $_SESSION['loginstatus'] = 0;
        fnRunAlert("Maaf, nama dan/atau kata laluan tidak sah ATAU pengguna tidak aktif.");
        // fnRefreshPgMeta();
    }
    $rs->free();
    $conn->close();
}

# Forward to landing page
function fnFwdToLandingPg(){
    ?>
    <script>
        var myWindow = window.open("../layouts/lay_plainpagecontent.php", "_self");
    </script>
    <?php
}

# Refresh page using header
function fnRefreshPgHeader(){
    header("Refresh:0"); // just refresh
}

# Refresh and redirect page using header
function fnRefreshAndRedirectPgHeader($url){
    header("Refresh:0; url=$url"); // refresh and redirect to $url
}

# Refresh page using meta
function fnRefreshPgMeta(){
    ?>
    <meta http-equiv="refresh" content="0"> <!-- set time in content -->
    <?php
}

# Refresh and redirect page using meta
function fnRefreshAndRedirectPgMeta($url){
    ?>
    <!-- <meta http-equiv="refresh" content="0; url=<?php echo $url;  ?>"> --> <!-- set time in content -->
    <?php
    echo "<meta http-equiv=\"refresh\" content=\"0; url=$url\">";
}

// removing slashes
function removeslashes($string){
    $string=implode("",explode("\\",$string));
    return stripslashes(trim($string));
}

// for the not selected input
function checkAndRevalue($selectedvalue){
    // echo $selectedvalue;
    if ($selectedvalue == '0') {
        // beri nilai 1
        $selectedvalue = '1';
    }
    elseif ($selectedvalue == "") {
        // beri nilai kepada kotak teks yang kosong
        $selectedvalue = 'Tiada data dimasukkan.';
    }
    return $selectedvalue;
}

// for the not selected checkbox input
function checkAndRevalueCheckbox($selectedvalue){
	// echo $selectedvalue;
	if ($selectedvalue == "") {
		// beri nilai kepada kotak teks yang kosong
		$selectedvalue = '0';
	}
	return $selectedvalue;
}

# Clear sessions for list pages
function fnClearSessionForListPages(){
  $_SESSION['page_title'] = "";
  $_SESSION['addnew_form_title'] = "";
  $_SESSION['addnew_form_action'] = "";
  $_SESSION['update_form_title'] = "";
  $_SESSION['update_form_action'] = "";
  $_SESSION['table_title'] = "";
  $_SESSION['table_action'] = "";
}

// clear session for new doc form
function fnClearSessionNewDoc(){
  $_SESSION['tajuk_dok'] = "";
  $_SESSION['bil_dok'] = "";
  $_SESSION['tahun_dok'] = "";
  $_SESSION['des_dok'] = "";
  $_SESSION['kod_kat'] = "";
  $_SESSION['kod_sektor'] = "";
  $_SESSION['kod_teras'] = "";
  if (isset($_SESSION['bil_teras'])) {
      for ($i=0; $i < $_SESSION['bil_teras']; $i++) { 
          $_SESSION["teras_$i"]["kod_teras"] = "";
          $_SESSION["teras_$i"]["checked_value"] = "";
      }
  }
  $_SESSION['kod_kem'] = "";
  $_SESSION['kod_jab'] = "";
  $_SESSION['kod_bah'] = "";
  $_SESSION['kod_status'] = "";
  $_SESSION['id_pendaftar'] = "";
  $_SESSION['tarikh_wujud'] = "";
  $_SESSION['tarikh_dok'] = "";
  $_SESSION['nama_fail_asal'] = "";
  $_SESSION['nama_fail_disimpan'] = "";
  $_SESSION['tarikh_kemaskini'] = "";
  $_SESSION['tarikh_mansuh'] = "";
  $_SESSION['tarikh_pinda'] = "";
  $_SESSION['tarikh_serah'] = "";
  $_SESSION['kod_jab_asal'] = "";
  $_SESSION['kod_jab_baharu'] = "";
  $_SESSION['tag_dokumen'] = "";
  $_SESSION['tajuk_dok_asal'] = "";
  $_SESSION['tajuk_dok_baharu'] = "";
  $_SESSION['id_pengemaskini'] = ""; 
}

// clear session for list doc & update form
function fnClearSessionListDoc(){
  $_SESSION['tajuk_dok'] = "";
  $_SESSION['bil_dok'] = "";
  $_SESSION['tahun_dok'] = "";
  $_SESSION['des_dok'] = "";
  $_SESSION['kod_kat'] = "";
  $_SESSION['kod_sektor'] = "";
  $_SESSION['kod_teras'] = "";
  $_SESSION['kod_kem'] = "";
  $_SESSION['kod_jab'] = "";
  $_SESSION['kod_bah'] = "";
  $_SESSION['kod_status'] = "";
  $_SESSION['id_pendaftar'] = "";
  $_SESSION['tarikh_wujud'] = "";
  $_SESSION['tarikh_dok'] = "";
  $_SESSION['nama_fail_asal'] = "";
  $_SESSION['nama_fail_disimpan'] = "";
  $_SESSION['tarikh_kemaskini'] = "";
  $_SESSION['tarikh_mansuh'] = "";
  $_SESSION['tarikh_pinda'] = "";
  $_SESSION['tarikh_serah'] = "";
  $_SESSION['kod_jab_asal'] = "";
  $_SESSION['kod_jab_baharu'] = "";
  $_SESSION['tag_dokumen'] = "";
  $_SESSION['tajuk_dok_asal'] = "";
  $_SESSION['tajuk_dok_baharu'] = "";
  $_SESSION['id_pengemaskini'] = "";
  $_SESSION['kod_dok_to_be_updated'] = "";
  $_SESSION['kod_dok_utk_dikemaskini'] = "";
  $_SESSION['status_buka_borang_kemaskini_dokumen'] = "";
}

// clear session for list user & update form
function fnClearSessionListUser(){
  $_SESSION['nama_penuh'] = "";
  $_SESSION['kod_gelaran_nama'] = "";
  $_SESSION['nama_pengguna'] = "";
  $_SESSION['kata_laluan'] = "";
  $_SESSION['kata_laluan2'] = "";
  $_SESSION['garam'] = "";
  $_SESSION['emel'] = "";
  $_SESSION['kod_kem'] = "";
  $_SESSION['kod_jab'] = "";
  $_SESSION['pentadbir_sistem'] = "";
  $_SESSION['pentadbir_dokumen'] = "";
  $_SESSION['pentadbir_pengguna'] = "";
  $_SESSION['status_pengguna'] = "";
  $_SESSION['id_pendaftar'] = "";
  $_SESSION['tarikh_daftar'] = "";
  $_SESSION['id_pengemaskini'] = "";
  $_SESSION['tarikh_kemaskini'] = "";
}

// clear session for new user form
function fnClearSessionNewUser(){
  $_SESSION['nama_penuh'] = "";
  $_SESSION['kod_gelaran_nama'] = "";
  $_SESSION['nama_pengguna'] = "";
  $_SESSION['kata_laluan'] = "";
  $_SESSION['kata_laluan2'] = "";
  $_SESSION['garam'] = "";
  $_SESSION['emel'] = "";
  $_SESSION['kod_kem'] = "";
  $_SESSION['kod_jab'] = "";
  $_SESSION['pentadbir_sistem'] = "";
  $_SESSION['pentadbir_dokumen'] = "";
  $_SESSION['pentadbir_pengguna'] = "";
  $_SESSION['status_pengguna'] = "";
  $_SESSION['id_pendaftar'] = "";
  $_SESSION['tarikh_daftar'] = "";
  $_SESSION['id_pengemaskini'] = "";
  $_SESSION['tarikh_kemaskini'] = "";
}

function fnRunAlert($a){
    $_SESSION['msgforfnalert'] = "$a";
    fnAlert();
}

function fnAlert(){
    ?>
    <script>
        alert("<?php echo $_SESSION['msgforfnalert']; ?>");
    </script>
    <?php
    $_SESSION['msgforfnalert']="";
}

// set default timezone to Asia/Kuala_lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');
?>
