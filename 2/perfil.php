<?php 
ob_start();
require("send.php");

?>
    <?php
      $queryEdit = mysqli_query($con, "select * from clientes where id='".$_COOKIE['codcliente']."'");
      $rowEdit = mysqli_fetch_array($queryEdit)
    ?>
<!DOCTYPE html>
<html
  lang="pt-br"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Login Mão Amiga App</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>

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
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="https://gessomt.app.br/pediuservico/adm/assets/vendor/css/pages/page-auth.css" />
    <!-- Helpers -->

  </head>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

  <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar" style="margin: 0 !important; width: 100% !important; height: 74px;"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="">
              <img src="logo1.png" alt=""
                            data-pagespeed-url-hash="2577310689"
                            onload="pagespeed.CriticalImages.checkImageForCriticality(this);" style="
        max-height: 68px;
        padding:3px 1px;

        ">
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Place this tag where you want the button to render. -->
                <li class="nav-item lh-1 me-3">
 
                </li>

                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <i style="font-size: 33px !important;
    color: #00adef;" class="bx bx-menu bx-sm"></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="https://gessomt.app.br/pediuservico/perfil">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">Minha Conta</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                      <i class="bx bx-bell me-1"></i>
                        <span class="align-middle">Meus Orçamentos</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="https://gessomt.app.br/pediuservico/consumidor">
                        <span class="d-flex align-items-center align-middle">
                         
                          <span class="flex-grow-1 align-middle">Novo Pedido</span>
                          
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="sair2.php">
                        <i class="bx bx-power-off me-2"></i>
                        <span class="align-middle">Sair</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>

          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
              <div class="row">
                <div class="col-md-12">
                  <ul class="nav nav-pills flex-column flex-md-row mb-3">
                    <li class="nav-item">
                      <a class="nav-link active" style="background-color: #00adef !important;" href="javascript:void(0);"><i class="bx bx-user me-1"></i> Minha Conta</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="pages-account-settings-notifications.html"
                        ><i class="bx bx-bell me-1"></i> Meus Orçamentos</a
                      >
                    </li>
                  </ul>
                  <div class="card mb-4">
                    <hr class="my-0" />
                    <div class="card-body">
                      <form id="formAccountSettings" method="POST" action="editar-cliente.php">
                        <div class="row">
                       
                <div class="mb-3 cnpjj" style=" position: relative !important;">
                  <label for="username" class="form-label">CPF / CNPJ</label>
                  <input
                  style="position: relative !important;"
                    type="text"
                    class="form-control"
                    value="<?php echo $rowEdit['CNPJ_CPF']; ?>"
                      name="cpf_cnpj" readonly="true"
                  />
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Nome</label>
                  <input
                      type="text"
                      class="form-control"
                      name="nome"
                      value="<?php echo $rowEdit['NOME']; ?>"
                      required
                    />
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Celular</label>
                  <input
                      type="text"
                      id="telefone"
                      class="form-control"
                      name="telefone"
                      value="<?php echo $rowEdit['CELULAR']; ?>"
                      required
                    />
                </div>
             

                <div class="mb-3"  style="position: relative !important;">
                <label for="email" class="form-label">Senha</label>
  
            <input id="pass"
                   type="password"
                   name="pass"
                   minlength="3"
                   class="form-control"
                   value="<?php echo $rowEdit['senha']; ?>"
                   required"">
                </div>


                        </div>

                        <div class="mt-2">
                        <button type="submit" class="btn btn-primary d-grid w-100" style="
    color: #fff;
    font-size: 110%;
    padding-top: 13px;
    padding-bottom: 13px;
   background-color: #00afef;
">ATUALIZAR PERFIL</button>
                   
                        </div>
                      </form>
                    </div>
                    <!-- /Account -->
                  </div>
         
                </div>
              </div>
            </div>
            <!-- / Content -->


            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>


        <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="https://gessomt.app.br/pediuservico/adm/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://gessomt.app.br/pediuservico/adm/assets/vendor/libs/popper/popper.js"></script>
    <script src="https://gessomt.app.br/pediuservico/adm/assets/vendor/js/bootstrap.js"></script>
    <script src="https://gessomt.app.br/pediuservico/adm/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="https://gessomt.app.br/pediuservico/adm/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="https://gessomt.app.br/pediuservico/adm/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="https://gessomt.app.br/pediuservico/adm/assets/js/pages-account-settings-account.js"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>