<?php session_start();
 require_once("send.php"); 
 header("Content-Type: text/html; charset=utf-8",true);
 if(isset($_SESSION['nomeUsuario'])) { ?><?php } else { ?> <script language="javascript1.2">window.location.href='<?php echo $urlserver."login.php"; ?>';</script><?php } ?>

<html
  lang="pt-br"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="https://gessomt.app.br/pediuservico/adm2/assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Sistema Mão Amiga App</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="fav.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm2/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm2/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm2/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm2/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm2/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm2/assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/js/config.js"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="index.html" class="app-brand-link">
              
              <span class="app-brand-text demo menu-text fw-bolder ms-2">
              <img src="https://gessomt.app.br/pediuservico/maoamiga/logo1.png"  height="60" >
              </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <?php
            require_once("menu.php");
          ?>
        </aside>
        <!-- / Menu -->

        <?php
            $url = (isset($_GET['url'])) ? $_GET['url']:'capa.php';
            $url = array_filter(explode('/',$url));
            
            $file = $url[0].'.php';
            
            if(is_file($file)){
                include $file;
            }else{
                include 'capa.php';
            }            
        ?>
           

               

          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->


    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/libs/popper/popper.js"></script>
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/js/bootstrap.js"></script>
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="https://gessomt.app.br/pediuservico/adm2/assets/js/dashboards-analytics.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>
</html>
