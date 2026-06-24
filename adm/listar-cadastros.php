<?php 
 require_once("send.php"); 
 header("Content-Type: text/html; charset=utf-8",true);

if(isset($_GET['acao']) && $_GET['acao']=="deletar") {
  $codigo = $_GET['codigo'];
  $delete = mysqli_query($con, "DELETE FROM parceiro WHERE id='$codigo'") or die(mysqli_error($con));
  $delete2 = mysqli_query($con, "DELETE FROM categoria_prestador WHERE codcadastro='$codigo'")or die(mysqli_error($con));
  echo "<script>alert('Registro deletado com sucesso!'); window.location.href='listar-cadastros.php';</script>";
}

// Item 12: garante as colunas usadas pelos selos na tabela de verificações
$colunasVerif = [
    'parceiro_fundador' => "TINYINT(1) DEFAULT 0",
    'selo_verificado'   => "TINYINT(1) DEFAULT 0",
    'selo_seguro'       => "TINYINT(1) DEFAULT 0",
];
$tabVerif = mysqli_query($con, "SHOW TABLES LIKE 'verificacoes_usuario'");
$temTabVerif = ($tabVerif && mysqli_num_rows($tabVerif) > 0);
if ($temTabVerif) {
    foreach ($colunasVerif as $col => $def) {
        $chk = mysqli_query($con, "SHOW COLUMNS FROM verificacoes_usuario LIKE '$col'");
        if ($chk && mysqli_num_rows($chk) == 0) {
            mysqli_query($con, "ALTER TABLE verificacoes_usuario ADD COLUMN $col $def");
        }
    }
}

// Item 12: conceder/remover selo de Parceiro Fundador direto da lista de cadastros
if (isset($_GET['fundador_toggle'])) {
    $pid = (int)$_GET['fundador_toggle'];
    $val = (isset($_GET['val']) && $_GET['val'] == '1') ? 1 : 0;
    if ($temTabVerif && $pid > 0) {
        $qf = mysqli_query($con, "SELECT id FROM verificacoes_usuario WHERE id_usuario='$pid' AND tipo_usuario='prestador' ORDER BY id DESC LIMIT 1");
        if ($qf && $rf = mysqli_fetch_assoc($qf)) {
            mysqli_query($con, "UPDATE verificacoes_usuario SET parceiro_fundador='$val' WHERE id='".$rf['id']."'");
        } else {
            mysqli_query($con, "INSERT INTO verificacoes_usuario (id_usuario, tipo_usuario, status, parceiro_fundador) VALUES ('$pid','prestador','pendente','$val')");
        }
    }
    echo "<script>window.location.href='listar-cadastros';</script>";
    exit;
}

