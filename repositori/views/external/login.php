<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php include "../../title.php"; ?></title>
	<?php require "../engine/mysqlidbconnect.php"; ?>
	<?php 
	require "../engine/function.php"; 
	fnRunAlert("Telah disambungkan ke function.php");
	?>
	<?php require "../layouts/lay_csslink.php"; ?>
	<?php  

	# When a user clicks logout
	if (isset($_GET['a']) == 9) {
		$_SESSION['loginstatus'] = 0;
		$_SESSION['loggedinname'] = "";
		unset($_SESSION['loggedinname']);
		$_SESSION['loggedinid'] = "";
		unset($_SESSION['loggedinid']);
		$_SESSION['status_pentadbir_sistem'] = "";
		unset($_SESSION['status_pentadbir_sistem']);
		$_SESSION['status_pentadbir_pengguna'] = "";
		unset($_SESSION['status_pentadbir_pengguna']);
		$_SESSION['status_pentadbir_dokumen'] = "";
		unset($_SESSION['status_pentadbir_dokumen']);
		session_destroy();
	}

	# When a user clicks login
	if (isset($_POST['sbmt_login'])) {
		## verify login credentials
		## assign sessions
		## forward to landing page
		$loginname = $_POST['txt_nama'];
		$loginpwd = $_POST['pwd_katalaluan'];
		fnVerifyLogin($DBServer,$DBUser,$DBPass,$DBName,$loginname,$loginpwd);
		fnRunAlert($_SESSION['loginstatus']);
		if ($_SESSION['loginstatus'] === 1) {
			fnRefreshAndRedirectPgMeta("../sysmgmt/dashboard.php");
		}
	}

	# this was temporary login process
	// if ($_POST['txt_nama']) {
		// $inputtxtnama = $_POST['txt_nama'];
		// $_SESSION['txt_nama'] = $inputtxtnama;
		// $slashedinputtxtnama = addslashes($inputtxtnama);
		// $_SESSION['slashedtxtnama'] = $slashedinputtxtnama;
		// $_SESSION['loginstatus'] = 1;
		// $_SESSION['loggedinname'] = $inputtxtnama;
		// $_SESSION['loggedinid'] = 1;
		// fnFwdToLandingPg();
		// fnRefreshAndRedirectPgMeta("../layouts/lay_plainpagecontent.php");
	// }

	?>
</head>

<body class="login">
	<div>
		<a class="hiddenanchor" id="signup"></a>
		<a class="hiddenanchor" id="signin"></a>

		<div class="login_wrapper">
			<div class="animate form login_form">
				<section>
					<div id="system_logo">
						<img src="../images/logo.png" style="border: 0px solid blue; margin: 10px 20px 5px -75px ">
					</div>
				</section>
				<section class="login_content">
					<form action="login.php" method="post" accept-charset="utf-8">
						<h1>Borang Log Masuk</h1>
						<div>
							<input type="text" name="txt_nama" id="txt_nama" class="form-control" placeholder="Nama Pengguna" required="" />
						</div>
						<div>
							<input type="password" name="pwd_katalaluan" id="pwd_katalaluan" class="form-control" placeholder="Katalaluan" required="" />
						</div>
						<div>
							<input type="submit" name="sbmt_login" id="sbmt_login" value="Log Masuk" class="btn btn-default submit form-control" style="margin-left: 0px;">
							<a class="reset_pass" href="#" hidden>Lupa katalaluan?</a>
						</div>

						<div class="clearfix"></div>

						<div class="separator">
							<!-- <p class="change_link">Tiada akaun? -->
								<!-- <a href="#signup" class="to_register"> Buka Akaun Baru </a> -->
							<!-- </p> -->

							<div class="clearfix"></div>
							<br />

							<?php require "../layouts/lay_loginfooter.php"; ?>
						</div>
					</form>
				</section>
			</div>

			<div id="register" class="animate form registration_form">
				<section class="login_content">
					<form>
						<h1>Buka Akaun Baru</h1>
						<div>
							<input type="text" class="form-control" placeholder="Username" required="" />
						</div>
						<div>
							<input type="email" class="form-control" placeholder="Email" required="" />
						</div>
						<div>
							<input type="password" class="form-control" placeholder="Password" required="" />
						</div>
						<div>
							<a class="btn btn-default submit" href="index.html">Hantar</a>
						</div>

						<div class="clearfix"></div>

						<div class="separator">
							<p class="change_link">Telah mempunyai akaun?
								<a href="#signin" class="to_register"> Log masuk </a>
							</p>

							<div class="clearfix"></div>
							<br />

							<?php require "../layouts/lay_loginfooter.php"; ?>
						</div>
					</form>
				</section>
			</div>
		</div>
	</div>
</body>
</html>