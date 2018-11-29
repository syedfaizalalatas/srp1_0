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
    //  die('There was an error running the query [' . $db->error . ']');
    // }
    // echo 'Total results: ' . $result->num_rows;

    $_SESSION['loginnamecount'] = $data;
}

function fnResetSessionsForPages(){
    # the session for this page has to be cleared before this page is opened.
    # when a user clicks on any menu item, the page will start clean, which means the system will reset everything
    # list all menu items and their code
    /*
      ldr - list document records
      ndr - new doc record
      sdr - search doc record
      rdr - reports of doc records
      sdrc

      status
        n - new
        v - view form opened; data is saved in array
        u - update form opened
    */
    $_SESSION['pageSource'] = 'new'; # for all related page
    $_SESSION['currentPage'] = 1; # for page with pagination
    $_SESSION['langkah'] = 1; # for newdoc_v2.php
    $_SESSION['statusDaftarDokumen'] = 'baharu'; # for newdoc_v2.phps
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kem">Kementerian <span hidden class="required">*</label>
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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kat">Kategori <span hidden class="required">*</label>
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

function fnDropdownKategoriForSearch($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_kat, nama_kat FROM kategori WHERE kod_kat != 1 AND papar_data = 1 ORDER BY nama_kat';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kat">Kategori <span class="required" hidden>*</label>
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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

function fnDropdownSektorForSearch($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_sektor">Sektor <span class="required" hidden>*</label>
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_sektor">Sektor <span hidden class="required">*</label>
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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

function fnDropdownBahagianForSearch($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_bah">Bahagian <span class="required" hidden>*</label>
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_bah">Bahagian <span hidden class="required">*</label>
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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="<?php echo $inputidname; ?>">Jabatan/Agensi <span hidden class="required">*</label>
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

    $sql='SELECT kod_jab, nama_jab FROM jabatan WHERE kod_jab != 1 ORDER BY nama_jab ASC';

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
                    if ($row['kod_jab'] == $_SESSION[$inputidname]) {
                        // fnRunAlert("$_SESSION[$inputidname]");
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

function fnDropdownJabStatSerahForView($a,$b,$c,$d,$e,$f){
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
                <?php  
                $rs=$conn->query($sql);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                foreach($arr as $row) {
                    if ($row['kod_jab'] == $_SESSION[$inputidname]) {
                        // fnRunAlert("$_SESSION[$inputidname]");
                        $dropdownselected="selected";
                        ?>
                        <textarea rows="4" id="<?php echo $inputidname; ?>" name="<?php echo $inputidname; ?>" required class="form-control col-md-7 col-xs-12" readonly><?php echo $row['nama_jab']; ?></textarea>
                        <?php
                    }
                    else {
                        $dropdownselected="";
                    }
                    // echo "<option ".$dropdownselected." value=".$row['kod_jab'].">".$row['nama_jab']."</option>";
                }
                ?>
            <!-- <select class="form-control" id="<?php echo $inputidname; ?>" name="<?php echo $inputidname; ?>" required="required" disabled> -->
                <!-- <option value="1">Sila pilih...</option> -->
            <!-- </select> -->
        </div>
    <!-- </div> -->
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownJabForDivisionMgmt($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='SELECT kod_jab, nama_jab FROM jabatan WHERE nama_jab LIKE "Unit Pemodenan%" AND kod_jab != 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    ?>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_kem">Jabatan / Agensi <span class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <select class="form-control" id="kod_jab" name="kod_jab" required="required" autofocus>
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
                    echo "<option ".$dropdownselected." value=".$row['kod_jab'].">".$row['nama_jab']." (".$row['kod_jab'].")</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <?php

    $rs->free();
    $conn->close();
}

function fnDropdownStatusDok($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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

function fnDropdownStatusDokForSearch($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_status">Status Dokumen <span class="required" hidden>*</label>
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_status">Status Dokumen <span hidden class="required">*</label>
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

function fnSetTarikhStatusDoc(){
  /*
  2 - masih berkuat kuasa
  3 - mansuh
  4 - serah
  5 - pinda
  */
  if(isset($_POST['kod_status'])) {
    if ($_POST['kod_status'] == 2) {
      $_SESSION['tarikh_mansuh'] = "0000-00-00";
      $_SESSION['tarikh_pinda'] = "0000-00-00";
      $_SESSION['tarikh_serah'] = "0000-00-00";
    }
    if ($_POST['kod_status'] == 3) {
      $_SESSION['tarikh_mansuh'] = date("Y-m-d", strtotime($_SESSION['tarikh_mansuh']));
      $_SESSION['tarikh_pinda'] = "0000-00-00";
      $_SESSION['tarikh_serah'] = "0000-00-00";
    }
    if ($_POST['kod_status'] == 4) {
      $_SESSION['tarikh_serah'] = date("Y-m-d", strtotime($_SESSION['tarikh_serah']));
      $_SESSION['tarikh_mansuh'] = "0000-00-00";
      $_SESSION['tarikh_pinda'] = "0000-00-00";
    }
    if ($_POST['kod_status'] == 5) {
      $_SESSION['tarikh_pinda'] = date("Y-m-d", strtotime($_SESSION['tarikh_pinda']));
      $_SESSION['tarikh_mansuh'] = "0000-00-00";
      $_SESSION['tarikh_serah'] = "0000-00-00";
    }
  }
  if (isset($_SESSION['tarikh_mansuh'])) {
    // fnRunAlert("Tarikh Mansuh: $_SESSION[tarikh_mansuh]");
  }
  if (isset($_SESSION['tarikh_pinda'])) {
    // fnRunAlert("Tarikh Pinda: $_SESSION[tarikh_pinda]");
  }
  if (isset($_SESSION['tarikh_serah'])) {
    // fnRunAlert("Tarikh Serah: $_SESSION[tarikh_serah]");
  }
}

function fnCountTerasStrategik($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
                if (isset($_SESSION[$sessionname]["kod_teras"]) AND $_SESSION[$sessionname]["kod_teras"] == $row['kod_teras']) {
                    if ($_SESSION[$sessionname]["checked_value"] == 1) {
                        $checked_value_for_update = "checked";
                        // echo "hey...............................";
                    }
                    else {
                        $checked_value_for_update = "";
                        // echo "hoo...............................";
                    }
                }
                else {
                    $checked_value_for_update = "";
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        // $_SESSION['bil_teras'];
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

            $DBServer       = $_SESSION['DBServer'];
            $DBUser         = $_SESSION['DBUser'];
            $DBPass         = $_SESSION['DBPass'];
            $DBName         = $_SESSION['DBName'];
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="kod_teras">Teras Strategik <span hidden class="required">*</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <?php  
            $rs=$conn->query($sql);

            if($rs === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr = $rs->fetch_all(MYSQLI_ASSOC);
            }

            $DBServer       = $_SESSION['DBServer'];
            $DBUser         = $_SESSION['DBUser'];
            $DBPass         = $_SESSION['DBPass'];
            $DBName         = $_SESSION['DBName'];
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $new_doc_id_to_search=$_SESSION['new_doc_id'];
    // fnRunAlert("$new_doc_id_to_search");

    # delete teras_dok entry for the latest new_doc_id
    $sql_del="DELETE FROM teras_dok WHERE kod_dok = '$new_doc_id_to_search'";
    $rs=$conn->query($sql_del);
    # end delete existing teras_dok entry

    # start checking for existing teras_dok entry
    $sql='SELECT * FROM teras_dok WHERE kod_dok = "$new_doc_id_to_search"';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        $existing_teras_dok=$rows_returned;
        // fnRunAlert("$existing_teras_dok");
    }
    # end checking for existing teras_dok entry


    $sql='SELECT kod_teras, nama_teras FROM teras_strategik WHERE kod_teras != 1 AND papar_data = 1';

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // $_SESSION['bil_teras'];
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }

    # 
    # if no teras_dok entry with the same kod_dok insert new teras
    $teras_index = 0;
    foreach($arr as $row) {
        # capture teras_index
        $_SESSION['teras_index'] = $teras_index;
        # capture checked value (1 or 0)
        if (!isset($_POST["teras_$teras_index"])) {
            $_POST["teras_$teras_index"] = 0;
        }
        if (isset($_POST["teras_$teras_index"]) AND $_POST["teras_$teras_index"] != 0) {
            $_SESSION['checked_value'] = 1;
        }
        else {
            $_SESSION['checked_value'] = 0;
        }
        # capture kod_teras
        $_SESSION['kod_teras'] = $row['kod_teras'];
        # capture kod_dok
        # $_SESSION['new_doc_id'] - generated in fnUploadFilesRename

        if ($existing_teras_dok==0) {
            # code...
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
        }
        elseif ($existing_teras_dok==4) { // if the entry for the kod_dok in teras_dok already existed
            $sql='UPDATE teras_dok SET checked_value=?  WHERE kod_dok=? AND kod_teras=?';

            /* Prepare statement */
            $stmt = $conn->prepare($sql);
            if($stmt === false) {
                trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            }

            /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
            $stmt->bind_param('iii',$_SESSION['checked_value'],$_SESSION['new_doc_id'],$_SESSION['kod_teras']);

            /* Execute statement */
            $stmt->execute();

            // echo $stmt->insert_id;
            // echo $stmt->affected_rows;

            $stmt->close();
        }

        $teras_index++;
    }

    $rs->free();
    $conn->close();
}

function fnUpdateCheckedTeras($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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

    if ($_SESSION['touploadOK'] == 1) {
        $kod_dok_selected = $_SESSION['kod_dok_to_be_updated'];
        $sql="UPDATE dokumen SET tajuk_dok=?, bil_dok=?, tahun_dok=?, des_dok=?, kod_kat=?, kod_sektor=?, kod_bah=?, kod_kem=?, kod_jab=?, kod_status=?, id_pengemaskini=?, tarikh_wujud=?, tarikh_dok=?, nama_dok_asal=?, nama_dok_disimpan=?, tarikh_kemaskini=?, tarikh_mansuh=?, tarikh_pinda=?, tarikh_serah=?, kod_jab_asal=?, kod_jab_baharu=?, tag_dokumen=?, tajuk_dok_asal=?, tajuk_dok_baharu=?, catatan_dokumen=? WHERE kod_dok=?";

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('siisiiiiiiissssssssiissssi',$_SESSION['tajuk_dok'],$_SESSION['bil_dok'],$_SESSION['tahun_dok'],$_SESSION['des_dok'],$_SESSION['kod_kat'],$_SESSION['kod_sektor'],$_SESSION['kod_bah'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['kod_status'],$_SESSION['id_pengemaskini'],$_SESSION['tarikh_wujud'],$_SESSION['tarikh_dok'],$_SESSION['nama_fail_asal'],$_SESSION['nama_fail_disimpan'],$_SESSION['tarikh_kemaskini'],$_SESSION['tarikh_mansuh'],$_SESSION['tarikh_pinda'],$_SESSION['tarikh_serah'],$_SESSION['kod_jab_asal'],$_SESSION['kod_jab_baharu'],$_SESSION['tag_dokumen'],$_SESSION['tajuk_dok_asal'],$_SESSION['tajuk_dok_baharu'],$_SESSION['catatan_dokumen'],$_SESSION['kod_dok_to_be_updated']);
        if ($stmt) {
            // fnRunAlert("data dgn lampiran direkod ke db");
        }
    }
    else {
        $kod_dok_selected = $_SESSION['kod_dok_to_be_updated'];
        $sql="UPDATE dokumen SET tajuk_dok=?, bil_dok=?, tahun_dok=?, des_dok=?, kod_kat=?, kod_sektor=?, kod_bah=?, kod_kem=?, kod_jab=?, kod_status=?, id_pengemaskini=?, tarikh_wujud=?, tarikh_dok=?, tarikh_kemaskini=?, tarikh_mansuh=?, tarikh_pinda=?, tarikh_serah=?, kod_jab_asal=?, kod_jab_baharu=?, tag_dokumen=?, tajuk_dok_asal=?, tajuk_dok_baharu=? WHERE kod_dok=?";

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        // fnRunAlert("$stmt");
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('siisiiiiiiissssssiisssi',$_SESSION['tajuk_dok'],$_SESSION['bil_dok'],$_SESSION['tahun_dok'],$_SESSION['des_dok'],$_SESSION['kod_kat'],$_SESSION['kod_sektor'],$_SESSION['kod_bah'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['kod_status'],$_SESSION['id_pengemaskini'],$_SESSION['tarikh_wujud'],$_SESSION['tarikh_dok'],$_SESSION['tarikh_kemaskini'],$_SESSION['tarikh_mansuh'],$_SESSION['tarikh_pinda'],$_SESSION['tarikh_serah'],$_SESSION['kod_jab_asal'],$_SESSION['kod_jab_baharu'],$_SESSION['tag_dokumen'],$_SESSION['tajuk_dok_asal'],$_SESSION['tajuk_dok_baharu'],$_SESSION['kod_dok_to_be_updated']);
        // siisiiiissssssiisss
        if ($stmt) {
            // fnRunAlert("data tanpa lampiran direkod ke db");
            // fnRunAlert("$stmt");
        }
    }

    /* Execute statement */
    $stmt->execute();

    if ($stmt) {
        # clear dok sokongan untuk rekod ni dulu, kalau ada dok sokongan baharu hendak dimuat naik
        if ($_SESSION['bilDokSokUtkMuatNaik']>0) {
            $delDokSokonganSql = "DELETE FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_selected'";
        }
        $delDokSokonganResult = $conn->query($delDokSokonganSql);
        if ($_SESSION['slot01_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot01 berjaya direkod ke db.";
            fnUpdateDokSokongan($_SESSION['nama_dok_asal_slot01'], $_SESSION['nama_dok_disimpan_slot01'], $kod_dok_selected);
            // fnInsertDokSokongan("$_SESSION[nama_dok_asal_slot01]", "$_SESSION[nama_dok_disimpan_slot01]");
            // fnRunAlert("Ni selepas insert dok sokongan slot01.");
        }
        if ($_SESSION['slot02_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot02 berjaya direkod ke db.";
            fnUpdateDokSokongan($_SESSION['nama_dok_asal_slot02'], $_SESSION['nama_dok_disimpan_slot02'], $kod_dok_selected);
        }
        if ($_SESSION['slot03_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot03 berjaya direkod ke db.";
            fnUpdateDokSokongan($_SESSION['nama_dok_asal_slot03'], $_SESSION['nama_dok_disimpan_slot03'], $kod_dok_selected);
        }
        if ($_SESSION['slot04_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot04 berjaya direkod ke db.";
            fnUpdateDokSokongan($_SESSION['nama_dok_asal_slot04'], $_SESSION['nama_dok_disimpan_slot04'], $kod_dok_selected);
        }
        $_SESSION['mesejBerjaya'] = ""; #kosongkan mesej berjaya
        fnRunAlert("Rekod BERJAYA dikemaskini.");
    }
    else {
        echo "ERROR";
    }

    $stmt->close();
    $conn->close();
    fnClearSessionListDoc();
}

function fnInsertNewDoc($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='INSERT INTO dokumen (tajuk_dok, bil_dok, tahun_dok, des_dok, kod_kat, kod_sektor, kod_bah, kod_kem, kod_jab, kod_status, id_pendaftar, tarikh_wujud, tarikh_dok, nama_dok_asal, nama_dok_disimpan, tarikh_kemaskini, tarikh_mansuh, tarikh_pinda, tarikh_serah, kod_jab_asal, kod_jab_baharu, tag_dokumen, tajuk_dok_asal, tajuk_dok_baharu, catatan_dokumen, tanda_hapus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
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
    $stmt->bind_param('siisiiiiiiissssssssiissssi',$_SESSION['tajuk_dok'],$_SESSION['bil_dok'],$_SESSION['tahun_dok'],$_SESSION['des_dok'],$_SESSION['kod_kat'],$_SESSION['kod_sektor'],$_SESSION['kod_bah'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['kod_status'],$_SESSION['id_pendaftar'],$_SESSION['tarikh_wujud'],$_SESSION['tarikh_dok'],$_SESSION['nama_fail_asal'],$_SESSION['nama_fail_disimpan'],$_SESSION['tarikh_kemaskini'],$_SESSION['tarikh_mansuh'],$_SESSION['tarikh_pinda'],$_SESSION['tarikh_serah'],$_SESSION['kod_jab_asal'],$_SESSION['kod_jab_baharu'],$_SESSION['tag_dokumen'],$_SESSION['tajuk_dok_asal'],$_SESSION['tajuk_dok_baharu'],$_SESSION['catatan_dokumen'], $tanda_hapus);

    $tanda_hapus = 1;
    
    /* Execute statement */
    // $stmt->execute();
    # 20170126 trying this
    // fnRunAlert("Ni sebelum insert dok sokongan.");
    if ($stmt->execute()) {
        // fnRunAlert("Ni selepas execute stmt.");
        $_SESSION['insertOK'] = 1; # tanda rekod berjaya dimasukkan dalam table dokumen
        if ($_SESSION['slot01_OK'] == 1) {
            // fnRunAlert("Ni baru mula if slot01.");
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot01 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot01'], $_SESSION['nama_dok_disimpan_slot01']);
            // fnInsertDokSokongan("$_SESSION[nama_dok_asal_slot01]", "$_SESSION[nama_dok_disimpan_slot01]");
            // fnRunAlert("Ni selepas insert dok sokongan slot01.");
        }
        if ($_SESSION['slot02_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot02 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot02'], $_SESSION['nama_dok_disimpan_slot02']);
        }
        if ($_SESSION['slot03_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot03 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot03'], $_SESSION['nama_dok_disimpan_slot03']);
        }
        if ($_SESSION['slot04_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot04 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot04'], $_SESSION['nama_dok_disimpan_slot04']);
        }
        $_SESSION['mesejBerjaya'] = ""; #kosongkan mesej berjaya
    }
    else {
        $_SESSION['insertOK'] = 0;
    }

    $stmt->close();

    $conn->close();
    fnClearSessionNewDoc();
}

function fnInsertNewDoc_v2($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql='INSERT INTO dokumen (tajuk_dok, bil_dok, tahun_dok, des_dok, kod_kat, kod_sektor, kod_bah, kod_kem, kod_jab, kod_status, id_pendaftar, tarikh_wujud, tarikh_dok, nama_dok_asal, nama_dok_disimpan, tarikh_kemaskini, tarikh_mansuh, tarikh_pinda, tarikh_serah, kod_jab_asal, kod_jab_baharu, tag_dokumen, tajuk_dok_asal, tajuk_dok_baharu, catatan_dokumen, tanda_hapus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
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
    $stmt->bind_param('siisiiiiiiissssssssiissssi',$_SESSION['tajuk_dok'],$_SESSION['bil_dok'],$_SESSION['tahun_dok'],$_SESSION['des_dok'],$_SESSION['kod_kat'],$_SESSION['kod_sektor'],$_SESSION['kod_bah'],$_SESSION['kod_kem'],$_SESSION['kod_jab'],$_SESSION['kod_status'],$_SESSION['id_pendaftar'],$_SESSION['tarikh_wujud'],$_SESSION['tarikh_dok'],$_SESSION['nama_fail_asal'],$_SESSION['nama_fail_disimpan'],$_SESSION['tarikh_kemaskini'],$_SESSION['tarikh_mansuh'],$_SESSION['tarikh_pinda'],$_SESSION['tarikh_serah'],$_SESSION['kod_jab_asal'],$_SESSION['kod_jab_baharu'],$_SESSION['tag_dokumen'],$_SESSION['tajuk_dok_asal'],$_SESSION['tajuk_dok_baharu'],$_SESSION['catatan_dokumen'], $tanda_hapus);

    $tanda_hapus = 1;
    
    /* Execute statement */
    // $stmt->execute();
    # 20170126 trying this
    // fnRunAlert("Ni sebelum insert dok sokongan.");
    if ($stmt->execute()) {
        // fnRunAlert("Ni selepas execute stmt.");
        $_SESSION['insertOK'] = 1; # tanda rekod berjaya dimasukkan dalam table dokumen
        if ($_SESSION['slot01_OK'] == 1) {
            // fnRunAlert("Ni baru mula if slot01.");
            $_SESSION['mesejBerjaya'] = "Dokumen sokongan berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot01'], $_SESSION['nama_dok_disimpan_slot01']);
            // fnInsertDokSokongan("$_SESSION[nama_dok_asal_slot01]", "$_SESSION[nama_dok_disimpan_slot01]");
            // fnRunAlert("Ni selepas insert dok sokongan slot01.");
        }
        if ($_SESSION['slot02_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot02 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot02'], $_SESSION['nama_dok_disimpan_slot02']);
        }
        if ($_SESSION['slot03_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot03 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot03'], $_SESSION['nama_dok_disimpan_slot03']);
        }
        if ($_SESSION['slot04_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot04 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot04'], $_SESSION['nama_dok_disimpan_slot04']);
        }
        $_SESSION['mesejBerjaya'] = ""; #kosongkan mesej berjaya
    }
    else {
        $_SESSION['insertOK'] = 0;
    }

    $stmt->close();

    $conn->close();
    fnClearSessionNewDoc();
}

function fnInsertNewSupportDoc_v2($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $tanda_hapus = 1;
    
    /* Execute statement */
    // $stmt->execute();
    # 20170126 trying this
    // fnRunAlert("Ni sebelum insert dok sokongan.");
    if ($_SESSION['insertOK'] == 1) {
        // fnRunAlert("Ni selepas execute stmt.");
        // $_SESSION['insertOK'] = 1; # tanda rekod berjaya dimasukkan dalam table dokumen
        if ($_SESSION['slot01_OK'] == 1) {
            // fnRunAlert("Ni baru mula if slot01.");
            $_SESSION['mesejBerjaya'] = "Dokumen sokongan berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot01'], $_SESSION['nama_dok_disimpan_slot01']);
            // fnInsertDokSokongan("$_SESSION[nama_dok_asal_slot01]", "$_SESSION[nama_dok_disimpan_slot01]");
            // fnRunAlert("Ni selepas insert dok sokongan slot01.");
        }
        if ($_SESSION['slot02_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot02 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot02'], $_SESSION['nama_dok_disimpan_slot02']);
        }
        if ($_SESSION['slot03_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot03 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot03'], $_SESSION['nama_dok_disimpan_slot03']);
        }
        if ($_SESSION['slot04_OK'] == 1) {
            $_SESSION['mesejBerjaya'] = "Dokumen Sokongan Slot04 berjaya direkod ke db.";
            fnInsertDokSokongan($_SESSION['nama_dok_asal_slot04'], $_SESSION['nama_dok_disimpan_slot04']);
        }
        $_SESSION['mesejBerjaya'] = ""; #kosongkan mesej berjaya
    }
    else {
        $_SESSION['insertOK'] = 0;
    }

    // $stmt->close();

    $conn->close();
    fnClearSessionNewDoc();
}

function fnUpdateDokSokongan($namaDokAsal,$namaDokDisimpan,$kodDokFK){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
    # operasi insert ke dalam table dok_sokongan. satu untuk setiap dok_sokongan.
    # prepare statement
    // fnRunAlert("Marker 01");
    $dokSokonganSql = "INSERT INTO dok_sokongan (kod_dok_fk, nama_dok_asal, nama_dok_disimpan) VALUES (?, ?, ?)";
    // fnRunAlert("Marker 02");
    $dokSokonganStmt = $conn->prepare($dokSokonganSql);
    # if statement is false
    // fnRunAlert("Marker 03");
    if($dokSokonganStmt === false) {
        trigger_error('Wrong SQL: ' . $dokSokonganSql . ' Error: ' . $conn->error, E_USER_ERROR);
    }
    # bind parameter
    // fnRunAlert("Marker 04");
    $dokSokonganStmt->bind_param('iss', $kod_dok_fk, $nama_dok_asal, $nama_dok_disimpan);
    # kira bil rekod dalam 'dokumen' dan ambil m = jumlah rekod + 1
    # semak sama ada terdapat rekod dengan kod_dok = nilai m
    // fnRunAlert("Marker 05");
    $semakBilDokSql = "SELECT kod_dok FROM dokumen"; // tak perlu semak tanda_hapus sebab nak tahu bilangan semua
    // fnRunAlert("Marker 06");
    $semakBilDokStmt = $conn->query($semakBilDokSql);
    // fnRunAlert("Marker 07");
    if($semakBilDokStmt === false) {
        trigger_error('Wrong SQL: ' . $semakBilDokSql . ' Error: ' . $conn->error, E_USER_ERROR);
    }
    else {
        $rows_returned = $semakBilDokStmt->num_rows;
    }
    $kod_dok_fk = $kodDokFK;
    // fnRunAlert("Marker 08");
    $nama_dok_asal = $namaDokAsal;
    $nama_dok_disimpan = $namaDokDisimpan;
    # execute
    // fnRunAlert("Marker 09");
    if ($dokSokonganStmt->execute()) {
        $_SESSION['insertRekodDokSokonganOK'] = 1; # berjaya
        fnRunAlert("$_SESSION[mesejBerjaya]");
    }
    else {
        $_SESSION['insertRekodDokSokonganOK'] = 0; # gagal
    }
    # close
    $dokSokonganStmt->close();
}

function fnInsertDokSokongan($namaDokAsal,$namaDokDisimpan){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
    # operasi insert ke dalam table dok_sokongan. satu untuk setiap dok_sokongan.
    # prepare statement
    // fnRunAlert("Marker 01");
    $dokSokonganSql = "INSERT INTO dok_sokongan (kod_dok_fk, nama_dok_asal, nama_dok_disimpan) VALUES (?, ?, ?)";
    // fnRunAlert("Marker 02");
    $dokSokonganStmt = $conn->prepare($dokSokonganSql);
    # if statement is false
    // fnRunAlert("Marker 03");
    if($dokSokonganStmt === false) {
        trigger_error('Wrong SQL: ' . $dokSokonganSql . ' Error: ' . $conn->error, E_USER_ERROR);
    }
    # bind parameter
    // fnRunAlert("Marker 04");
    $dokSokonganStmt->bind_param('iss', $kod_dok_fk, $nama_dok_asal, $nama_dok_disimpan);
    # kira bil rekod dalam 'dokumen' dan ambil m = jumlah rekod + 1
    # semak sama ada terdapat rekod dengan kod_dok = nilai m
    // fnRunAlert("Marker 05");
    $semakBilDokSql = "SELECT kod_dok FROM dokumen"; // tak perlu semak tanda_hapus sebab nak tahu bilangan semua
    // fnRunAlert("Marker 06");
    $semakBilDokStmt = $conn->query($semakBilDokSql);
    // fnRunAlert("Marker 07");
    if($semakBilDokStmt === false) {
        trigger_error('Wrong SQL: ' . $semakBilDokSql . ' Error: ' . $conn->error, E_USER_ERROR);
    }
    else {
        $rows_returned = $semakBilDokStmt->num_rows;
        $kod_dok_fk = $rows_returned;
    }
    // fnRunAlert("Marker 08");
    $nama_dok_asal = $namaDokAsal;
    $nama_dok_disimpan = $namaDokDisimpan;
    # execute
    // fnRunAlert("Marker 09");
    if ($dokSokonganStmt->execute()) {
        $_SESSION['insertRekodDokSokonganOK'] = 1; # berjaya
        fnRunAlert("$_SESSION[mesejBerjaya]");
    }
    else {
        $_SESSION['insertRekodDokSokonganOK'] = 0; # gagal
    }
    # close
    $dokSokonganStmt->close();
}

function fnDeleteDoc2(){
    ?>
    <script>
        alert("hello delete");
    </script>
    <?php
}

function fnDeleteDoc(){
    /*--do this if confirm delete doc is pressed... check the session--*/
    // fnRunAlert("The value of confirm delete doc is $_SESSION[value_confirm_delete_doc_pressed].");
    fnRunAlert("here's fnDeleteDoc");
    if ($_SESSION['value_confirm_delete_doc_pressed'] == 1) {
        /*--prepare the document's code--*/
        $kod_dok_to_delete_now = $_SESSION['kod_dok_to_delete_now'];
        /*--connect to database--*/
        $DBServer       = $_SESSION['DBServer'];
        $DBUser         = $_SESSION['DBUser'];
        $DBPass         = $_SESSION['DBPass'];
        $DBName         = $_SESSION['DBName'];
        $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
        /*check connection*/
        if ($conn->connect_error) {
            trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
        }
        /*cari nama fail yang berkaitan dengan dokumen*/
        $getDocFileSql="SELECT nama_dok_disimpan FROM dokumen WHERE kod_dok = '$kod_dok_to_delete_now'";
        $getDocFileResult=$conn->query($getDocFileSql);

        if($getDocFileResult === false) {
            trigger_error('Wrong SQL: ' . $getDocFileSql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $getDocFileArray = $getDocFileResult->fetch_all(MYSQLI_ASSOC);
        }

        foreach($getDocFileArray as $getDocFileRow) {
            $_SESSION['nama_dok_disimpan_utk_dipadam'] = $getDocFileRow['nama_dok_disimpan'];
            $filepath = "../papers/".$_SESSION['nama_dok_disimpan_utk_dipadam'];
        }

        /*jika ada, semak fail wujud atau tidak*/
        if (file_exists($filepath)) {
            /*jika wujud, semak fail adalah fail sebelum padam*/
            if (is_file($filepath))
            {
                /*padamkan fail*/
                unlink($filepath);
            }
        }
        /*delete from dokumen table*/
        $delDokumenSql = "DELETE FROM dokumen WHERE kod_dok = '$kod_dok_to_delete_now'";
        $delDokumenResult = $conn->query($delDokumenSql);
        /*delete from teras_dok table*/
        $delTerasDokSql = "DELETE FROM teras_dok WHERE kod_dok = '$kod_dok_to_delete_now'";
        $delTerasDokResult = $conn->query($delTerasDokSql);
    }
}

function fnClearNewDocForm(){
    $_SESSION['tajuk_dok'] = "";
    $_SESSION['bil_dok'] = "";
    $_SESSION['tahun_dok'] = "";
    $_SESSION['des_dok'] = "";
    $_SESSION['kod_kat'] = "";
    $_SESSION['kod_sektor'] = "";
    $_SESSION['kod_bah'] = "";
    $_SESSION['kod_kem'] = "";
    $_SESSION['kod_jab'] = "";
    $_SESSION['kod_status'] = "";
    $_SESSION['id_pendaftar'] = "";
    $_SESSION['tarikh_wujud'] = "";
    $_SESSION['tarikh_mansuh'] = "";
    $_SESSION['tarikh_pinda'] ="";
    $_SESSION['tajuk_dok_asal'] = "";
    $_SESSION['tajuk_dok_baharu'] = "";
    $_SESSION['tarikh_serah'] = "";
    $_SESSION['kod_jab_asal'] = "";
    $_SESSION['kod_jab_baharu'] = "";
    $_SESSION['tarikh_dok'] = "";
    $_SESSION['tarikh_kemaskini'] = "";
    $_SESSION['tag_dokumen'] = "";
}

# check saved agency codes for duplicates
function fnCheckSavedDivisionCodeToAdd($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_bah = $_SESSION['kod_bah'];
    $nama_bahagian = $_SESSION['nama_bahagian'];
    $kod_jab = $_SESSION['kod_jab'];

    $sql="SELECT * FROM bahagian WHERE kod_bah = '$kod_bah' AND kod_jab = '$kod_jab'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicatedivisioncode'] = 1;
        fnRunAlert("Kod Bahagian telah digunakan.");
    }
    else {
        $_SESSION['duplicatedivisioncode'] = 0;
    }
    $rs->free();
    $conn->close();
}

# check saved agency names for duplicates
function fnCheckSavedDivisionNameToAdd($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_bahagian = $_SESSION['kod_data'];
    $nama_bahagian = $_SESSION['nama_bahagian'];
    $singkatan_bahagian = $_SESSION['singkatan_bahagian'];
    $kod_jab = $_SESSION['kod_jab'];

    $sql="SELECT * FROM bahagian WHERE (nama_bahagian LIKE '$nama_bahagian' OR singkatan_bahagian LIKE '$singkatan_bahagian') AND kod_jab = '$kod_jab' AND kod_bah != '$kod_bahagian'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicatedivisionname'] = 1;
        fnRunAlert("Nama/Singkatan Nama Bahagian telah digunakan.");
    }
    else {
        $_SESSION['duplicatedivisionname'] = 0;
        // fnRunAlert("Maaf, nama dan/atau kata laluan tidak sah ATAU pengguna tidak aktif.");
    }
    $rs->free();
    $conn->close();
}

# check saved agency codes for duplicates
function fnCheckSavedAgencyCodeToAdd($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        $_SESSION['duplicateagencycode'] = 0;
    }
    $rs->free();
    $conn->close();
}

# check saved agency names for duplicates
function fnCheckSavedAgencyNameToAdd($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
        $_SESSION['duplicateagencyname'] = 0;
    }
    $rs->free();
    $conn->close();
}

# check saved docs for duplicates
function fnCheckSavedDoc($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_kat = $_SESSION['kod_kat'];
    $bil_dok = $_SESSION['bil_dok'];
    $tahun_dok = $_SESSION['tahun_dok'];

    # cari nama kategori
    $sql_nama_kat_yg_disemak="SELECT * FROM kategori WHERE kod_kat = '$kod_kat'";
    $rs_nama_kat_yg_disemak=$conn->query($sql_nama_kat_yg_disemak);

    if($rs_nama_kat_yg_disemak === false) {
        trigger_error('Wrong SQL: ' . $sql_nama_kat_yg_disemak . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr_nama_kat_yg_disemak = $rs_nama_kat_yg_disemak->fetch_all(MYSQLI_ASSOC);
    }

    foreach($arr_nama_kat_yg_disemak as $row_nama_kat_yg_disemak) {
        $_SESSION['nama_kat_yg_disemak'] = $row_nama_kat_yg_disemak['nama_kat'];
    }



    $sql="SELECT * FROM dokumen WHERE kod_kat LIKE '$kod_kat' AND bil_dok = '$bil_dok' AND tahun_dok = '$tahun_dok' AND tanda_hapus<>0";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert($rows_returned);
    }

    if ($rows_returned > 0) {
        $_SESSION['duplicatedoc'] = 1;
        fnRunAlert("Dokumen $_SESSION[nama_kat_yg_disemak] $bil_dok/$tahun_dok telah wujud.");
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }
    
    $kod_kat = $_SESSION['kod_kat'];
    $bil_dok = $_SESSION['bil_dok'];
    $tahun_dok = $_SESSION['tahun_dok'];
    $kod_dok_to_be_updated = $_SESSION['kod_dok_to_be_updated'];
    $sql="SELECT * FROM dokumen WHERE kod_kat LIKE '$kod_kat' AND bil_dok = '$bil_dok' AND tahun_dok = '$tahun_dok' AND kod_dok != '$kod_dok_to_be_updated' AND tanda_hapus<>0";

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
    if ($_FILES["nama_dok"]["size"] > 50000000) {
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

function fnUploadFilesUpdateDoc($nama_dok_dari_borang_kemas_kini){
    /* Getting file info and separating the name */
    if (isset($_FILES[$nama_dok_dari_borang_kemas_kini]["name"])) {
        $filename = $_FILES[$nama_dok_dari_borang_kemas_kini]["name"];
    }
    if (isset($filename)) {
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    }
    # Check if any file is uploaded. If none, ok but end if. If not, continue to check.
    // if (empty($file_basename)) {
        // fnRunAlert("Tiada pengemaskinian ke atas fail yang telah dimuatnaik.");
        // $uploadOk = 1;
        // $_SESSION['uploadOk'] = 1;
    // }
    // else {
    // }

    /* File name marker */
    if ($nama_dok_dari_borang_kemas_kini == "nama_dok") {
        $filename_marker = "a";
        // fnRunAlert($nama_dok_dari_borang_kemas_kini." ".$filename_marker);
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_01") {
        $filename_marker = "b";
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_02") {
        $filename_marker = "c";
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_03") {
        $filename_marker = "d";
    }

    # Save uploaded file
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Create new name for file */
    $new_id=$_SESSION['kod_dok_to_be_updated']; // add quote mark for 1. removed quote mark 20161013 1720.
    // fnRunAlert("kod_dok_to_be_updated = ".$new_id);
    $_SESSION['new_doc_id'] = $new_id;
    $new_base_name = "srp_doc"."$new_id"."$filename_marker"; // removed .'_' 20161013 1713
    // fnRunAlert($new_base_name);

    /* Rename file */
    $new_full_file_name = "$new_base_name"."$file_ext"; // added quote marks 20161013 1722.
    $_SESSION['nama_fail_disimpan'] = "$new_full_file_name"; // added this line 20161013 1716.

    /* Setting the target directory */
    $target_dir = "../papers/";
    $full_file_name = basename($_FILES[$nama_dok_dari_borang_kemas_kini]["name"]);
    $_SESSION['nama_fail_asal'] = basename($_FILES[$nama_dok_dari_borang_kemas_kini]["name"]);
    $target_file = "$target_dir" . "$new_full_file_name"; // add quotation marks 20161013 1546. changed full_file_name to new_full_file_name 20161013 1701. removed full_file_name, new_base_name & added new_full_file_name 20161013 1713
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

    /* File names to be saved for each slot */
    if ($nama_dok_dari_borang_kemas_kini == "nama_dok") {
        $_SESSION['nama_dok_asal_slot01'] = "";
        $_SESSION['nama_dok_disimpan_slot01'] = "";
        $_SESSION['nama_dok_asal_slot01'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot01'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_01") {
        $_SESSION['nama_dok_asal_slot02'] = "";
        $_SESSION['nama_dok_disimpan_slot02'] = "";
        $_SESSION['nama_dok_asal_slot02'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot02'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_02") {
        $_SESSION['nama_dok_asal_slot03'] = "";
        $_SESSION['nama_dok_disimpan_slot03'] = "";
        $_SESSION['nama_dok_asal_slot03'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot03'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_03") {
        $_SESSION['nama_dok_asal_slot04'] = "";
        $_SESSION['nama_dok_disimpan_slot04'] = "";
        $_SESSION['nama_dok_asal_slot04'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot04'] = $_SESSION['nama_fail_disimpan'];
    }

    # Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$nama_dok_dari_borang_kemas_kini]["tmp_name"]);
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
        if (!fnRunConfirm("Maaf, fail telah wujud. Teruskan memuat naik?")) {
            $uploadOk = 1;
            $_SESSION['uploadOk'] = $uploadOk;
        }
        else {
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
    }
    # Check file size
    if ($_FILES[$nama_dok_dari_borang_kemas_kini]["size"] > 50000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda melebihi 50MB."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    # Allow certain file formats
    if($imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "tif" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "zip" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PNG, JPG, JPEG, TIF, GIF, PDF, DOC, DOCX atau ZIP sahaja yang dibenarkan."; ?>");
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
        if (move_uploaded_file($_FILES[$nama_dok_dari_borang_kemas_kini]["tmp_name"], "$target_file")) { // add quote marks for target_file 20161013 1550
            ?>
            <script>
                alert("<?php echo "Fail ".basename($_FILES[$nama_dok_dari_borang_kemas_kini]["name"])." telah dimuatnaik sebagai ".$new_full_file_name."."; ?>");
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

function fnUploadFilesUpdateDoc_trial($nama_dok_dari_borang_kemas_kini){
    /* Getting file info and separating the name */
    if (isset($_FILES[$nama_dok_dari_borang_kemas_kini]["name"])) {
        $filename = $_FILES[$nama_dok_dari_borang_kemas_kini]["name"];
    }
    if (isset($filename)) {
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    }
    # Check if any file is uploaded. If none, ok but end if. If not, continue to check.
    // if (empty($file_basename)) {
        // fnRunAlert("Tiada pengemaskinian ke atas fail yang telah dimuatnaik.");
        // $uploadOk = 1;
        // $_SESSION['uploadOk'] = 1;
    // }
    // else {
    // }

    /* File name marker */
    if ($nama_dok_dari_borang_kemas_kini == "nama_dok") {
        $filename_marker = "a";
        // fnRunAlert($nama_dok_dari_borang_kemas_kini." ".$filename_marker);
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_01") {
        $filename_marker = "b";
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_02") {
        $filename_marker = "c";
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_03") {
        $filename_marker = "d";
    }

    # Save uploaded file
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Create new name for file */
    $new_id=$_SESSION['kod_dok_to_be_updated']; // add quote mark for 1. removed quote mark 20161013 1720.
    // fnRunAlert("kod_dok_to_be_updated = ".$new_id);
    $_SESSION['new_doc_id'] = $new_id;
    $new_base_name = "srp_doc"."$new_id"."$filename_marker"; // removed .'_' 20161013 1713
    // fnRunAlert($new_base_name);

    /* Rename file */
    $new_full_file_name = "$new_base_name"."$file_ext"; // added quote marks 20161013 1722.
    $_SESSION['nama_fail_disimpan'] = "$new_full_file_name"; // added this line 20161013 1716.

    /* Setting the target directory */
    $target_dir = "../papers/";
    $full_file_name = basename($_FILES[$nama_dok_dari_borang_kemas_kini]["name"]);
    $_SESSION['nama_fail_asal'] = basename($_FILES[$nama_dok_dari_borang_kemas_kini]["name"]);
    $target_file = "$target_dir" . "$new_full_file_name"; // add quotation marks 20161013 1546. changed full_file_name to new_full_file_name 20161013 1701. removed full_file_name, new_base_name & added new_full_file_name 20161013 1713
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

    /* File names to be saved for each slot */
    if ($nama_dok_dari_borang_kemas_kini == "nama_dok") {
        $_SESSION['nama_dok_asal_slot01'] = "";
        $_SESSION['nama_dok_disimpan_slot01'] = "";
        $_SESSION['nama_dok_asal_slot01'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot01'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_01") {
        $_SESSION['nama_dok_asal_slot02'] = "";
        $_SESSION['nama_dok_disimpan_slot02'] = "";
        $_SESSION['nama_dok_asal_slot02'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot02'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_02") {
        $_SESSION['nama_dok_asal_slot03'] = "";
        $_SESSION['nama_dok_disimpan_slot03'] = "";
        $_SESSION['nama_dok_asal_slot03'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot03'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok_dari_borang_kemas_kini == "nama_dok_03") {
        $_SESSION['nama_dok_asal_slot04'] = "";
        $_SESSION['nama_dok_disimpan_slot04'] = "";
        $_SESSION['nama_dok_asal_slot04'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot04'] = $_SESSION['nama_fail_disimpan'];
    }

    # Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$nama_dok_dari_borang_kemas_kini]["tmp_name"]);
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
        if (!fnRunConfirm("Maaf, fail telah wujud. Teruskan memuat naik?")) {
            $uploadOk = 1;
            $_SESSION['uploadOk'] = $uploadOk;
        }
        else {
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
    }
    # Check file size
    if ($_FILES[$nama_dok_dari_borang_kemas_kini]["size"] > 50000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda melebihi 50MB."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    # Allow certain file formats
    if($imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "tif" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "zip" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PNG, JPG, JPEG, TIF, GIF, PDF, DOC, DOCX atau ZIP sahaja yang dibenarkan."; ?>");
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
        if (move_uploaded_file($_FILES[$nama_dok_dari_borang_kemas_kini]["tmp_name"], "$target_file")) { // add quote marks for target_file 20161013 1550
            ?>
            <script>
                alert("<?php echo "Fail ".basename($_FILES[$nama_dok_dari_borang_kemas_kini]["name"])." telah dimuatnaik sebagai ".$new_full_file_name."."; ?>");
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

function fnUploadFilesUpdateDoc_bak201808021505(){
    /* Getting file info and separating the name */
    if (isset($_FILES["nama_dok"]["name"])) {
        $filename = $_FILES["nama_dok"]["name"];
    }
    if (isset($filename)) {
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    }
    # Check if any file is uploaded. If none, ok but end if. If not, continue to check.
    // if (empty($file_basename)) {
        // fnRunAlert("Tiada pengemaskinian ke atas fail yang telah dimuatnaik.");
        // $uploadOk = 1;
        // $_SESSION['uploadOk'] = 1;
    // }
    // else {
    // }

    # Save uploaded file
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
        if (!fnRunConfirm("Maaf, fail telah wujud. Teruskan memuat naik?")) {
            $uploadOk = 1;
            $_SESSION['uploadOk'] = $uploadOk;
        }
        else {
            $uploadOk = 0;
            $_SESSION['uploadOk'] = 0;
        }
    }
    # Check file size
    if ($_FILES["nama_dok"]["size"] > 50000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda melebihi 50MB."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    # Allow certain file formats
    if($imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "tif" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PNG, JPG, JPEG, TIF, GIF, PDF, DOC atau DOCX sahaja yang dibenarkan."; ?>");
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

function fnUploadFilesRename($nama_dok){
    /* Getting file info and separating the name */
    $filename = $_FILES[$nama_dok]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Find biggest doc id/code */
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);

    /* File name marker */
    if ($nama_dok == "nama_dok") {
        $filename_marker = "a";
    }
    elseif ($nama_dok == "nama_dok_01") {
        $filename_marker = "b";
    }
    elseif ($nama_dok == "nama_dok_02") {
        $filename_marker = "c";
    }
    else {
        $filename_marker = "d";
    }

    /* Create new name for file */
    $new_id=$_SESSION['biggest_doc_id']+1; // add quote mark for 1. removed quote mark 20161013 1720.
    $_SESSION['new_doc_id'] = $new_id;
    // $new_base_name = "srp_doc".$new_id; // removed .'_' 20161013 1713
    $new_base_name = "srp_doc".$new_id."$filename_marker"; // 20180723syedfaizal: tambah marker untuk tanda slot

    /* Rename file */
    $new_full_file_name = "$new_base_name"."$file_ext"; // added quote marks 20161013 1722.
    $_SESSION['nama_fail_disimpan'] = "$new_full_file_name"; // added this line 20161013 1716.
    // echo $_SESSION['nama_fail_disimpan'];

    /* Setting the target directory */
    $target_dir = "../papers/";
    $full_file_name = basename($_FILES[$nama_dok]["name"]);
    $_SESSION['nama_fail_asal'] = basename($_FILES[$nama_dok]["name"]);
    /* File names to be saved for each slot */
    if ($nama_dok == "nama_dok") {
        $_SESSION['nama_dok_asal_slot01'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot01'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok == "nama_dok_01") {
        $_SESSION['nama_dok_asal_slot02'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot02'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok == "nama_dok_02") {
        $_SESSION['nama_dok_asal_slot03'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot03'] = $_SESSION['nama_fail_disimpan'];
    }
    else {
        $_SESSION['nama_dok_asal_slot04'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot04'] = $_SESSION['nama_fail_disimpan'];
    }
    # bila nak padam session ni semua?
    $target_file = "$target_dir" . "$new_full_file_name"; // add quotation marks 20161013 1546. changed full_file_name to new_full_file_name 20161013 1701. removed full_file_name, new_base_name & added new_full_file_name 20161013 1713
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    # Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$nama_dok]["tmp_name"]);
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
    // if (file_exists($target_file)) {
        // fnRunAlert("Maaf, fail telah wujud.");
        // $uploadOk = 0;
        // $_SESSION['uploadOk'] = 0;
    // }

    # Check file size
    if ($_FILES[$nama_dok]["size"] > 50000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda melebihi 50MB."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    # Allow certain file formats
    if($imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "tif" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "zip" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PNG, JPG, JPEG, TIF, GIF, PDF, DOC, DOCX atau ZIP sahaja yang dibenarkan."; ?>");
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
        if (move_uploaded_file($_FILES[$nama_dok]["tmp_name"], "$target_file")) { // add quote marks for target_file 20161013 1550
            ?>
            <script>
                alert("<?php echo "Fail ".basename($_FILES[$nama_dok]["name"])." telah dimuatnaik sebagai ".$new_full_file_name."."; ?>");
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

function fnUploadFilesRename_v2($nama_dok){
    /* Getting file info and separating the name */
    $filename = $_FILES[$nama_dok]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Find biggest doc id/code */
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    fnFindBiggestDocID($DBServer,$DBUser,$DBPass,$DBName);

    /* File name marker */
    if ($nama_dok == "nama_dok") {
        $filename_marker = "a";
    }
    elseif ($nama_dok == "nama_dok_01") {
        $filename_marker = "b";
    }
    elseif ($nama_dok == "nama_dok_02") {
        $filename_marker = "c";
    }
    else {
        $filename_marker = "d";
    }

    /* Get original filename */
    $full_file_name = basename($_FILES[$nama_dok]["name"]);
    // fnRunAlert("Full file name = ".$full_file_name);
    $file_name_extension = end((explode(".", $full_file_name))); # extra () to prevent notice
    $file_name_only = basename($_FILES[$nama_dok]["name"], ".".$file_name_extension);
    // fnRunAlert("File name with no extension = ".$file_name_only);
    $_SESSION['nama_fail_asal'] = basename($_FILES[$nama_dok]["name"]);
    $_SESSION['nama_fail_asal_untuk_nama_baharu'] = $file_name_only;

    /* Create new name for file */
    $new_id=$_SESSION['kod_dok_step2']; // add quote mark for 1. removed quote mark 20161013 1720.
    $_SESSION['new_doc_id'] = $new_id;
    // $new_base_name = "srp_doc".$new_id; // removed .'_' 20161013 1713
    $new_base_name = $new_id."_".$_SESSION['nama_fail_asal_untuk_nama_baharu']; 

    /* Rename file */
    $new_full_file_name = "$new_base_name"."$file_ext"; // added quote marks 20161013 1722.
    $_SESSION['nama_fail_disimpan'] = "$new_full_file_name"; // added this line 20161013 1716.
    // echo $_SESSION['nama_fail_disimpan'];

    /* Setting the target directory */
    $target_dir = "../papers/";
    /* File names to be saved for each slot */
    if ($nama_dok == "nama_dok") {
        $_SESSION['nama_dok_asal_slot01'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot01'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok == "nama_dok_01") {
        $_SESSION['nama_dok_asal_slot02'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot02'] = $_SESSION['nama_fail_disimpan'];
    }
    elseif ($nama_dok == "nama_dok_02") {
        $_SESSION['nama_dok_asal_slot03'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot03'] = $_SESSION['nama_fail_disimpan'];
    }
    else {
        $_SESSION['nama_dok_asal_slot04'] = $_SESSION['nama_fail_asal'];
        $_SESSION['nama_dok_disimpan_slot04'] = $_SESSION['nama_fail_disimpan'];
    }
    # bila nak padam session ni semua?
    $target_file = "$target_dir" . "$new_full_file_name"; // add quotation marks 20161013 1546. changed full_file_name to new_full_file_name 20161013 1701. removed full_file_name, new_base_name & added new_full_file_name 20161013 1713
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    # Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$nama_dok]["tmp_name"]);
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
        fnRunAlert("Maaf, fail telah wujud.");
        if ($nama_dok == "nama_dok") {
            $_SESSION['slot01_OK'] = 0;
        }
        elseif ($nama_dok == "nama_dok_01") {
            $_SESSION['slot02_OK'] = 0;
        }
        elseif ($nama_dok == "nama_dok_02") {
            $_SESSION['slot03_OK'] = 0;
        }
        else {
            $_SESSION['slot04_OK'] = 0;
        }
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }

    # Check file size
    if ($_FILES[$nama_dok]["size"] > 50000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda melebihi 50MB."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    # Allow certain file formats
    if($imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "tif" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" && $imageFileType != "zip" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PNG, JPG, JPEG, TIF, GIF, PDF, DOC, DOCX atau ZIP sahaja yang dibenarkan."; ?>");
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
        if (move_uploaded_file($_FILES[$nama_dok]["tmp_name"], "$target_file")) { // add quote marks for target_file 20161013 1550
            ?>
            <script>
                alert("<?php echo "Fail ".basename($_FILES[$nama_dok]["name"])." telah dimuatnaik sebagai ".$new_full_file_name."."; ?>");
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

function fnUploadFilesRename_bak20180723(){
    /* Getting file info and separating the name */
    $filename = $_FILES["nama_dok"]["name"];
    $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
    $file_ext = substr($filename, strripos($filename, '.')); // get file extension

    /* Find biggest doc id/code */
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
    // if (file_exists($target_file)) {
        // fnRunAlert("Maaf, fail telah wujud.");
        // $uploadOk = 0;
        // $_SESSION['uploadOk'] = 0;
    // }

    // Check file size
    if ($_FILES["nama_dok"]["size"] > 50000000) {
        ?>
        <script>
            alert("<?php echo "Maaf, fail anda melebihi 50MB."; ?>");
        </script>
        <?php
        $uploadOk = 0;
        $_SESSION['uploadOk'] = 0;
    }
    // Allow certain file formats
    if($imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "tif" && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "doc" && $imageFileType != "docx" ) {
        ?>
        <script>
            alert("<?php echo "Maaf, cuma fail PNG, JPG, JPEG, TIF, GIF, PDF, DOC atau DOCX sahaja yang dibenarkan."; ?>");
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

function fnPreUploadFilesRename(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    # reset semua kpd 0
    $_SESSION['slot01_OK'] = 0;
    $_SESSION['slot02_OK'] = 0;
    $_SESSION['slot03_OK'] = 0;
    $_SESSION['slot04_OK'] = 0;
    $bilDokSokUtkMuatNaik = 0;

    if (isset($_FILES["nama_dok"]["name"]) or isset($_FILES['nama_dok']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        // if(!file_exists($_FILES['nama_dok']['tmp_name']["0"])) { // this causes error
        if(!file_exists($_FILES['nama_dok']['tmp_name'])) {
            // fnRunAlert("Slot 1: Tiada fail yang dipilih untuk dimuat naik.");
            // $_SESSION['touploadOK'] = 0;
        }
        else {
            // $_SESSION['touploadOK'] = 1;
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot01_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 1: Tiada fail dipilih untuk muat naik.");
    }

    # slot 2
    if (isset($_FILES["nama_dok_01"]["name"]) or isset($_FILES['nama_dok_01']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_01"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        if(!file_exists($_FILES['nama_dok_01']['tmp_name'])) {
            // fnRunAlert("Slot 2: Tiada fail yang dipilih untuk dimuat naik.");
        }
        else {
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot02_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 2: Tiada fail dipilih untuk muat naik.");
    }

    # slot 3
    if (isset($_FILES["nama_dok_02"]["name"]) or isset($_FILES['nama_dok_02']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_02"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        if(!file_exists($_FILES['nama_dok_02']['tmp_name'])) {
            // fnRunAlert("Slot 3: Tiada fail yang dipilih untuk dimuat naik.");
        }
        else {
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot03_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 3: Tiada fail dipilih untuk muat naik.");
    }

     # slot 4
    if (isset($_FILES["nama_dok_03"]["name"]) or isset($_FILES['nama_dok_03']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_03"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        if(!file_exists($_FILES['nama_dok_03']['tmp_name'])) {
            // fnRunAlert("Slot 4: Tiada fail yang dipilih untuk dimuat naik.");
        }
        else {
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot04_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 4: Tiada fail dipilih untuk muat naik.");
    }
    $_SESSION['bilDokSokUtkMuatNaik'] = $bilDokSokUtkMuatNaik;
    if ($bilDokSokUtkMuatNaik == 0) {
        fnRunAlert("Tiada fail dipilih untuk muat naik.");
        $_SESSION['touploadOK'] = 0;
    }
    else {
        fnRunAlert($bilDokSokUtkMuatNaik." fail telah dipilih untuk dimuat naik.");
        $_SESSION['touploadOK'] = 1;
    }
}

function fnPreUploadFilesRename_v2(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    # reset semua kpd 0
    $_SESSION['slot01_OK'] = 0;
    $_SESSION['slot02_OK'] = 0;
    $_SESSION['slot03_OK'] = 0;
    $_SESSION['slot04_OK'] = 0;
    $bilDokSokUtkMuatNaik = 0;

    if (isset($_FILES["nama_dok"]["name"]) or isset($_FILES['nama_dok']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        // if(!file_exists($_FILES['nama_dok']['tmp_name']["0"])) { // this causes error
        if(!file_exists($_FILES['nama_dok']['tmp_name'])) {
            // fnRunAlert("Slot 1: Tiada fail yang dipilih untuk dimuat naik.");
            // $_SESSION['touploadOK'] = 0;
        }
        else {
            // $_SESSION['touploadOK'] = 1;
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot01_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 1: Tiada fail dipilih untuk muat naik.");
    }

    # slot 2
    if (isset($_FILES["nama_dok_01"]["name"]) or isset($_FILES['nama_dok_01']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_01"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        if(!file_exists($_FILES['nama_dok_01']['tmp_name'])) {
            // fnRunAlert("Slot 2: Tiada fail yang dipilih untuk dimuat naik.");
        }
        else {
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot02_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 2: Tiada fail dipilih untuk muat naik.");
    }

    # slot 3
    if (isset($_FILES["nama_dok_02"]["name"]) or isset($_FILES['nama_dok_02']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_02"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        if(!file_exists($_FILES['nama_dok_02']['tmp_name'])) {
            // fnRunAlert("Slot 3: Tiada fail yang dipilih untuk dimuat naik.");
        }
        else {
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot03_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 3: Tiada fail dipilih untuk muat naik.");
    }

     # slot 4
    if (isset($_FILES["nama_dok_03"]["name"]) or isset($_FILES['nama_dok_03']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_03"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        if(!file_exists($_FILES['nama_dok_03']['tmp_name'])) {
            // fnRunAlert("Slot 4: Tiada fail yang dipilih untuk dimuat naik.");
        }
        else {
            $bilDokSokUtkMuatNaik = $bilDokSokUtkMuatNaik + 1;
            $_SESSION['slot04_OK'] = 1;
        }
    }
    else {
        // fnRunAlert("Slot 4: Tiada fail dipilih untuk muat naik.");
    }
    $_SESSION['bilDokSokUtkMuatNaik'] = $bilDokSokUtkMuatNaik;
    if ($bilDokSokUtkMuatNaik == 0) {
        fnRunAlert("Tiada fail dipilih untuk muat naik.");
        $_SESSION['touploadOK'] = 0;
    }
    else {
        fnRunAlert($bilDokSokUtkMuatNaik." fail telah dipilih untuk dimuat naik.");
        $_SESSION['touploadOK'] = 1;
    }
}

function fnPreUploadFilesRename01(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    if (isset($_FILES["nama_dok_01"]["name"]) or isset($_FILES['nama_dok_01']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_01"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        // if(!file_exists($_FILES['nama_dok_01']['tmp_name']["0"])) { // this causes error
        if(!file_exists($_FILES['nama_dok_01']['tmp_name'])) {
            fnRunAlert("Tiada fail dipilih untuk muat naik.");
            $_SESSION['touploadOK'] = 0;
        }
        else {
            $_SESSION['touploadOK'] = 1;
        }
    }
    else {
        fnRunAlert("Tiada fail dipilih untuk muat naik.");
        $_SESSION['touploadOK'] = 0;
    }
}

function fnPreUploadFilesRename02(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    if (isset($_FILES["nama_dok_02"]["name"]) or isset($_FILES['nama_dok_02']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_02"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        // if(!file_exists($_FILES['nama_dok_02']['tmp_name']["0"])) { // this causes error
        if(!file_exists($_FILES['nama_dok_02']['tmp_name'])) {
            fnRunAlert("Tiada fail dipilih untuk muat naik.");
            $_SESSION['touploadOK'] = 0;
        }
        else {
            $_SESSION['touploadOK'] = 1;
        }
    }
    else {
        fnRunAlert("Tiada fail dipilih untuk muat naik.");
        $_SESSION['touploadOK'] = 0;
    }
}

function fnPreUploadFilesRename03(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    if (isset($_FILES["nama_dok_03"]["name"]) or isset($_FILES['nama_dok_03']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_03"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        // if(!file_exists($_FILES['nama_dok_03']['tmp_name']["0"])) { // this causes error
        if(!file_exists($_FILES['nama_dok_03']['tmp_name'])) {
            fnRunAlert("Tiada fail dipilih untuk muat naik.");
            $_SESSION['touploadOK'] = 0;
        }
        else {
            $_SESSION['touploadOK'] = 1;
        }
    }
    else {
        fnRunAlert("Tiada fail dipilih untuk muat naik.");
        $_SESSION['touploadOK'] = 0;
    }
}

function fnPreUploadFilesRename04(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    if (isset($_FILES["nama_dok_04"]["name"]) or isset($_FILES['nama_dok_04']['tmp_name'])) {
        /* Getting file info and separating the name */
        $filename = $_FILES["nama_dok_04"]["name"];
        $file_basename = substr($filename, 0, strripos($filename, '.')); // get file name
        $file_ext = substr($filename, strripos($filename, '.')); // get file extension


        # Checking if file has been selected for upload
        // if(!file_exists($_FILES['nama_dok_04']['tmp_name']["0"])) { // this causes error
        if(!file_exists($_FILES['nama_dok_04']['tmp_name'])) {
            fnRunAlert("Tiada fail dipilih untuk muat naik.");
            $_SESSION['touploadOK'] = 0;
        }
        else {
            $_SESSION['touploadOK'] = 1;
        }
    }
    else {
        fnRunAlert("Tiada fail dipilih untuk muat naik.");
        $_SESSION['touploadOK'] = 0;
    }
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
    if ($_FILES["nama_dok"]["size"] > 50000000) {
        // echo "Sorry, your file is too large.";
        ?>
        <script>
            alert("Maaf, fail melebihi 50MB.");
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
    $allowed_file_size = 50000000;
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
    elseif ($filesize > $allowed_file_size) // max size 50MB
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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

    # check duplicates
    $sql="SELECT $field02name FROM $table01name WHERE $field02name LIKE '$_SESSION[nama_data]'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
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
    }
    else {
        ?>
        <script>
            alert("Maaf, rekod tidak disimpan kerana telah wujud.");
        </script>
        <?php
    }

    $conn->close();
}

function fnInsertNewMinistry($a,$b,$c,$d,$e,$f,$g){
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

    # check duplicates
    $sql="SELECT $field02name FROM $table01name WHERE $field02name LIKE '$_SESSION[nama_data]' OR $field01name = '$_SESSION[kod_data]'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
        $sql="INSERT INTO $table01name ($field01name, $field02name, papar_data) VALUES (?,?,?)";

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('isi',$_SESSION['kod_data'],$_SESSION['nama_data'],$_SESSION['papar_data']);

        /* Execute statement */
        $stmt->execute();

        ?>
        <script>
            alert("Rekod berjaya disimpan!");
        </script>
        <?php
        $stmt->close();
    }
    else {
        ?>
        <script>
            alert("Maaf, rekod tidak disimpan kerana telah wujud.");
        </script>
        <?php
    }

    $conn->close();
}

function fnInsertNewDivision($a,$b,$c,$d,$e,$f,$g){
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

    $sql="INSERT INTO bahagian (nama_bahagian, kod_jab, tkh_kemaskini, id_pengemaskini, papar_data, singkatan_bahagian) VALUES (?,?,?,?,?,?)";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('sisiis',$_SESSION['nama_bahagian'],$_SESSION['kod_jab'],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data'],$_SESSION['singkatan_bahagian']);

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

    # check duplicates
    $sql="SELECT * FROM jabatan WHERE (nama_jab LIKE '$_SESSION[nama_jab]' OR kod_jab = '$_SESSION[kod_jab]') AND kod_kem = '$_SESSION[kod_kem]'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
        $sql="INSERT INTO jabatan (kod_jab, nama_jab, kod_kem, tkh_kemaskini, id_pengemaskini, papar_data) VALUES (?,?,?,?,?,?)";

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('isisii',$_SESSION['kod_jab'],$_SESSION['nama_jab'],$_SESSION['kod_kem'],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data']);

        /* Execute statement */
        $stmt->execute();

        fnRunAlert("Rekod berjaya disimpan!");

        $stmt->close();
    }
    else {
        fnRunAlert("Maaf, rekod tidak disimpan kerana menyamai rekod lain.");
    }
    $conn->close();
}

function fnUpdateDivision($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $idtoupdate     = $h;
    if (isset($_SESSION['singkatan_bahagian'])) {
        $singkatan_bahagian = $_SESSION['singkatan_bahagian'];
    }

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # check duplicates
    $sql="SELECT nama_bahagian FROM bahagian WHERE $field02name LIKE '$_SESSION[nama_data]' AND $field01name != '$_SESSION[kod_data]'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
    }
    $sql="UPDATE bahagian SET nama_bahagian = ?, tkh_kemaskini = ?, id_pengemaskini = ?, papar_data = ?, singkatan_bahagian = ? WHERE $field01name = ?";

    /* Prepare statement */
    $stmt = $conn->prepare($sql);
    if($stmt === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    }

    /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
    $stmt->bind_param('ssiisi',$_SESSION['nama_bahagian'],$_SESSION['tkh_kemaskini'],$_SESSION['id_pengemaskini'],$_SESSION['papar_data'],$singkatan_bahagian,$idtoupdate);

    /* Execute statement */
    $stmt->execute();
    if ($stmt) {
        fnRunalert("Rekod BERJAYA dikemaskini!");
    }

    $stmt->close();
    $conn->close();
}

function fnUpdateData($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $idtoupdate     = $h;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # check duplicates
    $sql="SELECT $field02name FROM $table01name WHERE $field02name LIKE '$_SESSION[nama_data]' AND $field01name != '$_SESSION[kod_data]'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
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

        fnRunAlert("Rekod berjaya dikemaskini!");

        $stmt->close();
    }
    else {
        fnRunAlert("Maaf, rekod tidak dikemaskini kerana akan menyamai rekod lain.");
    }
    $conn->close();
}

function fnUpdateMinistry($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $idtoupdate     = $h;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    # check duplicates
    $sql="SELECT $field02name FROM $table01name WHERE $field02name LIKE '$_SESSION[nama_data]' AND $field01name != '$_SESSION[kod_data]'";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    if ($rows_returned == 0) {
        $sql="UPDATE $table01name SET $field02name = ?, papar_data = ? WHERE $field01name = ?";

        /* Prepare statement */
        $stmt = $conn->prepare($sql);
        if($stmt === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        }

        /* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
        $stmt->bind_param('sii',$_SESSION['nama_data'],$_SESSION['papar_data'],$idtoupdate);

        /* Execute statement */
        $stmt->execute();

        fnRunAlert("Rekod berjaya dikemaskini!");

        $stmt->close();
    }
    else {
        fnRunAlert("Maaf, rekod tidak dikemaskini kerana akan menyamai rekod lain.");
    }
    $conn->close();
}

function fnShowDocReportAllContent($a,$b,$c,$d,$e,$f,$g){
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
                
            </td>
            <td style='align-content: center;' align='center'>
                
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

function fnShowDocReportSelectYear(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql_tahun="SELECT DISTINCT tahun_dok FROM dokumen WHERE (tahun_dok LIKE '%$doclist_search_keyword%') AND tanda_hapus = 1 ORDER BY tahun_dok DESC";
    }
    else {
        // fnRunAlert("Bukan Carian");
        // $sql="SELECT DISTINCT tahun_dok FROM dokumen WHERE tanda_hapus = 1 ORDER BY tahun_dok DESC";
        $sql_tahun="SELECT DISTINCT tahun_dok FROM dokumen WHERE tanda_hapus = 1 ORDER BY tahun_dok DESC";
    }

    $rs=$conn->query($sql_tahun);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql_tahun . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        // fnRunAlert("Bil. Tahun = ".$rows_returned);
    }

    if ($rows_returned == 0) {
        echo "
        <tr>
            <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
        </tr>
        ";
    }
    
    if ($rows_returned%10 == 0) {
        $_SESSION['numPerPage'] = 11;
    }
    else {
        $_SESSION['numPerPage'] = 10;
    }
    $rs->free();

    ?>
    <?php  
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
    $rs_tahun=$conn->query($sql_tahun);

    if($rs_tahun === false) {
        trigger_error('Wrong SQL: ' . $sql_tahun . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr_tahun = $rs_tahun->fetch_all(MYSQLI_ASSOC);
    }
    $counter = 1;
    $_SESSION['jum_semua_kat'] = 0;
    foreach($arr_tahun as $row_tahun) {
        $tahun_rekod = stripslashes(strtoupper($row_tahun['tahun_dok']));
        $sql_tahun_ini="SELECT tahun_dok FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok = $tahun_rekod ORDER BY tahun_dok DESC";

        $DBServer       = $_SESSION['DBServer'];
        $DBUser         = $_SESSION['DBUser'];
        $DBPass         = $_SESSION['DBPass'];
        $DBName         = $_SESSION['DBName'];
        $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
        $rs_tahun_ini=$conn->query($sql_tahun_ini);

        if($rs_tahun_ini === false) {
            trigger_error('Wrong SQL: ' . $sql_tahun_ini . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned_tahun_ini = $rs_tahun_ini->num_rows;
            $_SESSION['bil_rekod_tahun_ini'] = $rows_returned_tahun_ini;
            $_SESSION['jum_semua_kat'] = $_SESSION['jum_semua_kat'] + $rows_returned_tahun_ini;
            // fnRunAlert("Bil. Tahun Ini = ".$rows_returned_tahun_ini);
        }
        ?>
        <!-- <tr>
            <td align="right"><?php echo $counter; ?>.</td>
            <td hidden><?php //echo $row_tahun[$field01name]; ?></td>
            <td>
                <a href='listdocfromreport.php?s=l&j=t&tahun_dok=<?php echo $row_tahun['tahun_dok']; ?>'>
                    <?php echo stripslashes(strtoupper($row_tahun['tahun_dok'])); ?>
                </a>
            </td>
            <td hidden></td>
            <td style='align-content: center;' align='center'>
                <?php echo $_SESSION['bil_rekod_tahun_ini']; ?>
            </td>
        </tr> -->
        <tr>
            <td align="right"><?php echo $counter; ?>.</td>
            <td hidden><?php //echo $row_tahun[$field01name]; ?></td>
            <td>
                <a href='listdocfromreport.php?s=l&j=t&tahun_dok=<?php echo $row_tahun['tahun_dok']; ?>'>
                    <?php echo stripslashes(strtoupper($row_tahun['tahun_dok'])); ?>
                </a>
            </td>
            <td hidden></td>
            <td style='align-content: center;' align='center'>
                <?php echo $_SESSION['bil_rekod_tahun_ini']; ?>
            </td>
        </tr>
        <?php
        $counter++;
        // fnRunAlert($counter);
        $rs_tahun_ini->free();
    }
    ?>
    <?php
    $rs_tahun->free();
    $conn->close();
}

function fnShowDocReportSelectYear_bak20180811($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok FROM dokumen WHERE (tajuk_dok LIKE '%$doclist_search_keyword%' OR tahun_dok LIKE '%$doclist_search_keyword%' OR bil_dok LIKE '%$doclist_search_keyword%') ORDER BY tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    else {
        // fnRunAlert("Bukan Carian");
        // $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, kod_kat FROM dokumen ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
        $sql="SELECT DISTINCT tahun_dok FROM dokumen WHERE tanda_hapus = 1 ORDER BY tahun_dok DESC";
        // $sql_tahun="SELECT DISTINCT tahun_dok FROM dokumen ORDER BY tahun_dok DESC";
    }
    $sql_tahun="SELECT DISTINCT tahun_dok FROM dokumen WHERE tanda_hapus = 1 ORDER BY tahun_dok DESC";

    $rs=$conn->query($sql_tahun);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql_tahun . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        fnRunAlert("Bil. Tahun = ".$rows_returned);
    }

    if ($rows_returned == 0) {
        echo "
        <tr>
            <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
        </tr>
        ";
    }
    
    if ($rows_returned%10 == 0) {
        $_SESSION['numPerPage'] = 11;
    }
    else {
        $_SESSION['numPerPage'] = 10;
    }

    ?>
    <!-- <form id="frmPaparIkutTahun" name="frmPaparIkutTahun" action="../docsmgmt/listdocfromreport.php?s=l&j=t" method="GET" target="_new"> -->
    <!-- <form id="frmPaparIkutTahun" name="frmPaparIkutTahun" action="listdocfromreport.php?s=l&j=t" method="GET" target="_new" enctype="multipart/form-data"> -->
    <!-- <div class="col-md-12 col-sm-12 col-xs-12 form-group text-center"> -->
        <!-- <form action="docreport_cat.php?s=n" method="GET"> -->
            <!-- <select name="tahun_dok" id="tahun_dok"> -->
                <?php  
                $rs=$conn->query($sql_tahun);

                if($rs === false) {
                    trigger_error('Wrong SQL: ' . $sql_tahun . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr = $rs->fetch_all(MYSQLI_ASSOC);
                }
                $counter = 1;
                foreach($arr as $row_tahun) {
                    $tahun_rekod = stripslashes(strtoupper($row_tahun['tahun_dok']));
                    $sql_tahun_ini="SELECT tahun_dok FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok = $tahun_rekod ORDER BY tahun_dok DESC";

                    $rs_tahun_ini=$conn->query($sql_tahun_ini);

                    if($rs_tahun_ini === false) {
                        trigger_error('Wrong SQL: ' . $sql_tahun_ini . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $rows_returned_tahun_ini = $rs_tahun_ini->num_rows;
                        // fnRunAlert("Bil. Tahun Ini = ".$rows_returned_tahun_ini);
                    }
                    ?>
                    <!-- <option value="<?php echo $tahun_rekod; ?>"><?php echo "&nbsp;&nbsp;".$tahun_rekod."&nbsp;"; ?></option> -->
                    <?php
                    echo "
                    <tr>
                        <td>".$counter.".</td>
                        <td hidden>".$row_tahun[$field01name]."</td>
                        <td><a href='listdocfromreport.php?s=l&j=k&c=$row_tahun[tahun_dok]'>".stripslashes(strtoupper($row_tahun['tahun_dok']))."</a></td>
                        <td style='align-content: center;' align='center'>
                            ".$rows_returned_tahun_ini."
                        </td>
                    </tr>
                    ";
                    $counter++;
                }
                ?>
            <!-- </select> -->
            <!-- <input type="submit" id="btnPaparIkutTahun" name="btnPaparIkutTahun" value="Paparkan Rekod" class="btn btn-default"> -->
        <!-- </form> -->
    <!-- </div> -->
    <?php

    $rs->free();
    $conn->close();
}

function fnShowDocReportByCat($a,$b,$c,$d,$e,$f,$g){
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

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT * FROM kategori WHERE nama_kat LIKE '%$doclist_search_keyword%' AND kod_kat != 1 AND papar_data = 1 ORDER BY nama_kat ASC";
    }
    else {
        $sql="SELECT kod_kat, nama_kat FROM kategori WHERE kod_kat != 1 AND papar_data = 1 ORDER BY nama_kat ASC";
    }
    // $sql="SELECT kod_kat, nama_kat FROM kategori WHERE kod_kat != 1 ORDER BY nama_kat ASC";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    /*to fix pagination problem 20180420 syedfaizal*/
    if ($rows_returned%10 == 0) {
        $_SESSION['numPerPage'] = 11;
    }
    else {
        $_SESSION['numPerPage'] = 10;
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
    $jum_semua_kat = 0;
    foreach($arr as $row) {
        $kod_kat = $row['kod_kat'];
        $sql="SELECT kod_kat FROM dokumen WHERE kod_kat = $kod_kat AND tanda_hapus=1";
        $rs=$conn->query($sql);
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
            $jum_kat_ini = $rows_returned;
            $jum_semua_kat = $jum_semua_kat + $jum_kat_ini;
        }

        echo "
        <tr>
            <td>".$counter.".</td>
            <td hidden>".$row[$field01name]."</td>
            <td><a href='listdocfromreport.php?s=l&j=k&c=$row[kod_kat]'>".stripslashes(strtoupper($row['nama_kat']))."</a></td>
            <td style='align-content: center;' align='center'>
                ".$jum_kat_ini."
            </td>
        </tr>
        ";
        $counter++;
    }
    $_SESSION['jum_semua_kat'] = $jum_semua_kat;
    ?>
    <?php
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowDocReportByDiv($a,$b,$c,$d,$e,$f,$g){
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

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT * FROM bahagian WHERE nama_bahagian LIKE '%$doclist_search_keyword%' AND kod_bah != 1 AND papar_data = 1 ORDER BY nama_bahagian ASC";
    }
    else {
        $sql="SELECT * FROM bahagian WHERE kod_bah != 1 AND papar_data = 1 ORDER BY nama_bahagian ASC";
    }
    // $sql="SELECT kod_kat, nama_kat FROM kategori WHERE kod_kat != 1 ORDER BY nama_kat ASC";

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }

    /*to fix pagination problem 20180420 syedfaizal*/
    if ($rows_returned%10 == 0) {
        $_SESSION['numPerPage'] = 11;
    }
    else {
        $_SESSION['numPerPage'] = 10;
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
    $jum_semua_bah = 0;
    foreach($arr as $row) {
        $kod_bah = $row['kod_bah'];
        $sql="SELECT kod_bah FROM dokumen WHERE kod_bah = $kod_bah AND tanda_hapus=1";
        $rs=$conn->query($sql);
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
            $jum_bah_ini = $rows_returned;
            $jum_semua_bah = $jum_semua_bah + $jum_bah_ini;
        }

        echo "
        <tr>
            <td>".$counter.".</td>
            <td hidden>".$row[$field01name]."</td>
            <td><a href='listdocfromreport.php?s=l&j=b&c=$row[kod_bah]'>".stripslashes(strtoupper($row['nama_bahagian']))."</a></td>
            <td style='align-content: center;' align='center'>
                ".$jum_bah_ini."
            </td>
        </tr>
        ";
        $counter++;
    }
    $_SESSION['jum_semua_bah'] = $jum_semua_bah;
    ?>
    <?php
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowDocReportBySec($a,$b,$c,$d,$e,$f,$g){
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

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT * FROM sektor WHERE nama_sektor LIKE '%$doclist_search_keyword%' AND kod_sektor != 1 AND papar_data = 1 ORDER BY nama_sektor ASC";
    }
    else {
        $sql="SELECT * FROM sektor WHERE kod_sektor != 1 AND papar_data = 1 ORDER BY nama_sektor ASC";
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
    $jum_semua_sek = 0;
    foreach($arr as $row) {
        $kod_sektor = $row['kod_sektor'];
        $sql="SELECT kod_sektor FROM dokumen WHERE kod_sektor = $kod_sektor AND tanda_hapus=1";
        $rs=$conn->query($sql);
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
            $jum_sek_ini = $rows_returned;
            $jum_semua_sek = $jum_semua_sek + $jum_sek_ini;
        }

        echo "
        <tr>
            <td>".$counter.".</td>
            <td hidden>".$row['kod_sektor']."</td>
            <td><a href='listdocfromreport.php?s=l&j=e&c=$row[kod_sektor]'>".stripslashes(strtoupper($row['nama_sektor']))."</a></td>
            <td style='align-content: center;' align='center'>
                ".$jum_sek_ini."
            </td>
        </tr>
        ";
        $counter++;
    }
    $_SESSION['jum_semua_sek'] = $jum_semua_sek;
    ?>
    <?php
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowDocReportByStat($a,$b,$c,$d,$e,$f,$g){
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

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT * FROM status WHERE nama_status LIKE '%$doclist_search_keyword%' AND kod_status != 1 AND papar_data = 1 ORDER BY nama_status ASC";
    }
    else {
        $sql="SELECT * FROM status WHERE kod_status != 1 AND papar_data = 1 ORDER BY nama_status ASC";
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
    $jum_semua_stat = 0;
    foreach($arr as $row) {
        $kod_status = $row['kod_status'];
        $sql="SELECT kod_status FROM dokumen WHERE kod_status = $kod_status AND tanda_hapus=1";
        $rs=$conn->query($sql);
        if($rs === false) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
        } else {
            $rows_returned = $rs->num_rows;
            $jum_stat_ini = $rows_returned;
            $jum_semua_stat = $jum_semua_stat + $jum_stat_ini;
        }

        echo "
        <tr>
            <td>".$counter.".</td>
            <td hidden>".$row['kod_status']."</td>
            <td><a href='listdocfromreport.php?s=l&j=s&c=$row[kod_status]'>".stripslashes(strtoupper($row['nama_status']))."</a></td>
            <td style='align-content: center;' align='center'>
                ".$jum_stat_ini."
            </td>
        </tr>
        ";
        $counter++;
    }
    $_SESSION['jum_semua_stat'] = $jum_semua_stat;
    ?>
    <?php
    ?>
    <?php

    $rs->free();
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

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, des_dok, tag_dokumen, nama_kat, tarikh_wujud, nama_status FROM dokumen, kategori, status WHERE (tajuk_dok LIKE '%$doclist_search_keyword%' OR tahun_dok LIKE '%$doclist_search_keyword%' OR bil_dok LIKE '%$doclist_search_keyword%' OR des_dok LIKE '%$doclist_search_keyword%' OR tag_dokumen LIKE '%$doclist_search_keyword%') AND tanda_hapus = 1 AND dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status ORDER BY tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    else {
        // $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, kod_kat FROM dokumen ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
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
        if ($row['bil_dok']==0) {
            $perkataanBil = "";
            $strokeBil = "";
            $bilDok = "";
        }
        else { # jika ada Bil. 
            $perkataanBil = "BIL.";
            $strokeBil = "/";
            $bilDok = $row['bil_dok'];
        }
        $_SESSION['kod_dok_to_delete'] = $row['kod_dok'];
        $kod_dok_ini = $row['kod_dok'];
        ?>
        <tr><!-- jadual senarai dokumen -->
            <td><?php echo $counter ?></td>
            <td hidden><?php echo $row[$field01name] ?></td>
            <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
            <td style='align-content: center;' align='center'>
                <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                <button hidden type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                <?php 
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                    ?>
                    <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=l" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php  
        $tarikh_wujud_rekod = stripslashes(strtoupper($row['tarikh_wujud']));
        $status_rekod = stripslashes(strtoupper($row['nama_status']));
        if ($kod_status_dipilih == 4) {
            $nama_jab_baharu = " ".stripslashes(strtoupper($row['nama_jab']));
        }
        else {
            $nama_jab_baharu = "";
        }
        if ($kod_status_dipilih == 5) {
            $tajuk_dok_baharu = " ".stripslashes(strtoupper($row['tajuk_dok_baharu']));
        }
        else {
            $tajuk_dok_baharu = "";
        }

        ?>
        <tr>
            <td colspan="4">
                <strong><small>Tajuk: </small></strong><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?><br/>
                <strong><small>Kategori: </small></strong><?php echo stripslashes(strtoupper($row['nama_kat'])) ?><br/>
                <strong><small>Bil. Dokumen: </small></strong><?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?><br/>
                <strong><small>Tarikh Berkuatkuasa: </small></strong><?php echo $tarikh_wujud_rekod; ?><br/>
                <strong><small>Status: </small></strong><?php echo $status_rekod.$nama_jab_baharu.$tajuk_dok_baharu; ?><br/>
                <strong><small>Teras: </small></strong>
                <?php
                $sql_teras = "SELECT nama_teras FROM teras_dok, teras_strategik WHERE teras_dok.kod_dok = '$kod_dok_ini' AND teras_dok.kod_teras = teras_strategik.kod_teras AND teras_dok.checked_value = 1 ORDER BY teras_index ASC";  

                $rs_teras=$conn->query($sql_teras);

                if($rs_teras === false) {
                    trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_teras->num_rows;
                }

                $jum_teras = $rows_returned;

                $rs_teras=$conn->query($sql_teras);

                if($rs_teras === false) {
                    trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_teras = $rs_teras->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_teras = 1;
                foreach($arr_teras as $row_teras) {
                    if ($pembilang_teras > 1 AND $pembilang_teras < $jum_teras) {
                        echo stripslashes(strtoupper(", "));
                    }
                    elseif ($pembilang_teras == $jum_teras AND $jum_teras <> 1) {
                        echo stripslashes(strtoupper(", DAN "));
                    }
                    elseif ($jum_teras == 1) {
                        echo stripslashes(strtoupper(""));
                    }
                    echo stripslashes(strtoupper($pembilang_teras.". ".$row_teras['nama_teras']));
                    $pembilang_teras++;
                }
                ?>
                <br/>
                <strong><small>Deskripsi: </small></strong><div class="report-list-desc"><?php echo stripslashes(strtoupper($row['des_dok'])) ?></div><br/>
                <strong><small>Dokumen: </small></strong>
                <?php
                $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                $rs_dok=$conn->query($sql_dok);

                if($rs_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_dok->num_rows;
                }

                $jum_dok = $rows_returned;

                $rs_dok=$conn->query($sql_dok);

                if($rs_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_dok = 1;
                foreach($arr_dok as $row_teras) {
                    if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                        echo stripslashes(strtoupper(", "));
                    }
                    elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                        echo stripslashes(strtoupper(", DAN "));
                    }
                    echo stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal'])); // nama fail
                    echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' target='_blank'> <i class='fa fa-eye'></i></a>"; // ikon papar
                    echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'> <i class='fa fa-download'></i></a>"; // ikon muat turun
                    // echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']))."</a>";
                    $pembilang_dok++;
                }
                ?>
                <br/>
            &nbsp;
            </td>
        </tr>
        <?php
        $counter++;
    }

    $rs->free();
    $conn->close();
}

function fnShowDocTableContentOriginal($a,$b,$c,$d,$e,$f,$g){
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

    if (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, des_dok, tag_dokumen, nama_kat, tarikh_wujud, nama_status FROM dokumen, kategori, status WHERE (tajuk_dok LIKE '%$doclist_search_keyword%' OR tahun_dok LIKE '%$doclist_search_keyword%' OR bil_dok LIKE '%$doclist_search_keyword%' OR des_dok LIKE '%$doclist_search_keyword%' OR tag_dokumen LIKE '%$doclist_search_keyword%') AND tanda_hapus = 1 AND dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status ORDER BY tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    else {
        // $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, kod_kat FROM dokumen ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
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
        if ($row['bil_dok']==0) {
            $perkataanBil = "";
            $strokeBil = "";
            $bilDok = "";
        }
        else { # jika ada Bil. 
            $perkataanBil = "BIL.";
            $strokeBil = "/";
            $bilDok = $row['bil_dok'];
        }
        $_SESSION['kod_dok_to_delete'] = $row['kod_dok'];
        $kod_dok_ini = $row['kod_dok'];
        ?>
        <tr><!-- jadual senarai dokumen -->
            <td><?php echo $counter ?></td>
            <td hidden><?php echo $row[$field01name] ?></td>
            <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
            <td style='align-content: center;' align='center'>
                <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                <?php 
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                    ?>
                    <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=l" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php  
        $tarikh_wujud_rekod = stripslashes(strtoupper($row['tarikh_wujud']));
        $status_rekod = stripslashes(strtoupper($row['nama_status']));
        if ($kod_status_dipilih == 4) {
            $nama_jab_baharu = " ".stripslashes(strtoupper($row['nama_jab']));
        }
        else {
            $nama_jab_baharu = "";
        }
        if ($kod_status_dipilih == 5) {
            $tajuk_dok_baharu = " ".stripslashes(strtoupper($row['tajuk_dok_baharu']));
        }
        else {
            $tajuk_dok_baharu = "";
        }

        ?>
        <tr hidden>
            <td hidden colspan="4">
                <strong><small>Tajuk: </small></strong><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?><br/>
                <strong><small>Kategori: </small></strong><?php echo stripslashes(strtoupper($row['nama_kat'])) ?><br/>
                <strong><small>Bil. Dokumen: </small></strong><?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?><br/>
                <strong><small>Tarikh Berkuatkuasa: </small></strong><?php echo $tarikh_wujud_rekod; ?><br/>
                <strong><small>Status: </small></strong><?php echo $status_rekod.$nama_jab_baharu.$tajuk_dok_baharu; ?><br/>
                <strong><small>Teras: </small></strong>
                <?php
                $sql_teras = "SELECT nama_teras FROM teras_dok, teras_strategik WHERE teras_dok.kod_dok = '$kod_dok_ini' AND teras_dok.kod_teras = teras_strategik.kod_teras AND teras_dok.checked_value = 1 ORDER BY teras_index ASC";  

                $rs_teras=$conn->query($sql_teras);

                if($rs_teras === false) {
                    trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_teras->num_rows;
                }

                $jum_teras = $rows_returned;

                $rs_teras=$conn->query($sql_teras);

                if($rs_teras === false) {
                    trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_teras = $rs_teras->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_teras = 1;
                foreach($arr_teras as $row_teras) {
                    if ($pembilang_teras > 1 AND $pembilang_teras < $jum_teras) {
                        echo stripslashes(strtoupper(", "));
                    }
                    elseif ($pembilang_teras == $jum_teras AND $jum_teras <> 1) {
                        echo stripslashes(strtoupper(", DAN "));
                    }
                    elseif ($jum_teras == 1) {
                        echo stripslashes(strtoupper(""));
                    }
                    echo stripslashes(strtoupper($pembilang_teras.". ".$row_teras['nama_teras']));
                    $pembilang_teras++;
                }
                ?>
                <br/>
                <strong><small>Deskripsi: </small></strong><div class="report-list-desc"><?php echo stripslashes(strtoupper($row['des_dok'])) ?></div><br/>
                <strong><small>Dokumen: </small></strong>
                <?php
                $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                $rs_dok=$conn->query($sql_dok);

                if($rs_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_dok->num_rows;
                }

                $jum_dok = $rows_returned;

                $rs_dok=$conn->query($sql_dok);

                if($rs_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_dok = 1;
                foreach($arr_dok as $row_teras) {
                    if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                        echo stripslashes(strtoupper(", "));
                    }
                    elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                        echo stripslashes(strtoupper(", DAN "));
                    }
                    echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']))."</a>";
                    $pembilang_dok++;
                }
                ?>
                <br/>
            &nbsp;
            </td>
        </tr>
        <?php
        $counter++;
    }

    $rs->free();
    $conn->close();
}

function fnShowDocTableContent_bak201808021139($a,$b,$c,$d,$e,$f,$g){
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
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, des_dok, tag_dokumen FROM dokumen WHERE (tajuk_dok LIKE '%$doclist_search_keyword%' OR tahun_dok LIKE '%$doclist_search_keyword%' OR bil_dok LIKE '%$doclist_search_keyword%' OR des_dok LIKE '%$doclist_search_keyword%' OR tag_dokumen LIKE '%$doclist_search_keyword%') AND tanda_hapus = 1 ORDER BY tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
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
        if ($row['bil_dok']==0) {
            $perkataanBil = "";
            $strokeBil = "";
            $bilDok = "";
        }
        else { # jika ada Bil. 
            $perkataanBil = "BIL.";
            $strokeBil = "/";
            $bilDok = $row['bil_dok'];
        }
        $_SESSION['kod_dok_to_delete'] = $row['kod_dok'];
        ?>
        <tr><!-- jadual senarai dokumen -->
            <td><?php echo $counter ?></td>
            <td hidden><?php echo $row[$field01name] ?></td>
            <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
            <td style='align-content: center;' align='center'>
                <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                <?php 
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                    ?>
                    <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=l" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
        $counter++;
    }

    $rs->free();
    $conn->close();
}

function fnClearSessionForListFromReport(){
    $_SESSION['sumber_senarai'] = "";
    $_SESSION['jenis_senarai'] = "";
    $_SESSION['kod_senarai'] = "";
}

function fnShowDocTableContentNewStyle($a,$b,$c,$d,$e,$f,$g){
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
    // fnRunAlert("fnShowDocTableContentNewStyle");
    if (isset($_SESSION['jenis_senarai']) AND $_SESSION['jenis_senarai'] == "k" AND $_SESSION['sumber_senarai'] == "l") {
        // fnRunAlert("Laporan Kategori");
        $kod_kat_dipilih = $_SESSION['kod_senarai'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 AND dokumen.kod_kat = $kod_kat_dipilih ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    elseif (isset($_SESSION['jenis_senarai']) AND $_SESSION['jenis_senarai'] == "b" AND $_SESSION['sumber_senarai'] == "l") {
        // fnRunAlert("Laporan Bahagian");
        $kod_bah_dipilih = $_SESSION['kod_senarai'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 AND dokumen.kod_bah = $kod_bah_dipilih ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    elseif (isset($_SESSION['jenis_senarai']) AND $_SESSION['jenis_senarai'] == "e" AND $_SESSION['sumber_senarai'] == "l") {
        // fnRunAlert("Laporan Sektor");
        $kod_sektor_dipilih = $_SESSION['kod_senarai'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 AND dokumen.kod_sektor = $kod_sektor_dipilih ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    elseif (isset($_SESSION['jenis_senarai']) AND $_SESSION['jenis_senarai'] == "s" AND $_SESSION['sumber_senarai'] == "l") {
        // fnRunAlert("Laporan Status");
        $kod_status_dipilih = $_SESSION['kod_senarai'];
        if ($kod_status_dipilih == 4) {
            $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok, nama_jab, tajuk_dok_baharu FROM dokumen, kategori, status, jabatan WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 AND dokumen.kod_status = $kod_status_dipilih AND dokumen.kod_jab_baharu = jabatan.kod_jab ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
        }
        else {
            $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok, tajuk_dok_baharu FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 AND dokumen.kod_status = $kod_status_dipilih ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
        }
    }
    elseif (isset($_SESSION['jenis_senarai']) AND $_SESSION['jenis_senarai'] == "t" AND $_SESSION['sumber_senarai'] == "l") {
        // fnRunAlert("Laporan Tahun");
        // fnRunAlert($_SESSION['kod_senarai']);
        $kod_tahun_dipilih = $_SESSION['kod_senarai'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 AND dokumen.tahun_dok = $kod_tahun_dipilih ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
    elseif (isset($_SESSION['doclist_search_keyword']) AND $_SESSION['doclist_search_keyword'] != "") {
        // fnRunAlert("Dengan carian");
        $doclist_search_keyword = $_SESSION['doclist_search_keyword'];
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, des_dok, tag_dokumen, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE (tajuk_dok LIKE '%$doclist_search_keyword%' OR tahun_dok LIKE '%$doclist_search_keyword%' OR bil_dok LIKE '%$doclist_search_keyword%' OR des_dok LIKE '%$doclist_search_keyword%' OR tag_dokumen LIKE '%$doclist_search_keyword%') AND dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 ORDER BY tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }
        
    else {
        // fnRunAlert("Tiada carian atau jenis");
        $sql="SELECT kod_dok, tajuk_dok, bil_dok, tahun_dok, dokumen.kod_kat, nama_kat, tarikh_wujud, nama_status, des_dok FROM dokumen, kategori, status WHERE dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status AND tanda_hapus = 1 ORDER BY kod_kat ASC, tahun_dok DESC, bil_dok ASC, tajuk_dok ASC";
    }

    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
    }
    $_SESSION['jum_rekod_ditemui_utk_jenis_ini'] = $rows_returned;

    if ($rows_returned == 0) {
        echo "
        <tr>
            <td colspan='4' align='center'><h2>Tiada rekod.</h2></td>
        </tr>
        ";
    }
    // fnRunAlert($sql);
    $rs=$conn->query($sql);

    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $arr = $rs->fetch_all(MYSQLI_ASSOC);
    }
    $counter = 1;
    foreach($arr as $row) {
        if ($row['bil_dok']==0) {
            $perkataanBil = "";
            $strokeBil = "";
            $bilDok = "";
        }
        else { # jika ada Bil. 
            $perkataanBil = "BIL.";
            $strokeBil = "/";
            $bilDok = $row['bil_dok'];
        }
        $_SESSION['kod_dok_to_delete'] = $row['kod_dok'];
        $kod_dok_ini = $row['kod_dok'];
        ?>
        <tr><!-- jadual senarai dokumen -->
            <td><?php echo $counter ?></td>
            <td hidden><?php echo $row[$field01name] ?></td>
            <td colspan="2"><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
            <td style='align-content: center;' align='center' hidden><!-- hilangkan untuk senarai ini -->
                <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                <?php 
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                    ?>
                    <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=l" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php  
        $tarikh_wujud_rekod = stripslashes(strtoupper($row['tarikh_wujud']));
        $status_rekod = stripslashes(strtoupper($row['nama_status']));
        if ($kod_status_dipilih == 4) {
            $nama_jab_baharu = " ".stripslashes(strtoupper($row['nama_jab']));
        }
        else {
            $nama_jab_baharu = "";
        }
        if ($kod_status_dipilih == 5) {
            $tajuk_dok_baharu = " ".stripslashes(strtoupper($row['tajuk_dok_baharu']));
        }
        else {
            $tajuk_dok_baharu = "";
        }

        ?>
        <tr>
            <td colspan="4">
                <strong><small>Tajuk: </small></strong><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?><br/>
                <strong><small>Kategori: </small></strong><?php echo stripslashes(strtoupper($row['nama_kat'])) ?><br/>
                <strong><small>Bil. Dokumen: </small></strong><?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?><br/>
                <strong><small>Tarikh Berkuatkuasa: </small></strong><?php echo $tarikh_wujud_rekod; ?><br/>
                <strong><small>Status: </small></strong><?php echo $status_rekod.$nama_jab_baharu.$tajuk_dok_baharu; ?><br/>
                <strong><small>Teras: </small></strong>
                <?php
                $sql_teras = "SELECT nama_teras FROM teras_dok, teras_strategik WHERE teras_dok.kod_dok = '$kod_dok_ini' AND teras_dok.kod_teras = teras_strategik.kod_teras AND teras_dok.checked_value = 1 ORDER BY teras_index ASC";  

                $rs_teras=$conn->query($sql_teras);

                if($rs_teras === false) {
                    trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_teras->num_rows;
                }

                $jum_teras = $rows_returned;

                $rs_teras=$conn->query($sql_teras);

                if($rs_teras === false) {
                    trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_teras = $rs_teras->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_teras = 1;
                foreach($arr_teras as $row_teras) {
                    if ($pembilang_teras > 1 AND $pembilang_teras < $jum_teras) {
                        echo stripslashes(strtoupper(", "));
                    }
                    elseif ($pembilang_teras == $jum_teras AND $jum_teras <> 1) {
                        echo stripslashes(strtoupper(", DAN "));
                    }
                    elseif ($jum_teras == 1) {
                        echo stripslashes(strtoupper(""));
                    }
                    echo stripslashes(strtoupper($pembilang_teras.". ".$row_teras['nama_teras']));
                    $pembilang_teras++;
                }
                ?>
                <br/>
                <strong><small>Deskripsi: </small></strong><div class="report-list-desc"><?php echo stripslashes(strtoupper($row['des_dok'])) ?></div><br/>
                <strong><small>Dokumen: </small></strong>
                <?php
                $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                $rs_dok=$conn->query($sql_dok);

                if($rs_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_dok->num_rows;
                }

                $jum_dok = $rows_returned;

                $rs_dok=$conn->query($sql_dok);

                if($rs_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_dok = 1;
                foreach($arr_dok as $row_teras) {
                    if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                        echo stripslashes(strtoupper(", "));
                    }
                    elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                        echo stripslashes(strtoupper(", DAN "));
                    }
                    // echo stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']));
                    echo stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal'])); // nama fail
                    echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' target='_blank'> <i class='fa fa-eye'></i></a>"; // ikon papar
                    echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'> <i class='fa fa-download'></i></a>"; // ikon muat turun
                    // echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']))."</a>";
                    $pembilang_dok++;
                }
                ?>
                <br/>
            &nbsp;
            </td>
        </tr>
        <?php
        $counter++;
    }

    $rs->free();
    $conn->close();
}

function fnShowDocTableContentForSimpleSearch($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['kata_kunci_mudah'])) {
        $katakuncimudah = $_SESSION['kata_kunci_mudah'];
        $kkm = $katakuncimudah;
        if ($kkm != "") {
            $sql = "SELECT * FROM dokumen, kategori, status WHERE (tajuk_dok LIKE '%$kkm%' OR tahun_dok = '$kkm' OR bil_dok = '$kkm' OR des_dok LIKE '%$kkm%') AND tanda_hapus<>0 AND bil_dok<>0 AND dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status = status.kod_status";
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
                if ($_SESSION['status_pentadbir_super']==1) {
                    $button_delete = "<button type='submit' id='btn_hapus_dokumen' name='btn_hapus_dokumen' class='btn btn-danger' title='Hapuskan Rekod Ini' value=''><i class='fa fa-trash'></i></button>";
                }
                else {
                    $button_delete = "";
                }
                $perkataanBil = "Bil.";
                $strokeBil = "/";
                ?>
                <tr><!-- senarai dalam carian mudah -->
                    <td><?php echo $counter ?></td>
                    <td hidden><?php echo $row[$field01name] ?></td>
                    <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])); ?> <?php echo $perkataanBil; ?> <?php echo $row['bil_dok']; ?><?php echo $strokeBil; ?><?php echo $row['tahun_dok']; ?></td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemas Kini Rekod' value='<?php echo $row['kod_dok']; ?>'><i class='fa fa-edit'></i></button>
                        <button hidden type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok']; ?>'><i class='fa fa-eye'></i></button>
                        <?php 
                        if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                            ?>
                            <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=s" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php  
                $kod_dok_ini = $row['kod_dok'];
                $bilDok = $row['bil_dok'];
                $tarikh_wujud_rekod = date('d-m-Y', strtotime($row['tarikh_wujud']));
                $status_rekod = stripslashes(strtoupper($row['nama_status']));
                if ($row['kod_status'] == 4) {
                    $nama_jab_baharu = " ".stripslashes(strtoupper($row['nama_jab']));
                }
                else {
                    $nama_jab_baharu = "";
                }
                if ($row['kod_status'] == 5) {
                    $tajuk_dok_baharu = " ".stripslashes(strtoupper($row['tajuk_dok_baharu']));
                }
                else {
                    $tajuk_dok_baharu = "";
                }
                ?>
                <tr>
                    <td colspan="4">
                        <strong><small>Tajuk: </small></strong><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?><br/>
                        <strong><small>Kategori: </small></strong><?php echo stripslashes(strtoupper($row['nama_kat'])) ?><br/>
                        <strong><small>Bil. Dokumen: </small></strong><?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?><br/>
                        <strong><small>Tarikh Berkuatkuasa: </small></strong><?php echo $tarikh_wujud_rekod; ?><br/>
                        <strong><small>Status: </small></strong><?php echo $status_rekod.$nama_jab_baharu.$tajuk_dok_baharu; ?><br/>
                        <strong><small>Teras: </small></strong>
                        <?php
                        $sql_teras = "SELECT nama_teras FROM teras_dok, teras_strategik WHERE teras_dok.kod_dok = '$kod_dok_ini' AND teras_dok.kod_teras = teras_strategik.kod_teras AND teras_dok.checked_value = 1 ORDER BY teras_index ASC";  

                        $rs_teras=$conn->query($sql_teras);

                        if($rs_teras === false) {
                            trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $rows_returned = $rs_teras->num_rows;
                        }

                        $jum_teras = $rows_returned;

                        $rs_teras=$conn->query($sql_teras);

                        if($rs_teras === false) {
                            trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr_teras = $rs_teras->fetch_all(MYSQLI_ASSOC);
                        }
                        $pembilang_teras = 1;
                        foreach($arr_teras as $row_teras) {
                            if ($pembilang_teras > 1 AND $pembilang_teras < $jum_teras) {
                                echo stripslashes(strtoupper(", "));
                            }
                            elseif ($pembilang_teras == $jum_teras AND $jum_teras <> 1) {
                                echo stripslashes(strtoupper(", DAN "));
                            }
                            elseif ($jum_teras == 1) {
                                echo stripslashes(strtoupper(""));
                            }
                            echo stripslashes(strtoupper($pembilang_teras.". ".$row_teras['nama_teras']));
                            $pembilang_teras++;
                        }
                        ?>
                        <br/>
                        <strong><small>Deskripsi: </small></strong><div class="report-list-desc"><?php echo stripslashes(strtoupper($row['des_dok'])) ?></div><br/>
                        <strong><small>Dokumen: </small></strong>
                        <?php
                        $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                        $rs_dok=$conn->query($sql_dok);

                        if($rs_dok === false) {
                            trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $rows_returned = $rs_dok->num_rows;
                        }

                        $jum_dok = $rows_returned;

                        $rs_dok=$conn->query($sql_dok);

                        if($rs_dok === false) {
                            trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                        }
                        $pembilang_dok = 1;
                        foreach($arr_dok as $row_teras) {
                            if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                                echo stripslashes(strtoupper(", "));
                            }
                            elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                                echo stripslashes(strtoupper(", DAN "));
                            }
                            echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']))."</a>";
                            $pembilang_dok++;
                        }
                        ?>
                        <br/>
                    &nbsp;
                    </td>
                </tr>

                <?php
                $counter++;
            }
        }
        $rs->free();
    }
    ?>
    <?php

    $conn->close();
}

function fnShowDocTableContentForSimpleSearch_bak20180807($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    if (isset($_SESSION['kata_kunci_mudah'])) {
        $katakuncimudah = $_SESSION['kata_kunci_mudah'];
        $kkm = $katakuncimudah;
        if ($kkm != "") {
            $sql = "SELECT * FROM dokumen WHERE (tajuk_dok LIKE '%$kkm%' OR tahun_dok = '$kkm' OR bil_dok = '$kkm') AND tanda_hapus<>0 AND bil_dok<>0";
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
                if ($_SESSION['status_pentadbir_super']==1) {
                    $button_delete = "<button type='submit' id='btn_hapus_dokumen' name='btn_hapus_dokumen' class='btn btn-danger' title='Hapuskan Rekod Ini' value=''><i class='fa fa-trash'></i></button>";
                }
                else {
                    $button_delete = "";
                }
/*                echo "
                <tr>
                    <td>".$counter.".</td>
                    <td>".stripslashes(strtoupper($row['tajuk_dok']))." BIL. ".$row['bil_dok']."/".$row['tahun_dok']."</td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='".$row['kod_dok']."'><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='".$row['kod_dok']."'><i class='fa fa-eye'></i></button>
                        $button_delete
                    </td>
                </tr>
                ";
*/                
                ?>
                <tr><!-- senarai dalam carian mudah -->
                    <td><?php echo $counter ?></td>
                    <td hidden><?php echo $row[$field01name] ?></td>
                    <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                        <?php 
                        if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                            ?>
                            <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=s" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==1) {
                    $button_delete = "<button type='submit' id='btn_hapus_dokumen' name='btn_hapus_dokumen' class='btn btn-danger' title='Hapuskan Rekod Ini' value=''><i class='fa fa-trash'></i></button>";
                }
                else {
                    $button_delete = "";
                }
                $perkataanBil = "Bil.";
                $strokeBil = "/";
                $bilDok = $row['bil_dok'];
                ?>
                <tr><!-- senarai dalam carian lengkap -->
                    <td><?php echo $counter ?></td>
                    <td hidden><?php echo $row[$field01name] ?></td>
                    <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                        <button hidden type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                        <?php 
                        if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                            ?>
                            <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=s" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php  
                $kod_dok_ini = $row['kod_dok'];
                /* paparkan kategori dan status */
                $sql_detail_dok = "SELECT nama_kat, nama_status FROM dokumen, kategori, status WHERE dokumen.kod_dok = '$kod_dok_ini' AND dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status  = status.kod_status";  

                $rs_detail_dok=$conn->query($sql_detail_dok);

                if($rs_detail_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_detail_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_detail_dok->num_rows;
                }

                $jum_detail_dok = $rows_returned;

                $rs_detail_dok=$conn->query($sql_detail_dok);

                if($rs_detail_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_detail_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_detail_dok = $rs_detail_dok->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_teras = 1;
                foreach($arr_detail_dok as $row_detail_dok) {
                }
                /* paparkan kategori dan status */
                $bilDok = $row['bil_dok'];
                $tarikh_wujud_rekod = date('d-m-Y', strtotime($row['tarikh_wujud']));
                $status_rekod = stripslashes(strtoupper($row_detail_dok['nama_status']));
                if ($row_detail_dok['kod_status'] == 4) {
                    $nama_jab_baharu = " ".stripslashes(strtoupper($row['nama_jab']));
                }
                else {
                    $nama_jab_baharu = "";
                }
                if ($row_detail_dok['kod_status'] == 5) {
                    $tajuk_dok_baharu = " ".stripslashes(strtoupper($row['tajuk_dok_baharu']));
                }
                else {
                    $tajuk_dok_baharu = "";
                }
                ?>
                <tr>
                    <td colspan="4">
                        <strong><small>Tajuk: </small></strong><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?><br/>
                        <strong><small>Kategori: </small></strong><?php echo stripslashes(strtoupper($row_detail_dok['nama_kat'])) ?><br/>
                        <strong><small>Bil. Dokumen: </small></strong><?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?><br/>
                        <strong><small>Tarikh Berkuatkuasa: </small></strong><?php echo $tarikh_wujud_rekod; ?><br/>
                        <strong><small>Status: </small></strong><?php echo $status_rekod.$nama_jab_baharu.$tajuk_dok_baharu; ?><br/>
                        <strong><small>Teras: </small></strong>
                        <?php
                        $sql_teras = "SELECT nama_teras FROM teras_dok, teras_strategik WHERE teras_dok.kod_dok = '$kod_dok_ini' AND teras_dok.kod_teras = teras_strategik.kod_teras AND teras_dok.checked_value = 1 ORDER BY teras_index ASC";  

                        $rs_teras=$conn->query($sql_teras);

                        if($rs_teras === false) {
                            trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $rows_returned = $rs_teras->num_rows;
                        }

                        $jum_teras = $rows_returned;

                        $rs_teras=$conn->query($sql_teras);

                        if($rs_teras === false) {
                            trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr_teras = $rs_teras->fetch_all(MYSQLI_ASSOC);
                        }
                        $pembilang_teras = 1;
                        foreach($arr_teras as $row_teras) {
                            if ($pembilang_teras > 1 AND $pembilang_teras < $jum_teras) {
                                echo stripslashes(strtoupper(", "));
                            }
                            elseif ($pembilang_teras == $jum_teras AND $jum_teras <> 1) {
                                echo stripslashes(strtoupper(", DAN "));
                            }
                            elseif ($jum_teras == 1) {
                                echo stripslashes(strtoupper(""));
                            }
                            echo stripslashes(strtoupper($pembilang_teras.". ".$row_teras['nama_teras']));
                            $pembilang_teras++;
                        }
                        ?>
                        <br/>
                        <strong><small>Deskripsi: </small></strong><div class="report-list-desc"><?php echo stripslashes(strtoupper($row['des_dok'])) ?></div><br/>
                        <strong><small>Dokumen: </small></strong>
                        <?php
                        $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                        $rs_dok=$conn->query($sql_dok);

                        if($rs_dok === false) {
                            trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $rows_returned = $rs_dok->num_rows;
                        }

                        $jum_dok = $rows_returned;

                        $rs_dok=$conn->query($sql_dok);

                        if($rs_dok === false) {
                            trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                        }
                        $pembilang_dok = 1;
                        foreach($arr_dok as $row_teras) {
                            if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                                echo stripslashes(strtoupper(", "));
                            }
                            elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                                echo stripslashes(strtoupper(", DAN "));
                            }
                            echo stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal'])); // nama fail
                            echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' target='_blank'> <i class='fa fa-eye'></i></a>"; // ikon papar
                            echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'> <i class='fa fa-download'></i></a>"; // ikon muat turun
                            // echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']))."</a>";
                            $pembilang_dok++;
                        }
                        ?>
                        <br/>
                    &nbsp;
                    </td>
                </tr>
                <?php
                $counter++;
            }
        }
        $rs->free();
    }
    ?>
    <?php

    $conn->close();
}

function fnShowDocTableContentForAdvancedSearchOriginal($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==1) {
                    $button_delete = "<button type='submit' id='btn_hapus_dokumen' name='btn_hapus_dokumen' class='btn btn-danger' title='Hapuskan Rekod Ini' value=''><i class='fa fa-trash'></i></button>";
                }
                else {
                    $button_delete = "";
                }
                $perkataanBil = "Bil.";
                $strokeBil = "/";
                $bilDok = $row['bil_dok'];
                ?>
                <tr><!-- senarai dalam carian lengkap -->
                    <td><?php echo $counter ?></td>
                    <td hidden><?php echo $row[$field01name] ?></td>
                    <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                        <?php 
                        if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                            ?>
                            <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=s" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php  
                $kod_dok_ini = $row['kod_dok'];
                /* paparkan kategori dan status */
                $sql_detail_dok = "SELECT nama_kat, nama_status FROM dokumen, kategori, status WHERE dokumen.kod_dok = '$kod_dok_ini' AND dokumen.kod_kat = kategori.kod_kat AND dokumen.kod_status  = status.kod_status";  

                $rs_detail_dok=$conn->query($sql_detail_dok);

                if($rs_detail_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_detail_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $rows_returned = $rs_detail_dok->num_rows;
                }

                $jum_detail_dok = $rows_returned;

                $rs_detail_dok=$conn->query($sql_detail_dok);

                if($rs_detail_dok === false) {
                    trigger_error('Wrong SQL: ' . $sql_detail_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                } else {
                    $arr_detail_dok = $rs_detail_dok->fetch_all(MYSQLI_ASSOC);
                }
                $pembilang_teras = 1;
                foreach($arr_detail_dok as $row_detail_dok) {
                }
                /* paparkan kategori dan status */
                $bilDok = $row['bil_dok'];
                $tarikh_wujud_rekod = date('d-m-Y', strtotime($row['tarikh_wujud']));
                $status_rekod = stripslashes(strtoupper($row_detail_dok['nama_status']));
                if ($row_detail_dok['kod_status'] == 4) {
                    $nama_jab_baharu = " ".stripslashes(strtoupper($row['nama_jab']));
                }
                else {
                    $nama_jab_baharu = "";
                }
                if ($row_detail_dok['kod_status'] == 5) {
                    $tajuk_dok_baharu = " ".stripslashes(strtoupper($row['tajuk_dok_baharu']));
                }
                else {
                    $tajuk_dok_baharu = "";
                }
                ?>
                <tr hidden>
                    <td hidden colspan="4">
                        <strong><small>Tajuk: </small></strong><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?><br/>
                        <strong><small>Kategori: </small></strong><?php echo stripslashes(strtoupper($row_detail_dok['nama_kat'])) ?><br/>
                        <strong><small>Bil. Dokumen: </small></strong><?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?><br/>
                        <strong><small>Tarikh Berkuatkuasa: </small></strong><?php echo $tarikh_wujud_rekod; ?><br/>
                        <strong><small>Status: </small></strong><?php echo $status_rekod.$nama_jab_baharu.$tajuk_dok_baharu; ?><br/>
                        <strong><small>Teras: </small></strong>
                        <?php
                        $sql_teras = "SELECT nama_teras FROM teras_dok, teras_strategik WHERE teras_dok.kod_dok = '$kod_dok_ini' AND teras_dok.kod_teras = teras_strategik.kod_teras AND teras_dok.checked_value = 1 ORDER BY teras_index ASC";  

                        $rs_teras=$conn->query($sql_teras);

                        if($rs_teras === false) {
                            trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $rows_returned = $rs_teras->num_rows;
                        }

                        $jum_teras = $rows_returned;

                        $rs_teras=$conn->query($sql_teras);

                        if($rs_teras === false) {
                            trigger_error('Wrong SQL: ' . $sql_teras . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr_teras = $rs_teras->fetch_all(MYSQLI_ASSOC);
                        }
                        $pembilang_teras = 1;
                        foreach($arr_teras as $row_teras) {
                            if ($pembilang_teras > 1 AND $pembilang_teras < $jum_teras) {
                                echo stripslashes(strtoupper(", "));
                            }
                            elseif ($pembilang_teras == $jum_teras AND $jum_teras <> 1) {
                                echo stripslashes(strtoupper(", DAN "));
                            }
                            elseif ($jum_teras == 1) {
                                echo stripslashes(strtoupper(""));
                            }
                            echo stripslashes(strtoupper($pembilang_teras.". ".$row_teras['nama_teras']));
                            $pembilang_teras++;
                        }
                        ?>
                        <br/>
                        <strong><small>Deskripsi: </small></strong><div class="report-list-desc"><?php echo stripslashes(strtoupper($row['des_dok'])) ?></div><br/>
                        <strong><small>Dokumen: </small></strong>
                        <?php
                        $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                        $rs_dok=$conn->query($sql_dok);

                        if($rs_dok === false) {
                            trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $rows_returned = $rs_dok->num_rows;
                        }

                        $jum_dok = $rows_returned;

                        $rs_dok=$conn->query($sql_dok);

                        if($rs_dok === false) {
                            trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                        } else {
                            $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                        }
                        $pembilang_dok = 1;
                        foreach($arr_dok as $row_teras) {
                            if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                                echo stripslashes(strtoupper(", "));
                            }
                            elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                                echo stripslashes(strtoupper(", DAN "));
                            }
                            echo "<a href='../papers/".stripslashes($row_teras['nama_dok_disimpan'])."' download='".stripslashes($row_teras['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_teras['nama_dok_asal']))."</a>";
                            $pembilang_dok++;
                        }
                        ?>
                        <br/>
                    &nbsp;
                    </td>
                </tr>
                <?php
                $counter++;
            }
        }
        $rs->free();
    }
    ?>
    <?php

    $conn->close();
}

function fnShowDocTableContentForAdvancedSearch_bak20180808($a,$b,$c,$d){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
                if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==1) {
                    $button_delete = "<button type='submit' id='btn_hapus_dokumen' name='btn_hapus_dokumen' class='btn btn-danger' title='Hapuskan Rekod Ini' value=''><i class='fa fa-trash'></i></button>";
                }
                else {
                    $button_delete = "";
                }
/*                echo "
                <tr>
                    <td>".$counter.".</td>
                    <td>".stripslashes(strtoupper($row['tajuk_dok']))." BIL. ".$row['bil_dok']."/".$row['tahun_dok']."</td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='".$row['kod_dok']."' ><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='".$row['kod_dok']."'><i class='fa fa-eye'></i></button>
                    $button_delete

                    </td>
                </tr>
                ";
*/                ?>
                <tr><!-- senarai dalam carian lengkap -->
                    <td><?php echo $counter ?></td>
                    <td hidden><?php echo $row[$field01name] ?></td>
                    <td><?php echo stripslashes(strtoupper($row['tajuk_dok'])) ?> <?php echo $perkataanBil ?> <?php echo $bilDok ?><?php echo $strokeBil ?><?php echo $row['tahun_dok'] ?></td>
                    <td style='align-content: center;' align='center'>
                        <button type='submit' id='btn_papar_borang_kemaskini' name='btn_papar_borang_kemaskini' class='btn btn-success' title='Kemaskini' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-edit'></i></button>
                        <button type='submit' id='btn_papar_perincian_dokumen' name='btn_papar_perincian_dokumen' class='btn btn-success' title='Papar' value='<?php echo $row['kod_dok'] ?>'><i class='fa fa-eye'></i></button>
                        <?php 
                        if ($_SESSION['status_pentadbir_super']==1 OR $_SESSION['status_pentadbir_dokumen']==2) {
                            ?>
                            <a href="delete.php?id=<?php echo $row['kod_dok']; ?>&source=s" title="Hapus Rekod <?php echo $row['kod_dok']; ?>" class='btn btn-danger' onclick="return confirm('Anda pasti untuk padamkan rekod?')"><i class="fa fa-trash"></i></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];

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
            // $_SESSION['kata_laluan2']=$row['kata_laluan2'];
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
        if ($row['papar_data'] != 1) {
            $showicon = "";
            // $showicon = "<i class='fa fa-eye-slash'></i>";
            $itemStatus = "Tidak Aktif";
            $fontColor = "red";
        }
        else {
            $showicon = "";
            $itemStatus = "Aktif";
            $fontColor = "turquoise";
        }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td ".$_SESSION['code_display_status'].">".$row[$field01name]."</td>
            <td>".stripslashes($row[$field02name])."</td>
            <td style='font-weight:bold; color:".$fontColor.";'>".$itemStatus."</td>
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

function fnShowDataTableContent_bak20180814($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
        if ($row['papar_data'] != 1) {
            $showicon = "<i class='fa fa-eye-slash'></i>";
        }
        else {
            $showicon = "";
        }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td ".$_SESSION['code_display_status'].">".$row[$field01name]."</td>
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

function fnShowNameTitleTableContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
        if (isset($row['papar_data']) AND $row['papar_data'] == 1) {
            $showicon = "";
            $itemStatus = "Aktif";
            $fontColor = "turquoise";
        }
        else {
            $showicon = "";
            // $showicon = "<i class='fa fa-eye-slash'></i>";
            $itemStatus = "Tidak Aktif";
            $fontColor = "red";
         }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td hidden>".$row[$field01name]."</td>
            <td>".stripslashes($row[$field02name])."</td>
            <td style='font-weight:bold; color:".$fontColor.";'>".$itemStatus."</td>
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

function fnShowDivisionTableContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT $field01name, $field02name, singkatan_bahagian, papar_data FROM $table01name WHERE $field01name != 1";

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
        if (isset($row['papar_data']) AND $row['papar_data'] == 1) {
            $showicon = "";
            $itemStatus = "Aktif";
            $fontColor = "turquoise";
        }
        else {
            $showicon = "";
            // $showicon = "<i class='fa fa-eye-slash'></i>";
            $itemStatus = "Tidak Aktif";
            $fontColor = "red";
         }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td ".$_SESSION['code_display_status'].">".$row[$field01name]."</td>
            <td>".stripslashes($row[$field02name])."</td>
            <td>".stripslashes($row['singkatan_bahagian'])."</td>
            <td style='font-weight:bold; color:".$fontColor.";'>".$itemStatus."</td>
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

function fnShowDivisionTableContent_bak20180814($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;

    $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);

    // check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
    }

    $sql="SELECT $field01name, $field02name, singkatan_bahagian, papar_data FROM $table01name WHERE $field01name != 1";

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
        if (isset($row['papar_data']) AND $row['papar_data'] == 1) {
            $showicon = "";
        }
        else {
            $showicon = "<i class='fa fa-eye-slash'></i>";
        }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td ".$_SESSION['code_display_status'].">".$row[$field01name]."</td>
            <td>".stripslashes($row[$field02name])."</td>
            <td>".stripslashes($row['singkatan_bahagian'])."</td>
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
        if (isset($row['papar_data']) AND $row['papar_data'] == 1) {
            $showicon = "";
            $itemStatus = "Aktif";
            $fontColor = "turquoise";
        }
        else {
            $showicon = "";
            // $showicon = "<i class='fa fa-eye-slash'></i>";
            $itemStatus = "Tidak Aktif";
            $fontColor = "red";
         }
        echo "
        <tr>
            <td>".$counter.".</td>
            <td>".$row[$field01name]."</td>
            <td>".stripslashes($row[$field02name])."</td>
            <td style='font-weight:bold; color:".$fontColor.";'>".$itemStatus."</td>
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

function fnSetDisplayStatForStatusDivs(){
    if ($_SESSION['kod_status'] == 2) {
        $_SESSION['display_stat_divmansuh'] = "hidden";
        $_SESSION['display_stat_divserah'] = "hidden";
        $_SESSION['display_stat_divpinda'] = "hidden";
    }
    elseif ($_SESSION['kod_status'] == 3) {
        $_SESSION['display_stat_divmansuh'] = "";
        $_SESSION['display_stat_divserah'] = "hidden";
        $_SESSION['display_stat_divpinda'] = "hidden";
    }
    elseif ($_SESSION['kod_status'] == 4) {
        $_SESSION['display_stat_divmansuh'] = "hidden";
        $_SESSION['display_stat_divserah'] = "";
        $_SESSION['display_stat_divpinda'] = "hidden";
    }
    elseif ($_SESSION['kod_status'] == 5) {
        $_SESSION['display_stat_divmansuh'] = "hidden";
        $_SESSION['display_stat_divserah'] = "hidden";
        $_SESSION['display_stat_divpinda'] = "";
    }
}

function fnShowViewDocContent($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
    // fnRunAlert($tablename);
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
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='kod_dok'>Kod Dokumen <span hidden class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <!-- <input type='text' id='kod_dok' name='kod_dok' title='Kod Dokumen' maxlength='11' class='form-control col-md-7 col-xs-12' value='<?php echo $row['kod_dok']; ?>' readonly > -->
                <p>
                    <?php echo $row['kod_dok']; ?>
                </p>
            </div>
        </div>
        <?php $_SESSION['kod_dok_to_delete'] = $row['kod_dok']; ?>
        <!-- copied from newdoc.php below -->
                <?php
                $_SESSION['kod_kat'] = $row['kod_kat'];
                fnDropdownKategoriForView($DBServer,$DBUser,$DBPass,$DBName);
                ?>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen <span hidden class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}" readonly> -->
                    <p>
                        <?php echo $row['bil_dok']; ?>
                    </p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tahun_dokumen">Tahun Dokumen <span hidden class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['tahun_dok']; ?>" type="text" id="tahun_dokumen" name="tahun_dokumen" required class="form-control col-md-7 col-xs-12" maxlength="4" pattern="\d{1,4}" readonly> -->
                    <p>
                        <?php echo $row['tahun_dok']; ?>
                    </p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tajuk_dokumen">Tajuk Dokumen <span hidden class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required autofocus class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                    <p>
                        <?php echo $row['tajuk_dok']; ?>
                    </p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="des_dokumen">Deskripsi Dokumen <span hidden class="required">*</span>
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
                fnSetDisplayStatForStatusDivs();
                fnDropdownStatusDokForView($DBServer,$DBUser,$DBPass,$DBName);
                ?>
                <p class="stattext" hidden></p>
                <!-- mansuh -->
                <div class="form-group" id="divmansuh" <?php echo $_SESSION['display_stat_divmansuh']; ?>>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span hidden class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy" readonly>
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                </div>
                <!-- serah -->
                <div class="form-group" id="divserah" <?php echo $_SESSION['display_stat_divserah']; ?>>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span hidden class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy" readonly>
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <?php  
                  $_SESSION['kod_jab_asal'] = $row['kod_jab_asal'];
                  fnDropdownJabStatSerahForView($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_asal','Asal');
                  $_SESSION['kod_jab_baharu'] = $row['kod_jab_baharu'];
                  fnDropdownJabStatSerahForView($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_baharu','Baharu');
                  ?>
                </div>
                <!-- pinda -->
                <div class="form-group" id="divpinda" <?php echo $_SESSION['display_stat_divpinda']; ?>>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span hidden class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy" readonly>
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true" readonly></span>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span hidden class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <!-- <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150" readonly/> -->
                    <textarea id="tajuk_dok_asal"  name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" readonly><?php echo $row['tajuk_dok_asal']; ?></textarea>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span hidden class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <!-- <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150" readonly/> -->
                    <textarea id="tajuk_dok_baharu"  name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" readonly><?php echo $row['tajuk_dok_baharu']; ?></textarea>
                  </div>
                </div>


                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Dokumen Sokongan <span hidden class="required">*</span>
                  </label>
                   <?php
                    $kod_dok_ini = $searchvalue;
                    $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                    $rs_dok=$conn->query($sql_dok);

                    if($rs_dok === false) {
                        trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $rows_returned = $rs_dok->num_rows;
                    }

                    $jum_dok = $rows_returned;

                    $rs_dok=$conn->query($sql_dok);

                    if($rs_dok === false) {
                        trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                    }
                    $pembilang_dok = 1;
                    ?>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <span>
                            <?php 
                            foreach($arr_dok as $row_dok) {
                                if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                                    echo stripslashes(strtoupper(", <br/>"));
                                }
                                elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                                    echo stripslashes(strtoupper(", DAN <br/>"));
                                }
                                // echo stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal']))." ";
                                echo stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal'])); // nama fail
                                echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' target='_blank'> <i class='fa fa-eye'></i></a>"; // ikon papar
                                echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' download='".stripslashes($row_dok['nama_dok_asal'])."'> <i class='fa fa-download'></i></a>"; // ikon muat turun
                                // echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' download='".stripslashes($row_dok['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal']))."</a>";
                                if ($pembilang_dok <= $jum_dok) {
                                    // echo "<br/>";
                                }
                                else {
                                    // echo "<br/>";
                                }
                                $pembilang_dok++;
                            }
                            // echo $row['nama_dok_asal']; 
                            ?>
                        </span>
                    </div>
                  <div hidden class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept=".pdf" class="file form-control col-md-7 col-xs-12"> -->
                    <?php 
                    if ($row['nama_dok_asal'] != "") {
                        echo $row['nama_dok_asal']; 
                        ?>
                        <br>
                    <object data="../papers/<?php echo $row['nama_dok_disimpan']; ?>" type="application/pdf" width="496" height="701.6" >
                        <!-- 
                        2480 X 3508 pixels
                        620 x 877 (1/4)
                        496 x 701.6 (1/5)
                        -->
                      <p>Nampaknya pelayar internet anda tidak dilengkapi dengan plug-in PDF.
                        Sila muat buka dokumen  <a href="../papers/<?php echo $row['nama_dok_disimpan']; ?>"> melalui pautan ini.</a>
                      </p>
                    </object>
                        <?php
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
                  <label class="control-label col-md-3 col-sm-3 col-xs-3" for="tarikh_wujud">Tarikh Kuat Kuasa Dokumen <span hidden class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy"> -->
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
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tag_dokumen"><i>Tag</i> Dokumen <span hidden class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <textarea rows="4" id="tag_dokumen" name="tag_dokumen" required class="form-control col-md-7 col-xs-12"><?php echo $row['tag_dokumen']; ?></textarea> -->
                    <!-- <small>masukkan <i>tag</i> dipisahkan dengan tanda koma</small> -->
                      <p>
                          <?php echo $row['tag_dokumen']; ?>
                      </p>
                  </div>
                </div>
                <!-- medan catatan: ditambah pada 20170322 oleh SFAA -->
                <div class="form-group"> 
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="catatan_dokumen">Catatan Dokumen</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <textarea rows="4" id="catatan_dokumen" name="catatan_dokumen" class="form-control col-md-7 col-xs-12"><?php // echo $_SESSION['catatan_dokumen']; ?></textarea> -->
                    <!-- <small>Sila masukkan catatan, jika ada.</small> -->
                    <p>
                        <?php echo $row['catatan_dokumen']; ?>
                    </p>
                  </div>
                </div>
                <!-- tamat medan catatan -->
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

function fnShowViewDocContent_bak20180813($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
    // fnRunAlert($tablename);
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
        <?php $_SESSION['kod_dok_to_delete'] = $row['kod_dok']; ?>
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
                fnSetDisplayStatForStatusDivs();
                fnDropdownStatusDokForView($DBServer,$DBUser,$DBPass,$DBName);
                ?>
                <p class="stattext" hidden></p>
                <!-- mansuh -->
                <div class="form-group" id="divmansuh" <?php echo $_SESSION['display_stat_divmansuh']; ?>>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy" readonly>
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                </div>
                <!-- serah -->
                <div class="form-group" id="divserah" <?php echo $_SESSION['display_stat_divserah']; ?>>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy" readonly>
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                  </div>
                  <?php  
                  $_SESSION['kod_jab_asal'] = $row['kod_jab_asal'];
                  fnDropdownJabStatSerahForView($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_asal','Asal');
                  $_SESSION['kod_jab_baharu'] = $row['kod_jab_baharu'];
                  fnDropdownJabStatSerahForView($DBServer,$DBUser,$DBPass,$DBName,'kod_jab_baharu','Baharu');
                  ?>
                </div>
                <!-- pinda -->
                <div class="form-group" id="divpinda" <?php echo $_SESSION['display_stat_divpinda']; ?>>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy" readonly>
                    <span class="fa fa-calendar form-control-feedback right" aria-hidden="true" readonly></span>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <!-- <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150" readonly/> -->
                    <textarea id="tajuk_dok_asal"  name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" readonly><?php echo $row['tajuk_dok_asal']; ?></textarea>
                  </div>
                  <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                  </label>
                  <div class="col-md-4 col-sm-4 col-xs-7">
                    <!-- <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150" readonly/> -->
                    <textarea id="tajuk_dok_baharu"  name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" readonly><?php echo $row['tajuk_dok_baharu']; ?></textarea>
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                  </label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept=".pdf" class="file form-control col-md-7 col-xs-12"> -->
                    <?php 
                    if ($row['nama_dok_asal'] != "") {
                        echo $row['nama_dok_asal']; 
                        ?>
                        <br>
                    <object data="../papers/<?php echo $row['nama_dok_disimpan']; ?>" type="application/pdf" width="496" height="701.6" >
                        <!-- 
                        2480 X 3508 pixels
                        620 x 877 (1/4)
                        496 x 701.6 (1/5)
                        -->
                      <p>Nampaknya pelayar internet anda tidak dilengkapi dengan plug-in PDF.
                        Sila muat buka dokumen  <a href="../papers/<?php echo $row['nama_dok_disimpan']; ?>"> melalui pautan ini.</a>
                      </p>
                    </object>
                        <?php
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
                    <!-- <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy"> -->
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
                <!-- medan catatan: ditambah pada 20170322 oleh SFAA -->
                <div class="form-group"> 
                  <label class="control-label col-md-3 col-sm-3 col-xs-12" for="catatan_dokumen">Catatan Dokumen</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <!-- <textarea rows="4" id="catatan_dokumen" name="catatan_dokumen" class="form-control col-md-7 col-xs-12"><?php // echo $_SESSION['catatan_dokumen']; ?></textarea> -->
                    <!-- <small>Sila masukkan catatan, jika ada.</small> -->
                    <p>
                        <?php echo $row['catatan_dokumen']; ?>
                    </p>
                  </div>
                </div>
                <!-- tamat medan catatan -->
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
            <?php 
            $_SESSION['kod_dok_to_delete'] = $row['kod_dok']; 
            ?>
            <!-- copied from newdoc.php below -->
                    <?php
                    $_SESSION['kod_kat'] = $row['kod_kat'];
                    fnDropdownKategori($DBServer,$DBUser,$DBPass,$DBName);
                    ?>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen<!-- <span class="required">*</span>-->
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
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
                        <input value="<?php echo $row['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required autofocus class="form-control col-md-7 col-xs-12" maxlength="300"/>
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
                    fnSetDisplayStatForStatusDivs();
                    fnDropdownStatusDok($DBServer,$DBUser,$DBPass,$DBName);
                    ?>


                    
                    <p class="stattext" hidden></p>
                    <!-- mansuh -->
                    <div class="form-group" id="divmansuh" <?php echo $_SESSION['display_stat_divmansuh']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                    </div>
                    <!-- serah -->
                    <div class="form-group" id="divserah" <?php echo $_SESSION['display_stat_divserah']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
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
                    <div class="form-group" id="divpinda" <?php echo $_SESSION['display_stat_divpinda']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <!-- <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                        <textarea id="tajuk_dok_asal"  name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12"><?php echo $row['tajuk_dok_asal']; ?></textarea>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <!-- <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                        <textarea id="tajuk_dok_baharu"  name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12"><?php echo $row['tajuk_dok_baharu']; ?></textarea>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                      </label>
                      <!-- tak kisah, brp banyak dah ada, boleh upload lg 4. yg baharu akan overwrite yg lama -->
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 5px;">&nbsp;</p>
                        <input type="file" id="nama_dok_01" name="nama_dok_01" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 5px;">&nbsp;</p>
                        <input type="file" id="nama_dok_02" name="nama_dok_02" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 5px;">&nbsp;</p>
                        <input type="file" id="nama_dok_03" name="nama_dok_03" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 1px;">&nbsp;</p>
                      </div>
                    </div>
                    <?php
                    $kod_dok_ini = $searchvalue;
                    $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                    $rs_dok=$conn->query($sql_dok);

                    if($rs_dok === false) {
                        trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $rows_returned = $rs_dok->num_rows;
                    }

                    $jum_dok = $rows_returned;

                    $rs_dok=$conn->query($sql_dok);

                    if($rs_dok === false) {
                        trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                    }
                    $pembilang_dok = 1;
                    ?>
                    <div class="form-group">
                        <span class="col-md-6 col-md-offset-3 col-sm-6 col-xs-12">
                            <?php 
                            foreach($arr_dok as $row_dok) {
                                if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                                    echo stripslashes(strtoupper(", <br/>"));
                                }
                                elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                                    echo stripslashes(strtoupper(", DAN <br/>"));
                                }
                                // echo stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal']))." ";
                                echo stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal'])); // nama fail
                                echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' target='_blank'> <i class='fa fa-eye'></i></a>"; // ikon papar
                                echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' download='".stripslashes($row_dok['nama_dok_asal'])."'> <i class='fa fa-download'></i></a>"; // ikon muat turun
                                // echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' download='".stripslashes($row_dok['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal']))."</a>";
                                if ($pembilang_dok <= $jum_dok) {
                                    // echo "<br/>";
                                }
                                else {
                                    // echo "<br/>";
                                }
                                $pembilang_dok++;
                            }
                            // echo $row['nama_dok_asal']; 
                            ?>
                        </span>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-3" for="tarikh_wujud">Tarikh Kuat Kuasa Dokumen <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
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
                    <!-- medan catatan: ditambah pada 20170322 oleh SFAA -->
                    <div class="form-group"> 
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="catatan_dokumen">Catatan Dokumen
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <textarea rows="4" id="catatan_dokumen" name="catatan_dokumen" class="form-control col-md-7 col-xs-12"><?php echo $row['catatan_dokumen']; ?></textarea>
                        <small>Sila masukkan catatan, jika ada.</small>
                      </div>
                    </div>
                    <!-- tamat medan catatan -->
                    <div class="ln_solid"></div>
            <!-- copied from newdoc.php above -->
            <?php

        }
    }
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowUpdateDocFormContentForSearch(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
        $sql="SELECT * FROM dokumen WHERE kod_dok = '$_SESSION[kod_dok_to_be_updated]' ORDER BY kod_dok ASC";

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
            <?php 
            $_SESSION['kod_dok_to_delete'] = $row['kod_dok']; 
            ?>
            <!-- copied from newdoc.php below -->
                    <?php
                    $_SESSION['kod_kat'] = $row['kod_kat'];
                    fnDropdownKategori($DBServer,$DBUser,$DBPass,$DBName);
                    ?>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen<!-- <span class="required">*</span>-->
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
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
                        <input value="<?php echo $row['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required autofocus class="form-control col-md-7 col-xs-12" maxlength="300"/>
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
                    fnSetDisplayStatForStatusDivs();
                    fnDropdownStatusDok($DBServer,$DBUser,$DBPass,$DBName);
                    ?>


                    
                    <p class="stattext" hidden></p>
                    <!-- mansuh -->
                    <div class="form-group" id="divmansuh" <?php echo $_SESSION['display_stat_divmansuh']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                    </div>
                    <!-- serah -->
                    <div class="form-group" id="divserah" <?php echo $_SESSION['display_stat_divserah']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
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
                    <div class="form-group" id="divpinda" <?php echo $_SESSION['display_stat_divpinda']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <!-- <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                        <textarea id="tajuk_dok_asal"  name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12"><?php echo $row['tajuk_dok_asal']; ?></textarea>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <!-- <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                        <textarea id="tajuk_dok_baharu"  name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12"><?php echo $row['tajuk_dok_baharu']; ?></textarea>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                      </label>
                      <!-- tak kisah, brp banyak dah ada, boleh upload lg 4. yg baharu akan overwrite yg lama -->
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 5px;">&nbsp;</p>
                        <input type="file" id="nama_dok_01" name="nama_dok_01" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 5px;">&nbsp;</p>
                        <input type="file" id="nama_dok_02" name="nama_dok_02" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 5px;">&nbsp;</p>
                        <input type="file" id="nama_dok_03" name="nama_dok_03" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
                        <br><p style="font-size: 1px;">&nbsp;</p>
                      </div>
                    </div>
                    <?php
                    $kod_dok_ini = $searchvalue;
                    $sql_dok = "SELECT nama_dok_asal, nama_dok_disimpan FROM dok_sokongan WHERE kod_dok_fk = '$kod_dok_ini' ORDER BY id ASC";  

                    $rs_dok=$conn->query($sql_dok);

                    if($rs_dok === false) {
                        trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $rows_returned = $rs_dok->num_rows;
                    }

                    $jum_dok = $rows_returned;

                    $rs_dok=$conn->query($sql_dok);

                    if($rs_dok === false) {
                        trigger_error('Wrong SQL: ' . $sql_dok . ' Error: ' . $conn->error, E_USER_ERROR);
                    } else {
                        $arr_dok = $rs_dok->fetch_all(MYSQLI_ASSOC);
                    }
                    $pembilang_dok = 1;
                    ?>
                    <div class="form-group">
                        <span class="col-md-6 col-md-offset-3 col-sm-6 col-xs-12">
                            <?php 
                            foreach($arr_dok as $row_dok) {
                                if ($pembilang_dok > 1 AND $pembilang_dok < $jum_dok) {
                                    echo stripslashes(strtoupper(", <br/>"));
                                }
                                elseif ($pembilang_dok == $jum_dok AND $jum_dok <> 1) {
                                    echo stripslashes(strtoupper(", DAN <br/>"));
                                }
                                // echo stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal']));
                                echo stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal'])); // nama fail
                                echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' target='_blank'> <i class='fa fa-eye'></i></a>"; // ikon papar
                                echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' download='".stripslashes($row_dok['nama_dok_asal'])."'> <i class='fa fa-download'></i></a>"; // ikon muat turun
                                // echo "<a href='../papers/".stripslashes($row_dok['nama_dok_disimpan'])."' download='".stripslashes($row_dok['nama_dok_asal'])."'>".stripslashes(strtoupper($pembilang_dok.". ".$row_dok['nama_dok_asal']))."</a>";
                                $pembilang_dok++;
                            }
                            // echo $row['nama_dok_asal']; 
                            ?>
                        </span>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-3" for="tarikh_wujud">Tarikh Kuat Kuasa Dokumen <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
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
                    <!-- medan catatan: ditambah pada 20170322 oleh SFAA -->
                    <div class="form-group"> 
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="catatan_dokumen">Catatan Dokumen
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <textarea rows="4" id="catatan_dokumen" name="catatan_dokumen" class="form-control col-md-7 col-xs-12"><?php echo $row['catatan_dokumen']; ?></textarea>
                        <small>Sila masukkan catatan, jika ada.</small>
                      </div>
                    </div>
                    <!-- tamat medan catatan -->
                    <div class="ln_solid"></div>
            <!-- copied from newdoc.php above -->
            <?php


        }
    }
    ?>
    <?php

    $rs->free();
    $conn->close();
}

function fnShowUpdateDocFormContent_bak201808021304($a,$b,$c,$d,$e,$f,$g){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
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
            <?php $_SESSION['kod_dok_to_delete'] = $row['kod_dok']; ?>
            <!-- copied from newdoc.php below -->
                    <?php
                    $_SESSION['kod_kat'] = $row['kod_kat'];
                    fnDropdownKategori($DBServer,$DBUser,$DBPass,$DBName);
                    ?>
                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bil_dokumen">Bil. Dokumen<!-- <span class="required">*</span>-->
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input value="<?php echo $row['bil_dok']; ?>" type="text" id="bil_dokumen" name="bil_dokumen" class="form-control col-md-7 col-xs-12" maxlength="3" pattern="\d{1,3}">
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
                        <input value="<?php echo $row['tajuk_dok']; ?>" type="text" id="tajuk_dokumen" name="tajuk_dokumen" required autofocus class="form-control col-md-7 col-xs-12" maxlength="300"/>
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
                    fnSetDisplayStatForStatusDivs();
                    fnDropdownStatusDok($DBServer,$DBUser,$DBPass,$DBName);
                    ?>


                    
                    <p class="stattext" hidden></p>
                    <!-- mansuh -->
                    <div class="form-group" id="divmansuh" <?php echo $_SESSION['display_stat_divmansuh']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_mansuh">Tarikh Mansuh <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_mansuh']; ?>" type="date" id="tarikh_mansuh" name="tarikh_mansuh"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                    </div>
                    <!-- serah -->
                    <div class="form-group" id="divserah" <?php echo $_SESSION['display_stat_divserah']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_serah">Tarikh Serah <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_serah']; ?>" type="date" id="tarikh_serah" name="tarikh_serah"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
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
                    <div class="form-group" id="divpinda" <?php echo $_SESSION['display_stat_divpinda']; ?>>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tarikh_pinda">Tarikh Pinda <span class="required">*</span></label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <input value="<?php echo $row['tarikh_pinda']; ?>" type="date" id="tarikh_pinda" name="tarikh_pinda"  class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
                        <span class="fa fa-calendar form-control-feedback right" aria-hidden="true"></span>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_asal">Tajuk Asal <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <!-- <input value="<?php echo $row['tajuk_dok_asal']; ?>" type="text" id="tajuk_dok_asal" name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                        <textarea id="tajuk_dok_asal"  name="tajuk_dok_asal" class="form-control col-md-7 col-xs-12"><?php echo $row['tajuk_dok_asal']; ?></textarea>
                      </div>
                      <label class="control-label col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2" for="tajuk_dok_baharu">Tajuk Baharu <span class="required">*</span>
                      </label>
                      <div class="col-md-4 col-sm-4 col-xs-7">
                        <!-- <input value="<?php echo $row['tajuk_dok_baharu']; ?>" type="text" id="tajuk_dok_baharu" name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12" maxlength="150"/> -->
                        <textarea id="tajuk_dok_baharu"  name="tajuk_dok_baharu" class="form-control col-md-7 col-xs-12"><?php echo $row['tajuk_dok_baharu']; ?></textarea>
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="nama_dok">Muatnaik Dokumen <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="file" id="nama_dok" name="nama_dok" value="ujian" accept="application/*, image/*" class="file form-control col-md-7 col-xs-12">
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
                        <input value="<?php echo $row['tarikh_wujud']; ?>" type="date" id="tarikh_wujud" name="tarikh_wujud" required class="form-control" data-inputmask="'mask': '99-99-9999'" placeholder="dd-mm-yyyy">
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
                    <!-- medan catatan: ditambah pada 20170322 oleh SFAA -->
                    <div class="form-group"> 
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" for="catatan_dokumen">Catatan Dokumen
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <textarea rows="4" id="catatan_dokumen" name="catatan_dokumen" class="form-control col-md-7 col-xs-12"><?php echo $row['catatan_dokumen']; ?></textarea>
                        <small>Sila masukkan catatan, jika ada.</small>
                      </div>
                    </div>
                    <!-- tamat medan catatan -->
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

function fnShowUpdateDivisionFormContent($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $searchvalue    = $h;

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

    $sql="SELECT $field01name, $field02name, singkatan_bahagian, papar_data FROM $table01name WHERE $field01name = $searchvalue";

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
    // $counter = 1;
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
        $row['singkatan_bahagian']=removeslashes($row['singkatan_bahagian']);
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
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='nama_data'>Nama Bahagian <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <input type='text' id='nama_data' name='nama_data' required='required' class='form-control col-md-7 col-xs-12' value='<?php echo htmlspecialchars($row[$field02name],ENT_QUOTES); ?>'>
            </div>
        </div>
        <div class='form-group'>
            <label class='control-label col-md-3 col-sm-3 col-xs-12' for='singkatan_bahagian'>Singkatan Bahagian <span class='required'>*</span>
            </label>
            <div class='col-md-6 col-sm-6 col-xs-12'>
                <input type='text' id='singkatan_bahagian' name='singkatan_bahagian' required='required' class='form-control col-md-7 col-xs-12' value='<?php echo htmlspecialchars($row['singkatan_bahagian'],ENT_QUOTES); ?>'>
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
        // $counter++;

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

function fnShowUpdateFormContent($a,$b,$c,$d,$e,$f,$g,$h){
    $DBServer       = $a; // e.g 'localhost' or '192.168.1.100'
    $DBUser         = $b;
    $DBPass         = $c;
    $DBName         = $d;
    $table01name    = $e;
    $field01name    = $f;
    $field02name    = $g;
    $searchvalue    = $h;

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
    // $counter = 1;
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
        // $counter++;

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
    $sql = "SELECT * FROM dokumen WHERE (tajuk_dok LIKE '%$kkm%' OR tahun_dok = '$kkm' OR bil_dok = '$kkm' OR des_dok LIKE '%$kkm%') AND tanda_hapus<>0 AND bil_dok<>0";
    // $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$kkm%' OR tahun_dok = '$kkm' OR bil_dok = '$kkm'";
    // $sql = 'SELECT * FROM dokumen WHERE tajuk_dok LIKE "%$kkm%" OR tahun_dok = "$kkm" OR bil_dok = "$kkm"';
    # larikan pernyataan
    $rs=$conn->query($sql);
    # jika $rs benar, kira rekod yang berpadanan
    if($rs === false) {
        trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
    } else {
        $rows_returned = $rs->num_rows;
        if ($rows_returned == 0) {
            $_SESSION['bil_dok_carian_mudah'] = 0;
            // fnRunalert($rows_returned);
            // fnRunalert($_SESSION['bil_dok_carian_mudah']);
        }
        else {
            $_SESSION['bil_dok_carian_mudah'] = $rows_returned;
        }
        // fnRunalert($_SESSION['bil_dok_carian_mudah']." Yo");
    }
}

function fnSearchDocAdvancedRepeat(){
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
    $cl_tajuk_dokumen = $_SESSION['cl_tajuk_dokumen_to_show_below_view'];
    $cl_tahun_dokumen = $_SESSION['cl_tahun_dokumen_to_show_below_view'];
    $kod_kat        = $_SESSION['kod_kat_to_show_below_view'];
    $kod_sektor     = $_SESSION['kod_sektor_to_show_below_view'];
    $kod_bah        = $_SESSION['kod_bah_to_show_below_view'];
    $kod_status     = $_SESSION['kod_status_to_show_below_view'];
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
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 1;
        }
        # 000010
        elseif ($_SESSION['kombinasi_cl_dok'] === 2) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 2;
        }
        # 000011
        elseif ($_SESSION['kombinasi_cl_dok'] === 3) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 3;
        }
        # 000100
        elseif ($_SESSION['kombinasi_cl_dok'] === 4) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 4;
        }
        # 000101
        elseif ($_SESSION['kombinasi_cl_dok'] === 5) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 5;
        }
        # 000110
        elseif ($_SESSION['kombinasi_cl_dok'] === 6) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 6;
        }
        # 000111
        elseif ($_SESSION['kombinasi_cl_dok'] === 7) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 7;
        }
        # 001000
        elseif ($_SESSION['kombinasi_cl_dok'] === 8) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 8;
        }
        # 001001
        elseif ($_SESSION['kombinasi_cl_dok'] === 9) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 9;
        }
        # 001010
        elseif ($_SESSION['kombinasi_cl_dok'] === 10) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 10;
        }
        # 001011
        elseif ($_SESSION['kombinasi_cl_dok'] === 11) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 11;
        }
        # 001100
        elseif ($_SESSION['kombinasi_cl_dok'] === 12) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 12;
        }
        # 001101
        elseif ($_SESSION['kombinasi_cl_dok'] === 13) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 13;
        }
        # 001110
        elseif ($_SESSION['kombinasi_cl_dok'] === 14) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 14;
        }
        # 001111
        elseif ($_SESSION['kombinasi_cl_dok'] === 15) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 15;
        }
        # 010000
        elseif ($_SESSION['kombinasi_cl_dok'] === 16) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 16;
        }
        # 010001
        elseif ($_SESSION['kombinasi_cl_dok'] === 17) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 17;
        }
        # 010010
        elseif ($_SESSION['kombinasi_cl_dok'] === 18) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 18;
        }
        # 010011
        elseif ($_SESSION['kombinasi_cl_dok'] === 19) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 19;
        }
        # 010100
        elseif ($_SESSION['kombinasi_cl_dok'] === 20) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 20;
        }
        # 010101
        elseif ($_SESSION['kombinasi_cl_dok'] === 21) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 21;
        }
        # 010110
        elseif ($_SESSION['kombinasi_cl_dok'] === 22) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 22;
        }
        # 010111
        elseif ($_SESSION['kombinasi_cl_dok'] === 23) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 23;
        }
        # 011000
        elseif ($_SESSION['kombinasi_cl_dok'] === 24) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 24;
        }
        # 011001
        elseif ($_SESSION['kombinasi_cl_dok'] === 25) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 25;
        }
        # 011010
        elseif ($_SESSION['kombinasi_cl_dok'] === 26) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 26;
        }
        # 011011
        elseif ($_SESSION['kombinasi_cl_dok'] === 27) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 27;
        }
        # 011100
        elseif ($_SESSION['kombinasi_cl_dok'] === 28) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 28;
        }
        # 011101
        elseif ($_SESSION['kombinasi_cl_dok'] === 29) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 29;
        }
        # 011110
        elseif ($_SESSION['kombinasi_cl_dok'] === 30) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 30;
        }
        # 011111
        elseif ($_SESSION['kombinasi_cl_dok'] === 31) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 31;
        }
        # 100000
        elseif ($_SESSION['kombinasi_cl_dok'] === 32) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 32;
        }
        # 100001
        elseif ($_SESSION['kombinasi_cl_dok'] === 33) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 33;
        }
        # 100010
        elseif ($_SESSION['kombinasi_cl_dok'] === 34) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 34;
        }
        # 100011
        elseif ($_SESSION['kombinasi_cl_dok'] === 35) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 35;
        }
        # 100100
        elseif ($_SESSION['kombinasi_cl_dok'] === 36) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 36;
        }
        # 100101
        elseif ($_SESSION['kombinasi_cl_dok'] === 37) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 37;
        }
        # 100110
        elseif ($_SESSION['kombinasi_cl_dok'] === 38) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 38;
        }
        # 100111
        elseif ($_SESSION['kombinasi_cl_dok'] === 39) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 39;
        }
        # 101000
        elseif ($_SESSION['kombinasi_cl_dok'] === 40) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 40;
        }
        # 101001
        elseif ($_SESSION['kombinasi_cl_dok'] === 41) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 41;
        }
        # 101010
        elseif ($_SESSION['kombinasi_cl_dok'] === 42) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 42;
        }
        # 101011
        elseif ($_SESSION['kombinasi_cl_dok'] === 43) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 43;
        }
        # 101100
        elseif ($_SESSION['kombinasi_cl_dok'] === 44) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 44;
        }
        # 101101
        elseif ($_SESSION['kombinasi_cl_dok'] === 45) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 45;
        }
        # 101110
        elseif ($_SESSION['kombinasi_cl_dok'] === 46) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 46;
        }
        # 101111
        elseif ($_SESSION['kombinasi_cl_dok'] === 47) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 47;
        }
        # 110000
        elseif ($_SESSION['kombinasi_cl_dok'] === 48) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 48;
        }
        # 110001
        elseif ($_SESSION['kombinasi_cl_dok'] === 49) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 49;
        }
        # 110010
        elseif ($_SESSION['kombinasi_cl_dok'] === 50) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 50;
        }
        # 110011
        elseif ($_SESSION['kombinasi_cl_dok'] === 51) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 51;
        }
        # 110100
        elseif ($_SESSION['kombinasi_cl_dok'] === 52) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 52;
        }
        # 110101
        elseif ($_SESSION['kombinasi_cl_dok'] === 53) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 53;
        }
        # 110110
        elseif ($_SESSION['kombinasi_cl_dok'] === 54) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 54;
        }
        # 110111
        elseif ($_SESSION['kombinasi_cl_dok'] === 55) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 55;
        }
        # 111000
        elseif ($_SESSION['kombinasi_cl_dok'] === 56) {        
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 56;
        }
        # 111001
        elseif ($_SESSION['kombinasi_cl_dok'] === 57) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 57;
        }
        # 111010
        elseif ($_SESSION['kombinasi_cl_dok'] === 58) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 58;
        }
        # 111011        
        elseif ($_SESSION['kombinasi_cl_dok'] === 59) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 59;
        }
        # 111100
        elseif ($_SESSION['kombinasi_cl_dok'] === 60) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 60;
        }
        # 111101
        elseif ($_SESSION['kombinasi_cl_dok'] === 61) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 61;
        }
        # 111110
        elseif ($_SESSION['kombinasi_cl_dok'] === 62) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
            $marker = 62;
        }
        # 111111
        elseif ($_SESSION['kombinasi_cl_dok'] === 63) {
            $sql = "SELECT * FROM dokumen WHERE tanda_hapus = 1 AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab'";
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
    if (isset($_SESSION['kombinasi_cl_dok']) AND $_SESSION['kombinasi_cl_dok'] != "") {
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
            $sql = "SELECT * FROM dokumen WHERE kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 1;
        }
        # 000010
        elseif ($_SESSION['kombinasi_cl_dok'] === 2) {
            $sql = "SELECT * FROM dokumen WHERE kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 2;
        }
        # 000011
        elseif ($_SESSION['kombinasi_cl_dok'] === 3) {
            $sql = "SELECT * FROM dokumen WHERE kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 3;
        }
        # 000100
        elseif ($_SESSION['kombinasi_cl_dok'] === 4) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 4;
        }
        # 000101
        elseif ($_SESSION['kombinasi_cl_dok'] === 5) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 5;
        }
        # 000110
        elseif ($_SESSION['kombinasi_cl_dok'] === 6) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 6;
        }
        # 000111
        elseif ($_SESSION['kombinasi_cl_dok'] === 7) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 7;
        }
        # 001000
        elseif ($_SESSION['kombinasi_cl_dok'] === 8) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 8;
        }
        # 001001
        elseif ($_SESSION['kombinasi_cl_dok'] === 9) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 9;
        }
        # 001010
        elseif ($_SESSION['kombinasi_cl_dok'] === 10) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 10;
        }
        # 001011
        elseif ($_SESSION['kombinasi_cl_dok'] === 11) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 11;
        }
        # 001100
        elseif ($_SESSION['kombinasi_cl_dok'] === 12) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 12;
        }
        # 001101
        elseif ($_SESSION['kombinasi_cl_dok'] === 13) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 13;
        }
        # 001110
        elseif ($_SESSION['kombinasi_cl_dok'] === 14) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 14;
        }
        # 001111
        elseif ($_SESSION['kombinasi_cl_dok'] === 15) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 15;
        }
        # 010000
        elseif ($_SESSION['kombinasi_cl_dok'] === 16) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 16;
        }
        # 010001
        elseif ($_SESSION['kombinasi_cl_dok'] === 17) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 17;
        }
        # 010010
        elseif ($_SESSION['kombinasi_cl_dok'] === 18) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 18;
        }
        # 010011
        elseif ($_SESSION['kombinasi_cl_dok'] === 19) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 19;
        }
        # 010100
        elseif ($_SESSION['kombinasi_cl_dok'] === 20) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 20;
        }
        # 010101
        elseif ($_SESSION['kombinasi_cl_dok'] === 21) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 21;
        }
        # 010110
        elseif ($_SESSION['kombinasi_cl_dok'] === 22) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 22;
        }
        # 010111
        elseif ($_SESSION['kombinasi_cl_dok'] === 23) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 23;
        }
        # 011000
        elseif ($_SESSION['kombinasi_cl_dok'] === 24) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 24;
        }
        # 011001
        elseif ($_SESSION['kombinasi_cl_dok'] === 25) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 25;
        }
        # 011010
        elseif ($_SESSION['kombinasi_cl_dok'] === 26) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 26;
        }
        # 011011
        elseif ($_SESSION['kombinasi_cl_dok'] === 27) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 27;
        }
        # 011100
        elseif ($_SESSION['kombinasi_cl_dok'] === 28) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 28;
        }
        # 011101
        elseif ($_SESSION['kombinasi_cl_dok'] === 29) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 29;
        }
        # 011110
        elseif ($_SESSION['kombinasi_cl_dok'] === 30) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 30;
        }
        # 011111
        elseif ($_SESSION['kombinasi_cl_dok'] === 31) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 31;
        }
        # 100000
        elseif ($_SESSION['kombinasi_cl_dok'] === 32) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 32;
        }
        # 100001
        elseif ($_SESSION['kombinasi_cl_dok'] === 33) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 33;
        }
        # 100010
        elseif ($_SESSION['kombinasi_cl_dok'] === 34) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 34;
        }
        # 100011
        elseif ($_SESSION['kombinasi_cl_dok'] === 35) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 35;
        }
        # 100100
        elseif ($_SESSION['kombinasi_cl_dok'] === 36) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 36;
        }
        # 100101
        elseif ($_SESSION['kombinasi_cl_dok'] === 37) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 37;
        }
        # 100110
        elseif ($_SESSION['kombinasi_cl_dok'] === 38) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 38;
        }
        # 100111
        elseif ($_SESSION['kombinasi_cl_dok'] === 39) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 39;
        }
        # 101000
        elseif ($_SESSION['kombinasi_cl_dok'] === 40) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 40;
        }
        # 101001
        elseif ($_SESSION['kombinasi_cl_dok'] === 41) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 41;
        }
        # 101010
        elseif ($_SESSION['kombinasi_cl_dok'] === 42) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 42;
        }
        # 101011
        elseif ($_SESSION['kombinasi_cl_dok'] === 43) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 43;
        }
        # 101100
        elseif ($_SESSION['kombinasi_cl_dok'] === 44) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 44;
        }
        # 101101
        elseif ($_SESSION['kombinasi_cl_dok'] === 45) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 45;
        }
        # 101110
        elseif ($_SESSION['kombinasi_cl_dok'] === 46) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 46;
        }
        # 101111
        elseif ($_SESSION['kombinasi_cl_dok'] === 47) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 47;
        }
        # 110000
        elseif ($_SESSION['kombinasi_cl_dok'] === 48) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 48;
        }
        # 110001
        elseif ($_SESSION['kombinasi_cl_dok'] === 49) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 49;
        }
        # 110010
        elseif ($_SESSION['kombinasi_cl_dok'] === 50) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 50;
        }
        # 110011
        elseif ($_SESSION['kombinasi_cl_dok'] === 51) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 51;
        }
        # 110100
        elseif ($_SESSION['kombinasi_cl_dok'] === 52) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 52;
        }
        # 110101
        elseif ($_SESSION['kombinasi_cl_dok'] === 53) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 53;
        }
        # 110110
        elseif ($_SESSION['kombinasi_cl_dok'] === 54) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 54;
        }
        # 110111
        elseif ($_SESSION['kombinasi_cl_dok'] === 55) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 55;
        }
        # 111000
        elseif ($_SESSION['kombinasi_cl_dok'] === 56) {        
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 56;
        }
        # 111001
        elseif ($_SESSION['kombinasi_cl_dok'] === 57) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 57;
        }
        # 111010
        elseif ($_SESSION['kombinasi_cl_dok'] === 58) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 58;
        }
        # 111011        
        elseif ($_SESSION['kombinasi_cl_dok'] === 59) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 59;
        }
        # 111100
        elseif ($_SESSION['kombinasi_cl_dok'] === 60) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 60;
        }
        # 111101
        elseif ($_SESSION['kombinasi_cl_dok'] === 61) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 61;
        }
        # 111110
        elseif ($_SESSION['kombinasi_cl_dok'] === 62) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 62;
        }
        # 111111
        elseif ($_SESSION['kombinasi_cl_dok'] === 63) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
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

