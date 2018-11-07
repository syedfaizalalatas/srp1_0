<?php session_start(); ?>
<?php require "../engine/function.php"; ?>
<?php require "../engine/mysqlidbconnect.php"; ?>
<?php // require "../layouts/lay_adminmaintop.php"; ?>
<?php  

function test(){
    echo "<br/>test";
}

test();


testMysqliOps($DBServer,$DBUser,$DBPass,$DBName);
?>
<!-- page content -->
<!-- /page content -->
<?php require "../layouts/lay_adminmainbottom.php"; ?>