// Item 12: carrega selos (verificado/fundador) de todos os prestadores de uma vez
$selosPorPrestador = [];
if ($temTabVerif) {
    $qSelos = mysqli_query($con, "SELECT id_usuario, MAX(selo_verificado) as verificado, MAX(parceiro_fundador) as fundador
                                  FROM verificacoes_usuario WHERE tipo_usuario='prestador' GROUP BY id_usuario");
    while ($qSelos && $rs = mysqli_fetch_assoc($qSelos)) {
        $selosPorPrestador[(string)$rs['id_usuario']] = $rs;
    }
}

// Filtros
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'recentes';
$status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Construir query
$where = "WHERE 1=1";
if($status == 'com_categoria') {
    $where .= " AND id IN (SELECT DISTINCT codcadastro FROM categoria_prestador)";
} elseif($status == 'sem_categoria') {
    $where .= " AND id NOT IN (SELECT DISTINCT codcadastro FROM categoria_prestador)";
}

if(!empty($dataInicio)) {
    $where .= " AND DATE(dataCad) >= '".mysqli_real_escape_string($con, $dataInicio)."'";
}
if(!empty($dataFim)) {
    $where .= " AND DATE(dataCad) <= '".mysqli_real_escape_string($con, $dataFim)."'";
}

// Ordenação
switch($ordenar) {
    case 'recentes':
        $orderBy = "ORDER BY dataCad DESC";
        break;
    case 'antigos':
        $orderBy = "ORDER BY dataCad ASC";
        break;
    case 'nome_asc':
        $orderBy = "ORDER BY NOME ASC";
        break;
    case 'nome_desc':
        $orderBy = "ORDER BY NOME DESC";
        break;
    case 'ultimo_acesso':
        $orderBy = "ORDER BY ultimoAcesso DESC";
        break;
    default:
        $orderBy = "ORDER BY dataCad DESC";
}

$query = "SELECT * FROM parceiro $where $orderBy";
$lista = mysqli_query($con, $query);
$totalRegistros = mysqli_num_rows($lista);

function formatarTelefoneAdmin($telefone) {
    $digits = preg_replace('/\D/', '', (string)$telefone);
    if (strlen($digits) === 11) {
        return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
    }
    if (strlen($digits) === 10) {
        return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
    }
    return $telefone;
}
?>

<style>
.filters-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}
.filters-card .form-label {
    color: #fff;
    font-weight: 600;
    font-size: 0.85rem;
    margin-bottom: 5px;
}
.filters-card .form-control,
.filters-card .form-select {
    border: none;
    border-radius: 10px;
    padding: 10px 15px;
    background: rgba(255,255,255,0.95);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.filters-card .form-control:focus,
.filters-card .form-select:focus {
    box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
}
.filters-card .btn-filter {
    background: #fff;
    color: #667eea;
    border: none;
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.filters-card .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}
.filters-card .btn-clear {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 2px solid rgba(255,255,255,0.5);
    border-radius: 10px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.filters-card .btn-clear:hover {
    background: rgba(255,255,255,0.3);
}
.stats-badge {
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    display: inline-block;
}
.table-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.table-card .card-header {
    background: #f8f9fa;
    border-bottom: none;
    padding: 15px 20px;
}
.table thead th {
    background: #f1f3f4;
    border: none;
    padding: 15px;
    font-weight: 600;
    color: #5f6368;
    cursor: pointer;
    transition: background 0.2s;
}
.table thead th:hover {
    background: #e8eaed;
}
.table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}
.table tbody tr:hover {
    background: #f8f9fa;
}
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
.status-ativo {
    background: #d4edda;
    color: #155724;
}
.status-inativo {
    background: #fff3cd;
    color: #856404;
}
.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    border: none;
    margin: 0 2px;
}
.action-btn-edit {
    background: #e3f2fd;
    color: #1976d2;
}
.action-btn-edit:hover {
    background: #1976d2;
    color: #fff;
}
.action-btn-delete {
    background: #ffebee;
    color: #d32f2f;
}
.action-btn-delete:hover {
    background: #d32f2f;
    color: #fff;
}
.search-input {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 12px 20px;
    transition: all 0.3s;
}
.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.cpf-cell {
    font-size: 10px;
    color: #6b7280;
    white-space: nowrap;
    padding: 8px 2px;
}
.cpf-col {
    width: 85px;
    max-width: 85px;
    padding: 8px 2px;
}
.table tbody td {
    padding: 10px 8px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
    font-size: 13px;
}
.table thead th {
    background: #f1f3f4;
    border: none;
    padding: 10px 8px;
    font-weight: 600;
    color: #5f6368;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 12px;
}
.btn-categorias {
    background: #e3f2fd;
    color: #1976d2;
    border: none;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-categorias:hover {
    background: #1976d2;
    color: #fff;
}
.categorias-list {
    display: none;
    margin-top: 8px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 11px;
}
.categorias-list.show {
    display: block;
}
.categoria-badge {
    display: inline-block;
    background: #667eea;
    color: #fff;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    margin: 2px;
    font-weight: 500;
}
.nome-cell {
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.telefone-cell {
    font-size: 13px;
}
.whats-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #128C7E;
    font-weight: 700;
    text-decoration: none;
    font-size: 13px;
}
.whats-link:hover { 
    text-decoration: underline;
    color: #075E54;
}
.whats-link .bx { font-size: 18px; }
</style>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("myInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("myTable");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    var found = false;
    var tds = tr[i].getElementsByTagName("td");
    for (var j = 0; j < tds.length; j++) {
      td = tds[j];
      if (td) {
        txtValue = td.textContent || td.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          found = true;
          break;
        }
      }
    }
    if (found || tr[i].getElementsByTagName("th").length > 0) {
      tr[i].style.display = "";
    } else {
      tr[i].style.display = "none";
    }
  }
}

function limparFiltros() {
    window.location.href = 'listar-cadastros';
}

