        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="../sysmgmt/dashboard.php" class="site_title" style="display: none;"><i class="fa fa-globe"></i> <span>SRP 1.0</span></a>
              <br>
              <img src="../images/logo.png" height="55" width="240" style="border: 0px solid blue; margin: -20px 10px 10px -10px; float: left; size: 20px; background-color: white;">
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->
            <div class="profile">
              <div class="profile_info">
                <span>Selamat Datang,</span>
                <!-- <h2><?php echo $_SESSION['loggedinname']." [".$_SESSION['loggedinid']."]"; ?>&nbsp;</h2> -->
                <h2><?php echo $_SESSION['loggedinname']; ?>&nbsp;</h2>
              </div>
              <div class="profile_pic">
              <!-- <img src="../images/img.jpg" alt="..." class="img-circle profile_img"> -->
              &nbsp;
              </div>
            </div>
            <!-- /menu profile quick info -->

            <div class="clearfix"></div>

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <!-- <h3>Dokumen</h3> -->
                <?php  
                if ($_SESSION['loggedinid'] !== "" AND $_SESSION['loggedinname'] !== "") {
                  ?>
                  <ul class="nav side-menu">
                    <li>
                      <a href="../sysmgmt/dashboard.php"><i class="fa fa-home"></i> Utama <!-- <span class="fa fa-chevron-down"></span> --></a>
                      <!-- <ul class="nav child_menu"> -->
                        <!-- <li><a href="{{url('/dash')}}">Dashboard</a></li> -->
                      <!-- </ul> -->
                    </li>
                    <?php  
                    if ($_SESSION['status_pentadbir_dokumen'] == 2) {
                      ?>
                      <li><a><i class="fa fa-book"></i> Dokumen <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="../docsmgmt/newdoc.php?s=n"><i class="fa fa-edit"></i>Pendaftaran Dokumen</a></li><!-- s=n (source=new) -->
                          <li><a href="../docsmgmt/newdoc_1.php?s=n"><i class="fa fa-edit"></i>Pendaftaran Dokumen Trial</a></li><!-- s=n (source=new) -->
                          <!-- <li><a href="../docsmgmt/listdoc.php?s=n"><i class="fa fa-table"></i>Senarai Dokumen Alt</a></li> -->
                          <!-- <li><a href="../docsmgmt/listdoc_o.php?s=n"><i class="fa fa-table"></i>Senarai Dokumen Asal</a></li> -->
                          <li><a href="../docsmgmt/searchdoc.php?s=n"><i class="fa fa-search"></i>Carian Dokumen</a></li>
                          <!-- <li><a href="../docsmgmt/searchdoc_o.php?s=n"><i class="fa fa-search"></i>Carian Dokumen Asal</a></li> -->
                          <li><a href="../docsmgmt/reportsearchdoc.php?s=n"><i class="fa fa-file-alt"></i>Laporan Dokumen</a></li>
                          <li><a><i class="fa fa-bar-chart"></i>Statistik Dokumen<span class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                              <!-- <li><a href="../docsmgmt/docreport_all.php?s=n">Keseluruhan Dokumen</a></li> -->
                              <li><a href="../docsmgmt/docreport_cat.php">Statistik Kategori</a></li><!-- remove ?s=n untuk mengekalkan chevron open -->
                              <li><a href="../docsmgmt/docreport_div.php">Statistik Bahagian</a></li>
                              <li><a href="../docsmgmt/docreport_sec.php">Statistik Sektor</a></li>
                              <li><a href="../docsmgmt/docreport_stat.php">Statistik Status</a></li>
                              <li><a href="../docsmgmt/docreport_year.php">Statistik Tahun</a></li>
                            </ul>
                          </li>
                        </ul>
                      </li>
                      <?php
                    }
                    ?>
                    <?php  
                    if ($_SESSION['status_pentadbir_dokumen'] == 2) {
                      ?>
                      <li><a><i class="fa fa-book"></i> Dokumen II <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li>
                            <a href="../docsmgmt/newdoc.php">
                              <i class="fa fa-edit"></i>
                              Pendaftaran Dokumen
                            </a>
                          </li>
                          <li><a href="../sysmgmt/categorymgmt.php"><i class="fa fa-table"></i>Kategori Dokumen</a></li>
                          <li><a href="../sysmgmt/ministrymgmt.php"><i class="fa fa-table"></i>Kementerian</a></li>
                          <li><a href="../sysmgmt/agencymgmt.php"><i class="fa fa-table"></i>Jabatan / Agensi</a></li>
                          <li><a href="../sysmgmt/divisionmgmt.php"><i class="fa fa-table"></i>Bahagian</a></li>
                          <li><a href="../sysmgmt/docstatusmgmt.php"><i class="fa fa-table"></i>Status Dokumen</a></li>
                          <li><a href="../sysmgmt/strategiccoremgmt.php"><i class="fa fa-table"></i>Teras Strategik</a></li>
                          <li><a href="../sysmgmt/sectormgmt.php"><i class="fa fa-table"></i>Sektor</a></li>
                          <!-- <li><a href="../sysmgmt/datamgmttemplate.php">Template</a></li> -->
                          <!-- <li><a href="../sysmgmt/datamgmtcleantemplate.php">Template Clean</a></li> -->
                          <li><a href="../sysmgmt/nametitlemgmt.php"><i class="fa fa-table"></i>Gelaran Nama</a></li>
                          <!-- <li><a href="../sysmgmt/testfilename.php"><i class="fa fa-table"></i>Test Filename</a></li> -->
                        </ul>
                      </li>
                      <?php
                    }
                    ?>
                    <?php  
                    if ($_SESSION['status_pentadbir_pengguna'] == 3) {
                      ?>
                      <li><a><i class="fa fa-users"></i> Pentadbir <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="../usersmgmt/newuser.php?s=n"><i class="fa fa-edit"></i>Pendaftaran Pentadbir</a></li>
                          <li><a href="../usersmgmt/listuser.php"><i class="fa fa-table"></i>Senarai Pentadbir</a></li>
                        </ul>
                      </li>
                      <?php
                    }
                    ?>
                    <?php  
                    if ($_SESSION['status_pentadbir_sistem'] == 1) {
                      ?>
                      <li><a><i class="fa fa-gear"></i> Sistem <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="../sysmgmt/categorymgmt.php"><i class="fa fa-table"></i>Kategori Dokumen</a></li>
                          <li><a href="../sysmgmt/ministrymgmt.php"><i class="fa fa-table"></i>Kementerian</a></li>
                          <li><a href="../sysmgmt/agencymgmt.php"><i class="fa fa-table"></i>Jabatan / Agensi</a></li>
                          <li><a href="../sysmgmt/divisionmgmt.php"><i class="fa fa-table"></i>Bahagian</a></li>
                          <li><a href="../sysmgmt/docstatusmgmt.php"><i class="fa fa-table"></i>Status Dokumen</a></li>
                          <li><a href="../sysmgmt/strategiccoremgmt.php"><i class="fa fa-table"></i>Teras Strategik</a></li>
                          <li><a href="../sysmgmt/sectormgmt.php"><i class="fa fa-table"></i>Sektor</a></li>
                          <!-- <li><a href="../sysmgmt/datamgmttemplate.php">Template</a></li> -->
                          <!-- <li><a href="../sysmgmt/datamgmtcleantemplate.php">Template Clean</a></li> -->
                          <li><a href="../sysmgmt/nametitlemgmt.php"><i class="fa fa-table"></i>Gelaran Nama</a></li>
                          <!-- <li><a href="../sysmgmt/testfilename.php"><i class="fa fa-table"></i>Test Filename</a></li> -->
                        </ul>
                      </li>
                      <?php
                    }
                    ?>
                    <!-- <li><a><i class="fa fa-edit"></i> Forms <span class="fa fa-chevron-down"></span></a> -->
                    <!-- <ul class="nav child_menu"> -->
                    <!-- <li><a href="form.html">General Form</a></li> -->
                    <!-- <li><a href="form_advanced.html">Advanced Components</a></li> -->
                    <!-- <li><a href="form_validation.html">Form Validation</a></li> -->
                    <!-- <li><a href="form_wizards.html">Form Wizard</a></li> -->
                    <!-- <li><a href="form_upload.html">Form Upload</a></li> -->
                    <!-- <li><a href="form_buttons.html">Form Buttons</a></li> -->
                    <!-- </ul> -->
                    <!-- </li> -->
                    <!-- <li><a><i class="fa fa-desktop"></i> UI Elements <span class="fa fa-chevron-down"></span></a> -->
                    <!-- <ul class="nav child_menu"> -->
                    <!-- <li><a href="general_elements.html">General Elements</a></li> -->
                    <!-- <li><a href="media_gallery.html">Media Gallery</a></li> -->
                    <!-- <li><a href="typography.html">Typography</a></li> -->
                    <!-- <li><a href="icons.html">Icons</a></li> -->
                    <!-- <li><a href="glyphicons.html">Glyphicons</a></li> -->
                    <!-- <li><a href="widgets.html">Widgets</a></li> -->
                    <!-- <li><a href="invoice.html">Invoice</a></li> -->
                    <!-- <li><a href="inbox.html">Inbox</a></li> -->
                    <!-- <li><a href="calendar.html">Calendar</a></li> -->
                    <!-- </ul> -->
                    <!-- </li> -->
                    <!-- <li><a><i class="fa fa-table"></i> Tables <span class="fa fa-chevron-down"></span></a> -->
                    <!-- <ul class="nav child_menu"> -->
                    <!-- <li><a href="tables.html">Tables</a></li> -->
                    <!-- <li><a href="tables_dynamic.html">Table Dynamic</a></li> -->
                    <!-- </ul> -->
                    <!-- </li> -->
                    <!-- <li><a><i class="fa fa-bar-chart-o"></i> Data Presentation <span class="fa fa-chevron-down"></span></a> -->
                    <!-- <ul class="nav child_menu"> -->
                    <!-- <li><a href="chartjs.html">Chart JS</a></li> -->
                    <!-- <li><a href="chartjs2.html">Chart JS2</a></li> -->
                    <!-- <li><a href="morisjs.html">Moris JS</a></li> -->
                    <!-- <li><a href="echarts.html">ECharts</a></li> -->
                    <!-- <li><a href="other_charts.html">Other Charts</a></li> -->
                    <!-- </ul> -->
                    <!-- </li> -->
                    <!-- <li><a><i class="fa fa-clone"></i>Layouts <span class="fa fa-chevron-down"></span></a> -->
                    <!-- <ul class="nav child_menu"> -->
                    <!-- <li><a href="fixed_sidebar.html">Fixed Sidebar</a></li> -->
                    <!-- <li><a href="fixed_footer.html">Fixed Footer</a></li> -->
                    <!-- </ul> -->
                    <!-- </li> -->
                  </ul>
                  <?php
                }
                ?>
              </div>
              <div class="menu_section">
                <!-- <h3>Pengguna</h3> -->
                <ul class="nav side-menu">
                  <!-- <li><a><i class="fa fa-bug"></i> Additional Pages <span class="fa fa-chevron-down"></span></a> -->
                  <!-- <ul class="nav child_menu"> -->
                  <!-- <li><a href="e_commerce.html">E-commerce</a></li> -->
                  <!-- <li><a href="projects.html">Projects</a></li> -->
                  <!-- <li><a href="project_detail.html">Project Detail</a></li> -->
                  <!-- <li><a href="contacts.html">Contacts</a></li> -->
                  <!-- <li><a href="profile.html">Profile</a></li> -->
                  <!-- </ul> -->
                  <!-- </li> -->
                  <!-- <li><a><i class="fa fa-windows"></i> Extras <span class="fa fa-chevron-down"></span></a> -->
                    <!-- <ul class="nav child_menu"> -->
                      <!-- <li><a href="page_403.html">403 Error</a></li> -->
                      <!-- <li><a href="page_404.html">404 Error</a></li> -->
                      <!-- <li><a href="page_500.html">500 Error</a></li> -->
                      <!-- <li><a href="/layouts/plainpage.php">Plain Page</a></li> -->
                      <!-- <li><a href="../sysmgmt/dbtestpage.php">DB Test Page</a></li> -->
                      <!-- <li><a href="login.html">Login Page</a></li> -->
                      <!-- <li><a href="pricing_tables.html">Pricing Tables</a></li> -->
                    <!-- </ul> -->
                  <!-- </li> -->
                  <!-- <li><a><i class="fa fa-sitemap"></i> Multilevel Menu <span class="fa fa-chevron-down"></span></a> -->
                  <!-- <ul class="nav child_menu"> -->
                  <!-- <li><a href="#level1_1">Level One</a> -->
                  <!-- <li><a>Level One<span class="fa fa-chevron-down"></span></a> -->
                  <!-- <ul class="nav child_menu"> -->
                  <!-- <li class="sub_menu"><a href="level2.html">Level Two</a> -->
                  <!-- </li> -->
                  <!-- <li><a href="#level2_1">Level Two</a> -->
                  <!-- </li> -->
                  <!-- <li><a href="#level2_2">Level Two</a> -->
                  <!-- </li> -->
                  <!-- </ul> -->
                  <!-- </li> -->
                  <!-- <li><a href="#level1_2">Level One</a> -->
                  <!-- </li> -->
                  <!-- </ul> -->
                  <!-- </li>                   -->
                  <!-- <li><a href="javascript:void(0)"><i class="fa fa-laptop"></i> Landing Page <span class="label label-success pull-right">Coming Soon</span></a></li> -->
                </ul>
              </div>

            </div>
            <!-- /sidebar menu -->
            
            <!-- sidemenu footer -->
            <div hidden class="sidemenu-footer hidden-small">
              <a href="../external/login.php?a=9" data-toggle="tooltip" data-placement="top" title="Log Keluar">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /sidemenu footer -->

            <!-- /menu footer buttons -->
            <div hidden class="sidebar-footer hidden-small">
              <!-- <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a> -->
              <!-- <a hidden data-toggle="none" data-placement="top">
                &nbsp;<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a> -->
              <!-- <a hidden data-toggle="none" data-placement="top">
                &nbsp;<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a> -->
              <!-- <a hidden data-toggle="none" data-placement="top">
                &nbsp;<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a> -->
              <!-- <a hidden href="../external/login.php?a=9" data-toggle="tooltip" data-placement="top" title="Log Keluar">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a> -->
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>
