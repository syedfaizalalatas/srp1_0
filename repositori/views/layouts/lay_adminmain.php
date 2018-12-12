<!-- resources/views/layouts/appmain.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sistem Repositori Pekeliling 1.1</title>

    <?php require "../layouts/lay_csslink.php"; ?>
</head>

<body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <?php include "../lay_sidebar.php"; ?>
        <!-- top navigation -->
        <?php include "../lay_navbar.php"; ?>
        <!-- /top navigation -->

        <!-- page content -->
        @yield('content')
        <!-- /page content -->

        <!-- footer content -->
        <?php include "../lay_footer.php"; ?>
        <!-- /footer content -->
   </div>
</div>

<!-- jQuery -->
<script src="../assets/vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="../assets/vendors/fastclick/lib/fastclick.js"></script>
<!-- NProgress -->
<script src="../assets/vendors/nprogress/nprogress.js"></script>

<!-- Custom Theme Scripts -->
<script src="../assets/build/js/custom.min.js"></script>
</body>
</html>
