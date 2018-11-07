<!-- resources/views/layouts/navbar.blade.php -->

        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <!--<a id="menu_toggle"><i class="fa fa-bars"></i></a>-->
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">
                  <a href="../external/login.php?a=9" class="user-profile dropdown-toggle" data-toggle="none" aria-expanded="true">
                  <!-- <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="true"> -->
                    <!-- <img src="../images/img.jpg" alt=""> -->
                    <?php //echo $_SESSION['loggedinname']; ?>
                    <?php echo "Log Keluar ".$_SESSION['loggedin_gel_nama']." ".$_SESSION['loggedin_nama_penuh']; ?>
                    <span hidden class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <!--<li><a href="javascript:;"> Profile</a></li>
                    <li>
                      <a href="javascript:;">
                        <span class="badge bg-red pull-right">50%</span>
                        <span>Settings</span>
                      </a>
                    </li>
                    <li><a href="javascript:;">Help</a></li>-->
                    <li>
                      <a href="../external/login.php?a=9"><i class="fa fa-sign-out pull-right"></i> Log Keluar</a>
                    </li>
                  </ul>
                </li>

              </ul>
            </nav>
          </div>
        </div>
