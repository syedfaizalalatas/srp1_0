<!-- resources/views/layouts/appmain.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <!-- Meta, title, CSS, favicons, etc. -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php include "../../title.php"; ?></title>

  <?php require "../engine/mysqlidbconnect.php"; ?>
  <?php require "../engine/agency_info.php"; ?>
  <?php require "../engine/function.php"; ?>
  <?php require "lay_csslink.php"; ?>
  <?php  
  fnCheckLoginStatus();
  fnGetSystemSettingInfo();
  ?>
  <!-- untuk upload form -->
  <!-- <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css"> -->
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
  <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
  <link rel="stylesheet" href="../css/uploadmultiple.css">
  <script src="../js/uploadmultiple.js"></script>
  <!-- /untuk upload form -->
</head>

<body class="nav-md footer_fixed">
  <div class="container body">
    <div class="main_container">
     <?php include "lay_sidebarfull.php"; ?>
     <!-- top navigation -->
     <?php include "lay_navbar.php"; ?>
     <!-- /top navigation -->

     <!-- page content -->