function toggleCategorias(userId) {
    console.log('toggleCategorias chamado para userId:', userId);
    
    const catDiv = document.getElementById('categorias-' + userId);
    const btn = document.getElementById('btn-cat-' + userId);
    
    if (!catDiv || !btn) {
        console.error('Elementos não encontrados:', {catDiv, btn});
        return;
    }
    
    if (catDiv.classList.contains('show')) {
        catDiv.classList.remove('show');
        btn.innerHTML = '<i class="bx bx-show"></i> Ver categorias';
        return;
    }
    
    // Buscar categorias via AJAX
    if (catDiv.dataset.loaded !== 'true') {
        catDiv.innerHTML = '<em style="color: #999;">Carregando...</em>';
        catDiv.classList.add('show');
        
        const url = 'get-categorias-usuario.php?id=' + userId;
        console.log('Fazendo requisição para:', url);
        
        fetch(url)
            .then(function(response) {
                console.log('Resposta recebida. Status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.text();
            })
            .then(function(text) {
                console.log('Texto recebido:', text.substring(0, 200));
                var data = JSON.parse(text);
                console.log('JSON parseado:', data);
                
                if (data.debug) {
                    console.log('Debug do servidor:', data.debug);
                }
                
                if (data.success && data.categorias && data.categorias.length > 0) {
                    var html = '<strong style="color: #333;">Categorias:</strong><br>';
                    data.categorias.forEach(function(cat) {
                        html += '<span class="categoria-badge">' + cat.categoria + ' → ' + cat.subcategoria + '</span>';
                    });
                    catDiv.innerHTML = html;
                    catDiv.dataset.loaded = 'true';
                    btn.innerHTML = '<i class="bx bx-hide"></i> Ocultar';
                } else {
                    catDiv.innerHTML = '<em style="color: #999;">Nenhuma categoria cadastrada</em>';
                    catDiv.dataset.loaded = 'true';
                    btn.innerHTML = '<i class="bx bx-hide"></i> Ocultar';
                }
            })
            .catch(function(err) {
                console.error('ERRO:', err);
                catDiv.innerHTML = '<em style="color: #d32f2f;">Erro: ' + err.message + '</em>';
                catDiv.dataset.loaded = 'false';
                btn.innerHTML = '<i class="bx bx-show"></i> Tentar novamente';
            });
    } else {
        catDiv.classList.add('show');
        btn.innerHTML = '<i class="bx bx-hide"></i> Ocultar';
    }
}
</script>
 
<!-- Layout container -->
<div class="layout-page">
    <?php require_once("nav-topo.php"); ?>

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Parceiro /</span> Cadastros
                </h4>
                <span class="stats-badge" style="background: #667eea; color: #fff;">
                    <i class="bx bx-user me-1"></i> <?php echo $totalRegistros; ?> cadastros
                </span>
            </div>

            <!-- Filtros -->
            <div class="filters-card">
                <form method="GET" action="listar-cadastros">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Ordenar por</label>
                            <select name="ordenar" class="form-select">
                                <option value="recentes" <?php echo $ordenar == 'recentes' ? 'selected' : ''; ?>>Mais recentes</option>
                                <option value="antigos" <?php echo $ordenar == 'antigos' ? 'selected' : ''; ?>>Mais antigos</option>
                                <option value="nome_asc" <?php echo $ordenar == 'nome_asc' ? 'selected' : ''; ?>>Nome A-Z</option>
                                <option value="nome_desc" <?php echo $ordenar == 'nome_desc' ? 'selected' : ''; ?>>Nome Z-A</option>
                                <option value="ultimo_acesso" <?php echo $ordenar == 'ultimo_acesso' ? 'selected' : ''; ?>>Último acesso</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="todos" <?php echo $status == 'todos' ? 'selected' : ''; ?>>Todos</option>
                                <option value="com_categoria" <?php echo $status == 'com_categoria' ? 'selected' : ''; ?>>Com categoria</option>
                                <option value="sem_categoria" <?php echo $status == 'sem_categoria' ? 'selected' : ''; ?>>Sem categoria</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?php echo $dataInicio; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?php echo $dataFim; ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-filter w-100">
                                <i class="bx bx-filter-alt me-1"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" onclick="limparFiltros()" class="btn btn-clear w-100">
                                <i class="bx bx-x me-1"></i> Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela -->
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <input type="text" id="myInput" class="form-control search-input" onkeyup="myFunction()" placeholder=" Buscar por nome, CPF/CNPJ..." style="max-width: 400px;">
                </div>
                <div class="table-responsive">
                    <table class="table" id="myTable">
                        <thead>
                            <tr>
                                <th class="cpf-col">CPF / CNPJ</th>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th>Verificado</th>
                                <th>Fundador</th>
                                <th>Categorias</th>
                                <th>Data Cadastro</th>
                                <th>Último Acesso</th>
                                <th style="width: 80px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        while($row = mysqli_fetch_array($lista)) {
                        ?>
                      <tr>
                      <td class="cpf-cell"><?php echo $row['CNPJ_CPF']; ?></td>
                        <td class="nome-cell"><i class="fab fa-angular fa-lg text-danger me-2"></i> <strong><?php echo $row['NOME'];?></strong></td>
                        <td class="telefone-cell">
                          <?php
                            $telefoneRaw = !empty($row['CELULAR']) ? $row['CELULAR'] : ($row['TELEFONE'] ?? '');
                            $telefoneDigits = preg_replace('/\D/', '', (string)$telefoneRaw);
                            if (!empty($telefoneDigits)) {
                                $telefoneFormatado = formatarTelefoneAdmin($telefoneRaw);
                                $whatsUrl = 'https://wa.me/55' . $telefoneDigits;
                                echo '<a class="whats-link" href="' . $whatsUrl . '" target="_blank" rel="noopener">'
                                    . '<i class="bx bxl-whatsapp"></i>'
                                    . $telefoneFormatado
                                    . '</a>';
                            } else {
                                echo '-';
                            }
                          ?>
                        </td>

                        <?php 
                        // Verificar se tem categoria vinculada
                        $temCategoria = mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as total FROM categoria_prestador WHERE codcadastro = '".$row['id']."'"));
                        if($temCategoria['total'] > 0) { ?>
                        <td><span class="badge bg-label-success me-1" style="font-size: 10px;">Ativado</span></td>
                          <?php } else { ?>
                        <td><span class="badge bg-label-warning me-1" style="font-size: 10px;">Sem categoria</span></td>
                         <?php } ?> 

                         <?php
                         // Item 12: selos do prestador
                         $selosRow = isset($selosPorPrestador[(string)$row['id']]) ? $selosPorPrestador[(string)$row['id']] : null;
                         $ehVerificado = $selosRow && (int)$selosRow['verificado'] === 1;
                         $ehFundador   = $selosRow && (int)$selosRow['fundador'] === 1;
                         ?>
                         <td>
                            <?php if ($ehVerificado) { ?>
                                <span class="badge bg-primary" style="font-size:10px;">🛡️ Sim</span>
                            <?php } else { ?>
                                <span class="badge bg-label-secondary" style="font-size:10px;">Não</span>
                            <?php } ?>
                         </td>
                         <td>
                            <?php if ($ehFundador) { ?>
                                <a href="listar-cadastros?fundador_toggle=<?php echo $row['id']; ?>&val=0" title="Remover selo de fundador" onclick="return confirm('Remover o selo de Parceiro Fundador deste prestador?')">
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">👑 Fundador</span>
                                </a>
                            <?php } else { ?>
                                <a href="listar-cadastros?fundador_toggle=<?php echo $row['id']; ?>&val=1" title="Conceder selo de fundador" onclick="return confirm('Conceder o selo de Parceiro Fundador a este prestador?')">
                                    <span class="badge bg-label-warning" style="font-size:10px;">+ Conceder</span>
                                </a>
                            <?php } ?>
                         </td>

                         <td>
                            <button type="button" class="btn-categorias" id="btn-cat-<?php echo $row['id']; ?>" onclick="toggleCategorias(<?php echo $row['id']; ?>)">
                                <i class="bx bx-show"></i> Ver categorias
                            </button>
                            <div id="categorias-<?php echo $row['id']; ?>" class="categorias-list" data-loaded="false">
                                Carregando...
                            </div>
                         </td>
                         
                         <td style="font-size: 11px;"><?php echo Data($row['dataCad']); ?></td>
                         <td style="font-size: 11px;"><?php echo Data($row['ultimoAcesso']); ?></td>
                        <td>
                          <div class="dropdown dropup">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow dropup" data-bs-toggle="dropdown">
                              <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="editar-cadastro.php?id=<?php echo $row['id'] ?>"
                                ><i class="bx bx-edit-alt me-1"></i> Editar</a
                              >
                             <a class="dropdown-item" href="listar-cadastros?acao=deletar&codigo=<?php echo $row['id'] ?>"
                                ><i class="bx bx-trash me-1"></i> Deletar</a
                              >
                            </div>
                          </div>
                        </td>
                      </tr>
                      <?php } ?>
                      </tbody>
                  </table>
                </div>
              </div>
             

              </div>
              </div>
              </div>
              </div>   
              </div>  
              <div class="buy-now">
      <a
        href="https://gessomt.app.br/pediuservico/adm2/cadastrar-novo"
        class="btn btn-danger btn-buy-now"
        >Cadastrar novo</a
      >
    </div>