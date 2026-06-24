<head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <meta name="description" content="" />
    <link rel="stylesheet" href="global-font-size.css">
    <script>document.documentElement.style.setProperty('font-size','16px','important');</script>

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
    <link rel="stylesheet" href="<?php echo $urlserver; ?>adm/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?php echo $urlserver; ?>adm/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?php echo $urlserver; ?>adm/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?php echo $urlserver; ?>adm/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?php echo $urlserver; ?>adm/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="<?php echo $urlserver; ?>adm/assets/vendor/css/pages/page-auth.css" />
    <!-- Helpers -->
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
</head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>


<div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar" style=" width: 100% !important; height: 74px;  "
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="">
              <img src="logo1.png" alt=""
                            data-pagespeed-url-hash="2577310689"
                             style="
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

              <ul class="navbar-nav flex-row align-items-center ms-auto" >
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
                      <a class="dropdown-item" href="consumidor">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">Buscar Prestador</span>
                      </a>
                    </li>

                    <li>
                      <a class="dropdown-item" href="<?php echo $urlserver; ?>edicao">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">Minha Conta</span>
                      </a>
                    </li>


                    <li style="position: relative; float:left">
                
                      <a  style="position: relative;" class="dropdown-item" href="meus-orcamentos">
                     
                      <i class="bx bx-bell me-1"></i>
                        <span class="align-middle">Meus Orçamentos</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?php echo $urlserver; ?>minhasmoedas">
                       
                        <i style="font-size: 145%;" class="las la-coins"></i> Minhas Moedas</a
                      >
                    </li>
                    <li >
                      <a class="dropdown-item"href="listar_avaliacoes.php"
                        ><i style="font-size: 145%;" class="las la-smile"></i> Minhas Avaliaçoes</a
                      >
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="sair.php">
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



          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">

            <div class="col-md-12 col-lg-4 mb-3">
              <div class="card mb-2">
                <div class="card-body" style="    padding: 18px 13px !important;">
                <a href="javascript:window.history.back()"
                class="fs-4 link-dark"><i style="font-size: 160%; font-weight: bold; color: #00adef;" class="las la-chevron-circle-left"></i></a> 
                <h5 class="card-title" style="font-size:105% !important; color:#000 !important; text-align: center;" ><i style="font-size:135% !important; color:#efb810;" class="las la-coins"></i> COMPRAR MOEDAS</h5>
                <h6 class="card-title" style="text-align: center; padding-top: 30px;">Selecione a quantia que deseja comprar</h6>
                <form action="gerar_pix2.php" method="GET">
                <div class="row">
                  <div class="mb-4">
                    <input type="hidden" name="codpedido" value="<?php echo $_GET['codpedido']; ?>">
                    <select class="form-select" aria-label="Default select example" name="moedas" id="moedaSelect">
                      <option value="8" data-preco="1,00" data-preco-amount="1.00" selected>8 Moedas</option>
                      <option value="15" data-preco="1,90" data-preco-amount="1.90">15 Moedas</option>
                      <option value="30" data-preco="3,00" data-preco-amount="3.00">30 Moedas</option>
                    </select>
                    <input type="hidden" name="valor" id="valor" value="1.00">
                  </div>
                </div>
                <h5 class="card-title" id="precoMoeda" style="font-size:110% !important; color:#000 !important; text-align: center; font-weight: bold;">R$ 1,00</h5>

                <script>
                  const selectElement = document.getElementById('moedaSelect');
                  const precoElement = document.getElementById('precoMoeda');
                  const valorInput = document.getElementById('valor');

                  selectElement.addEventListener('change', function() {
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const preco = selectedOption.getAttribute('data-preco-amount');
                    
                    // Atualiza o texto do preço
                    precoElement.textContent = `R$ ${preco}`;

                    // Atualiza o valor do input
                    valorInput.value = preco;
                  });
                </script>


              <div style="float: left; width: 100%; margin-bottom: 20px; margin-top: 20px;">
                <div style="width: 200px; margin: 0 auto;">
                    <button type="submit" style="background: none; border:none; margin: 0; padding: 0;"><img src="10.png" width="200"> </button>
                    </div>
                </div>
               </form> 

            </div>
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



<script>
// Type 1
document.getElementById('execCopy').addEventListener('click', execCopy);
function execCopy() {
  document.querySelector("#input").select();
  document.execCommand("copy");
  $("#execCopy").hide(100);
  $("#clipboardCopy").show(300);
  
}

</script>

<script src="<?php echo $urlserver; ?>adm/assets/vendor/js/bootstrap.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>



<script src="<?php echo $urlserver; ?>adm/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

<script src="<?php echo $urlserver; ?>adm/assets/vendor/js/menu.js"></script>
<!-- endbuild -->