function fnReportSearchDocAdvanced(){
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    fnRunAlert("Pilihan dimulakan...");
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
    fnRunAlert($cl_tajuk_dokumen);
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
    if (isset($_SESSION['kombinasi_cl_dok']) AND $_SESSION['kombinasi_cl_dok'] != "") {
        // fnRunAlert("isset($_SESSION[kombinasi_cl_dok])");
        // fnRunAlert("Ada kombinasi");
        fnRunAlert("comb fwd=$_SESSION[kombinasi_cl_dok]");
    }
    else {
        // fnRunAlert("Tiada kombinasi");
    }
        ## pilih sql ikut kombinasi
        # 000001
        if ($_SESSION['kombinasi_cl_dok'] === 1) {
            $sql = "SELECT * FROM dokumen WHERE kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 1;
        }
        # 000010
        elseif ($_SESSION['kombinasi_cl_dok'] === 2) {
            $sql = "SELECT * FROM dokumen WHERE kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 2;
        }
        # 000011
        elseif ($_SESSION['kombinasi_cl_dok'] === 3) {
            $sql = "SELECT * FROM dokumen WHERE kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 3;
        }
        # 000100
        elseif ($_SESSION['kombinasi_cl_dok'] === 4) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 4;
        }
        # 000101
        elseif ($_SESSION['kombinasi_cl_dok'] === 5) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 5;
        }
        # 000110
        elseif ($_SESSION['kombinasi_cl_dok'] === 6) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 6;
        }
        # 000111
        elseif ($_SESSION['kombinasi_cl_dok'] === 7) {
            $sql = "SELECT * FROM dokumen WHERE kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 7;
        }
        # 001000
        elseif ($_SESSION['kombinasi_cl_dok'] === 8) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 8;
        }
        # 001001
        elseif ($_SESSION['kombinasi_cl_dok'] === 9) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 9;
        }
        # 001010
        elseif ($_SESSION['kombinasi_cl_dok'] === 10) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 10;
        }
        # 001011
        elseif ($_SESSION['kombinasi_cl_dok'] === 11) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 11;
        }
        # 001100
        elseif ($_SESSION['kombinasi_cl_dok'] === 12) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 12;
        }
        # 001101
        elseif ($_SESSION['kombinasi_cl_dok'] === 13) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 13;
        }
        # 001110
        elseif ($_SESSION['kombinasi_cl_dok'] === 14) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 14;
        }
        # 001111
        elseif ($_SESSION['kombinasi_cl_dok'] === 15) {
            $sql = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 15;
        }
        # 010000
        elseif ($_SESSION['kombinasi_cl_dok'] === 16) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 16;
        }
        # 010001
        elseif ($_SESSION['kombinasi_cl_dok'] === 17) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 17;
        }
        # 010010
        elseif ($_SESSION['kombinasi_cl_dok'] === 18) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 18;
        }
        # 010011
        elseif ($_SESSION['kombinasi_cl_dok'] === 19) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 19;
        }
        # 010100
        elseif ($_SESSION['kombinasi_cl_dok'] === 20) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 20;
        }
        # 010101
        elseif ($_SESSION['kombinasi_cl_dok'] === 21) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 21;
        }
        # 010110
        elseif ($_SESSION['kombinasi_cl_dok'] === 22) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 22;
        }
        # 010111
        elseif ($_SESSION['kombinasi_cl_dok'] === 23) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 23;
        }
        # 011000
        elseif ($_SESSION['kombinasi_cl_dok'] === 24) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 24;
        }
        # 011001
        elseif ($_SESSION['kombinasi_cl_dok'] === 25) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 25;
        }
        # 011010
        elseif ($_SESSION['kombinasi_cl_dok'] === 26) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 26;
        }
        # 011011
        elseif ($_SESSION['kombinasi_cl_dok'] === 27) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 27;
        }
        # 011100
        elseif ($_SESSION['kombinasi_cl_dok'] === 28) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 28;
        }
        # 011101
        elseif ($_SESSION['kombinasi_cl_dok'] === 29) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 29;
        }
        # 011110
        elseif ($_SESSION['kombinasi_cl_dok'] === 30) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 30;
        }
        # 011111
        elseif ($_SESSION['kombinasi_cl_dok'] === 31) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 31;
        }
        # 100000
        elseif ($_SESSION['kombinasi_cl_dok'] === 32) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 32;
            // fnRunAlert($marker);
        }
        # 100001
        elseif ($_SESSION['kombinasi_cl_dok'] === 33) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 33;
        }
        # 100010
        elseif ($_SESSION['kombinasi_cl_dok'] === 34) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 34;
        }
        # 100011
        elseif ($_SESSION['kombinasi_cl_dok'] === 35) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 35;
        }
        # 100100
        elseif ($_SESSION['kombinasi_cl_dok'] === 36) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 36;
        }
        # 100101
        elseif ($_SESSION['kombinasi_cl_dok'] === 37) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 37;
        }
        # 100110
        elseif ($_SESSION['kombinasi_cl_dok'] === 38) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 38;
        }
        # 100111
        elseif ($_SESSION['kombinasi_cl_dok'] === 39) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 39;
        }
        # 101000
        elseif ($_SESSION['kombinasi_cl_dok'] === 40) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 40;
        }
        # 101001
        elseif ($_SESSION['kombinasi_cl_dok'] === 41) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 41;
        }
        # 101010
        elseif ($_SESSION['kombinasi_cl_dok'] === 42) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 42;
        }
        # 101011
        elseif ($_SESSION['kombinasi_cl_dok'] === 43) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 43;
        }
        # 101100
        elseif ($_SESSION['kombinasi_cl_dok'] === 44) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 44;
        }
        # 101101
        elseif ($_SESSION['kombinasi_cl_dok'] === 45) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 45;
        }
        # 101110
        elseif ($_SESSION['kombinasi_cl_dok'] === 46) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 46;
        }
        # 101111
        elseif ($_SESSION['kombinasi_cl_dok'] === 47) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 47;
        }
        # 110000
        elseif ($_SESSION['kombinasi_cl_dok'] === 48) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 48;
        }
        # 110001
        elseif ($_SESSION['kombinasi_cl_dok'] === 49) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 49;
        }
        # 110010
        elseif ($_SESSION['kombinasi_cl_dok'] === 50) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 50;
        }
        # 110011
        elseif ($_SESSION['kombinasi_cl_dok'] === 51) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 51;
        }
        # 110100
        elseif ($_SESSION['kombinasi_cl_dok'] === 52) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 52;
        }
        # 110101
        elseif ($_SESSION['kombinasi_cl_dok'] === 53) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 53;
        }
        # 110110
        elseif ($_SESSION['kombinasi_cl_dok'] === 54) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 54;
        }
        # 110111
        elseif ($_SESSION['kombinasi_cl_dok'] === 55) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 55;
        }
        # 111000
        elseif ($_SESSION['kombinasi_cl_dok'] === 56) {        
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 56;
        }
        # 111001
        elseif ($_SESSION['kombinasi_cl_dok'] === 57) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 57;
        }
        # 111010
        elseif ($_SESSION['kombinasi_cl_dok'] === 58) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 58;
        }
        # 111011        
        elseif ($_SESSION['kombinasi_cl_dok'] === 59) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 59;
        }
        # 111100
        elseif ($_SESSION['kombinasi_cl_dok'] === 60) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 60;
        }
        # 111101
        elseif ($_SESSION['kombinasi_cl_dok'] === 61) {
            $sql = "SELECT * FROM dokumen WHERE tahun_dok LIKE '%$cl_tahun_dokumen%' AND tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 61;
        }
        # 111110
        elseif ($_SESSION['kombinasi_cl_dok'] === 62) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%' AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
            $marker = 62;
        }
        # 111111
        elseif ($_SESSION['kombinasi_cl_dok'] === 63) {
            $sql = "SELECT * FROM dokumen WHERE tajuk_dok LIKE '%$cl_tajuk_dokumen%' OR des_dok LIKE '%$cl_tajuk_dokumen%'  AND tahun_dok LIKE '%$cl_tahun_dokumen%' AND kod_kat = '$kod_kat' AND kod_sektor = '$kod_sektor' AND kod_bah = '$kod_bah' AND kod_status = '$kod_status' AND kod_kem = '$kod_kem' AND kod_jab = '$kod_jab' AND tanda_hapus<>0 AND bil_dok<>0";
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
            fnRunAlert($rows_returned);
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
        $sql_bil_dok_dgn_kat_ini = "SELECT * FROM dokumen WHERE kod_kat = '$kod_kat' AND tanda_hapus<>0";
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
    $sql = "SELECT * FROM dokumen WHERE tanda_hapus<>0";
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
    $sql = "SELECT * FROM dokumen WHERE kod_status = '2' AND tanda_hapus<>0";
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
    $sql = "SELECT * FROM dokumen WHERE kod_status = '3' AND tanda_hapus<>0";
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
    $sql = "SELECT * FROM dokumen WHERE kod_status = '4' AND tanda_hapus<>0";
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

function fnCountAmmendedDocInRep(){
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
    $sql = "SELECT * FROM dokumen WHERE kod_status = '5' AND tanda_hapus<>0";
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

### Search Document Page

function fnClearSimpleDocSearchSessions(){
    $_SESSION['kata_kunci_mudah'] = "";
}

function fnClearAdvancedDocSearchSessions(){
    $_SESSION['cl_tajuk_dokumen'] = "";
    $_SESSION['cl_tahun_dokumen'] = "";
    $_SESSION['kod_kat'] = 1;
    $_SESSION['kod_sektor'] = 1;
    $_SESSION['kod_bah'] = 1;
    $_SESSION['kod_status'] = 1;
    $_SESSION['bil_dok_carian_lengkap'] = "z";
}

function fnClearSimpleDocSearchResult(){
    $_SESSION['bil_dok_carian_mudah'] = "z";
    unset($_SESSION['bil_dok_carian_mudah']);
}

function fnClearAdvancedDocSearchResult(){
    $_SESSION['bil_dok_carian_lengkap'] = "z";
    unset($_SESSION['bil_dok_carian_lengkap']);
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
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    $loginname      = $e;
    $loginpwd       = $f;
    // fnRunAlert($DBName);
    $DBServer       = $_SESSION['DBServer'];
    $DBUser         = $_SESSION['DBUser'];
    $DBPass         = $_SESSION['DBPass'];
    $DBName         = $_SESSION['DBName'];
    fnRunAlert($_SESSION['DBServer']);
    fnRunAlert($_SESSION['DBUser']);
    fnRunAlert($_SESSION['DBPass']);
    fnRunAlert($_SESSION['DBName']);
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
        // fnRunAlert("OK Jumpa User!");
        // fnRunAlert("Alert 2!");
        // $sql="SELECT * FROM pengguna WHERE nama_pengguna LIKE '$loginname' AND kata_laluan LIKE '$loginpwd' AND status_pengguna = 1";
        // $sql="SELECT * FROM pengguna WHERE nama_pengguna LIKE $loginname AND kata_laluan LIKE $loginpwd AND status_pengguna = '1'";
        $sql="SELECT * FROM pengguna WHERE nama_pengguna LIKE '$loginname' AND kata_laluan LIKE '$loginpwd' AND status_pengguna = 1";
        // fnRunAlert("Alert 3!");
        // $conn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
        $rs=$conn->query($sql);
        // fnRunAlert("$rs");
        // fnRunAlert("Alert 4!");

        if($rs === "false") {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $conn->error, E_USER_ERROR);
            // fnRunAlert("Error");
            // fnRunAlert("Alert 5a!");
        } else {
            $arr = $rs->fetch_all(MYSQLI_ASSOC);
            // fnRunAlert("Mendapatkan maklumat.");
            // fnRunAlert("Alert 5b!");
        }

        foreach($arr as $row) {
            $_SESSION['loggedinid'] = $row['id_pengguna'];        
            $_SESSION['loggedinname'] = $row['nama_pengguna'];        
            $_SESSION['loggedin_nama_penuh'] = $row['nama_penuh'];
            $_SESSION['status_pentadbir_sistem'] = $row['pentadbir_sistem'];        
            $_SESSION['status_pentadbir_pengguna'] = $row['pentadbir_pengguna'];        
            $_SESSION['status_pentadbir_dokumen'] = $row['pentadbir_dokumen'];
            $_SESSION['status_pentadbir_super'] = $row['pentadbir_super'];
            $_SESSION['loggedin_kod_kem'] = $row['kod_kem'];
            $_SESSION['loggedin_kod_jab'] = $row['kod_jab'];
            $sql_gel_nama="SELECT * FROM gelaran_nama WHERE kod_gelaran_nama = '$row[kod_gelaran_nama]' ";
            $rs_gel_nama=$conn->query($sql_gel_nama);

            if($rs_gel_nama === false) {
                trigger_error('Wrong SQL: ' . $sql_gel_nama . ' Error: ' . $conn->error, E_USER_ERROR);
            } else {
                $arr_gel_nama = $rs_gel_nama->fetch_all(MYSQLI_ASSOC);
            }
            foreach($arr_gel_nama as $row_gel_nama) {
                if ($row['kod_gelaran_nama'] == 1) {
                    $_SESSION['loggedin_gel_nama'] = "";
                }
                else {
                    $_SESSION['loggedin_gel_nama'] = $row_gel_nama['gelaran_nama'];
                }
            }
            // fnRunAlert($_SESSION['loggedin_gel_nama']);
        }
        $_SESSION['loginstatus'] = 1;
        // fnRunAlert("Status login = ".$_SESSION['loginstatus']);
        // fnRunAlert("Pengguna telah disahkan.");
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

# Clear sessions for doclist search
function fnClearSessionForDoclistSearch(){
  $_SESSION['btn_search_doclist'] = "";
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
  $_SESSION['catatan_dokumen'] = "";
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
    $_SESSION["msgforfnalert"] = "$a";
    fnAlert();
}

function fnRunConfirm2($a){
    $_SESSION['msgforfnconfirm'] = "$a";
    fnConfirm();
}

function fnAlert(){
    ?>
    <script>
        alert("<?php echo $_SESSION['msgforfnalert']; ?>");
    </script>
    <?php
    $_SESSION['msgforfnalert']="";
}

function fnConfirm(){
    ?>
    <script>
        confirm("<?php echo $_SESSION['msgforfnconfirm']; ?>");
    </script>
    <?php
    $_SESSION['msgforfnconfirm']="";
}

function fnRunConfirm($a){
    $_SESSION['msgtoconfirm'] = "$a";
    ?>
    <script>
        var r = confirm("<?php echo $_SESSION['msgtoconfirm']; ?>");
        if (r == true) {
            <?php $_SESSION['value_confirm_pressed'] = 1; ?>
            alert("You pressed OK! <?php // echo $_SESSION['value_confirm_pressed']; ?>");
        } else {
            <?php $_SESSION['value_confirm_pressed'] = 0; ?>
            alert("You pressed Cancel! <?php // echo $_SESSION['value_confirm_pressed']; ?>");
        }
    </script>
    <?php
    $_SESSION['msgtoconfirm']="";
}

function fnRunConfirmDeleteDoc3($a){
    $_SESSION['msgtoconfirm'] = "$a";
    ?>
    <script>
        var r = confirm("<?php echo $_SESSION['msgtoconfirm']; ?>");
        if (r == true) {
            <?php $_SESSION['value_confirm_delete_doc_pressed'] = 1; ?>
            alert("Anda telah bersetuju untuk memadam rekod! ");/*<?php // echo $_SESSION['value_confirm_delete_doc_pressed']." ".$_SESSION['kod_dok_to_delete_now']; ?>*/
            <?php fnRunAlert($_SESSION['value_confirm_delete_doc_pressed']); ?>
            <?php if ($_SESSION['value_confirm_delete_doc_pressed'] == 1) { /*fnDeleteDoc();*/ alert("Rekod dan dokumen telah dipadamkan daripada pangkalan data."); } ?>
        } 
        else {
            <?php $_SESSION['value_confirm_delete_doc_pressed'] = 0; ?>
            alert("Anda telah membatalkan pemadaman dokumen! "<?php // echo $_SESSION['value_confirm_delete_doc_pressed']; ?>);
        }
    </script>
    <?php
    $_SESSION['msgtoconfirm']="";
}

function fnRunConfirmDeleteDoc2($a){
    $_SESSION['msgtoconfirm'] = "$a";
    $_SESSION['value_confirm_delete_doc_pressed'] = "";
    ?>
    <script>
        alert("<?php echo $_SESSION['value_confirm_delete_doc_pressed']; ?>");
        var r = confirm("<?php echo $_SESSION['msgtoconfirm']; ?>");
        var c = "";
        alert(r);
        if (r == true) {
            c = "y";
            <?php $_SESSION['value_confirm_delete_doc_pressed'] = "a" ?>
            alert("Anda telah bersetuju untuk memadam rekod! <?php echo $_SESSION['value_confirm_delete_doc_pressed'] ?>");
            <?php $_SESSION['value_confirm_delete_doc_pressed'] = "a" ?>
        } else if (r == false) {
            c = "n";
            <?php $_SESSION['value_confirm_delete_doc_pressed'] = "b"; ?>
            alert("Anda telah membatalkan pemadaman dokumen! <?php echo $_SESSION['value_confirm_delete_doc_pressed']; ?>");
        }
        alert("<?php echo $_SESSION['value_confirm_delete_doc_pressed']; ?>");
        alert(c);
        if (c == 'y') { 
            alert("ok");
        } else { 
            alert("not ok")
        }
    </script>
    <?php
    if ($_SESSION['value_confirm_delete_doc_pressed'] == "a") { 
        /*fnDeleteDoc();*/ 
        fnRunAlert("Rekod dan dokumen telah dipadamkan daripada pangkalan data. $_SESSION[value_confirm_delete_doc_pressed]"); 
        // fnDeleteDoc2(); 
    }
    else {
        fnRunAlert("Rekod dan dokumen masih wujud di pangkalan data. $_SESSION[value_confirm_delete_doc_pressed]"); 
    }
    $_SESSION['msgtoconfirm']="";
}

// set default timezone to Asia/Kuala_lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');
?>
