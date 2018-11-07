<?php session_start(); ?>
<?php require "../layouts/lay_adminmaintop.php"; ?>
        <div class="right_col" role="main">
          <!-- top tiles -->
          <div class="row tile_count">
            <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-file"></i> Jumlah Dokumen</span>
              <div class="count"><?php fnCountDocInRep(); ?></div>
              <span class="count_bottom"><!-- <i class="green">4% </i> Semua Kat. --></span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-bolt"></i> Berkuat Kuasa</span>
              <div class="count green"><?php fnCountActiveDocInRep(); ?></div>
              <span class="count_bottom"><!-- <i class="green"><i class="fa fa-sort-asc"></i>3% </i> From last Week --></span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-ban"></i> Dimansuhkan</span>
              <div class="count red"><?php fnCountInactiveDocInRep(); ?></div>
              <span class="count_bottom"><!-- <i class="green"><i class="fa fa-sort-asc"></i>34% </i> From last Week --></span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-share"></i> Diserahkan</span>
              <div class="count purple"><?php fnCountGivenDocInRep(); ?></div>
              <span class="count_bottom"><!-- <i class="red"><i class="fa fa-sort-desc"></i>12% </i> From last Week --></span>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
              <span class="count_top"><i class="fa fa-pencil"></i> Pindaan</span>
              <div class="count blue"><?php fnCountAmmendedDocInRep(); ?></div>
              <span class="count_bottom"><!-- <i class="red"><i class="fa fa-sort-desc"></i>12% </i> From last Week --></span>
            </div>
            <!-- <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count"> -->
              <!-- <span class="count_top"><i class="fa fa-search"></i> Bil. Carian</span> -->
              <!-- <div class="count">2,315</div> -->
              <!-- <sp3an class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> From last Week</span> -->
            <!-- </div> -->
            <!-- <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count"> -->
              <!-- <span class="count_top"><i class="fa fa-question-circle"></i> Bil. Pertanyaan</span> -->
              <!-- <div class="count">7,325</div> -->
              <!-- <s3pan class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>34% </i> From last Week</span> -->
            <!-- </div> -->
          </div>
          <!-- /top tiles -->

          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="dashboard_graph">

                <div class="row x_title">
                  <div class="col-md-6">
                    <h3>Status Repositori <small>Ringkasan Simpanan</small></h3>
                  </div>
                  <div class="col-md-6" hidden>
                    <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                      <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                      <span>December 30, 2014 - January 28, 2015</span> <b class="caret"></b>
                    </div>
                  </div>
                </div>

                <div class="col-md-9 col-sm-9 col-xs-12" hidden>
                  <div id="placeholder33" style="height: 260px; display: none" class="demo-placeholder"></div>
                  <div style="width: 100%;">
                    <div id="canvas_dahs" class="demo-placeholder" style="width: 100%; height:270px;"></div>
                  </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 bg-white">
                  <div class="x_title">
                    <h2>Kategori Dokumen</h2>
                    <div class="clearfix"></div>
                  </div>

                  <div class="col-md-12 col-sm-12 col-xs-6">
                    <?php  
                    fnDashCatDisplay();
                    ?>
                  </div>

                </div>

                <div class="clearfix"></div>
              </div>
            </div>

          </div>
          <br />

          <div class="row">






          </div>


          <div class="row">


            <div class="col-md-8 col-sm-8 col-xs-12">



              <div class="row">


              </div>
              <div class="row">


                <!-- Start to do list -->
                <!-- End to do list -->
                
                <!-- start of weather widget -->
                <!-- end of weather widget -->
              </div>
            </div>
          </div>
        </div>
        <p>&&nbsp;</p>
        <p>&&nbsp;</p>
<?php require "../layouts/lay_adminmainbottom.php"; ?>