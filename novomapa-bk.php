<?php
require_once("send.php");
include_once 'conexao.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_GET['ver'])){
    
} else {

$subcategoria = isset($_GET['subcategoria']) ? $_GET['subcategoria'] : '';
$cordenadas = mysqli_query($con, "update pedido set lat='".(isset($_GET['latitude']) ? $_GET['latitude'] : '')."', log='".(isset($_GET['longitude']) ? $_GET['longitude'] : '')."' where codigo = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."'") or die(mysqli_error($con));

$listaGSub = mysqli_query($con, "select * from categoria_prestador where codsubcategoria='".$subcategoria."'");
while ($rowsub = mysqli_fetch_array($listaGSub)) {
    $queryPedi44 = mysqli_query($con, "INSERT INTO disparo_pedidos (codcadastro, token, codpedido, aceito) VALUES
    ('".$rowsub['codcadastro']."', '', ".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '').", 'n')") or die(mysqli_error($con)) or die(mysqli_error($con));
  
   
    $queryEdits = mysqli_query($con, "SELECT * FROM parceiro WHERE id='" . $rowsub['codcadastro'] . "'");
    $rowEdits = mysqli_fetch_array($queryEdits);

    $listaGCadend = mysqli_query($con, "SELECT * FROM endereco_prestador WHERE cod_cadastro='" . $rowsub['codcadastro'] . "'") or die(mysqli_error($con));
    
    // Verifica se encontrou algum endereço
    if ($rowcadend = mysqli_fetch_array($listaGCadend)) {
        $latitudeCentral = isset($_GET['latitude']) ? $_GET['latitude'] : '';  // Latitude do ponto central
        $longitudeCentral = isset($_GET['longitude']) ? $_GET['longitude'] : ''; // Longitude do ponto central
        $latitudeDestino = $rowcadend['lat'];  // Latitude do ponto de destino
        $longitudeDestino = $rowcadend['log']; // Longitude do ponto de destino
        $radius = 25;                   // Raio em km

        // Insere no banco apenas se houver endereço
        $queryPedisss = mysqli_query($con, "INSERT INTO markers (nome, codcadastro, valor_min, valor_max, lat, lon, type, codpedido, qtdestrelas) VALUES ('" . $rowEdits['NOME'] . "', '" . $rowEdits['id'] . "', '', '', '" . $rowcadend['lat'] . "', '" . $rowcadend['log'] . "', '1', '" . (isset($_GET['codpedido']) ? $_GET['codpedido'] : '') . "', '1')") or die(mysqli_error($con));
    }
}

}

$contacordenadas = mysqli_num_rows(mysqli_query($con, "select * from markers where type = '3' and codpedido = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."'"));

if($contacordenadas > 0) {

} else {
  $queryPedi = mysqli_query($con, "INSERT INTO markers (nome, valor_min, valor_max, lat, lon, type, codpedido) VALUES
  ('Minha Localização', '', '', ".(isset($_GET['latitude']) ? $_GET['latitude'] : '').", ".(isset($_GET['longitude']) ? $_GET['longitude'] : '').", '3', '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."')") or die(mysqli_error($con)) or die(mysqli_error($con));
}

$pedidos = mysqli_query($con, "SELECT p.tempo FROM pedido p, disparo_pedidos d WHERE d.codpedido = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."'	and p.codigo = '".(isset($_GET['codpedido']) ? $_GET['codpedido'] : '')."' AND d.aceito = 'n'") or die(mysqli_error($con));
$rowTemp = mysqli_fetch_array( $pedidos );

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontre Prestadores de Serviço</title>
    <link rel="stylesheet" href="global-font-size.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        success: '#22c55e',
                        background: '#ffffff',
                        muted: '#f3f4f6',
                        'muted-foreground': '#6b7280',
                    }
                }
            }
        }
    </script>

</head>
<body class="min-h-screen bg-gray-50" >
    <div class="flex flex-col min-h-screen">
        <!-- Map Section - Altura aumentada para 150px -->
        <div class="h-[150px] w-full relative bg-muted">
            <div id="map" class="absolute inset-0"></div>
        </div>

        <!-- Providers List Section -->
        <div class="flex-1 " style="background:#f3f3f3 !important;">
            <div class="max-w-3xl mx-auto p-4">
                <div class="sticky top-0 bg-white z-10 pb-2 mb-4 p-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-semibold">Prestadores Disponíveis</h2>
                        <div id="loading-container" class="flex items-center gap-2 text-primary">
                            <div id="loading-spinner" class="w-5 h-5 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-sm" id="status-text">Buscando prestadores...</span>
                        </div>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">Atualizando a cada segundo</p>
                </div>

                <div id="providers-list" class="space-y-4">
                    <!-- Cards serão inseridos aqui via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Contraproposta -->
    <div id="contraproposta-modal" class="modal">
        <div class="modal-content">
            <div class="p-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold" id="modal-title">Contraproposta</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-4" id="modal-content">
                <!-- Conteúdo da contraproposta será inserido aqui -->
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        // Armazena o estado dos prestadores da última atualização
        let previousProviders = {};
        // Armazena os marcadores do mapa
        let mapMarkers = {};
        // Referência para o mapa
        let map;

        function initMap() {
            // Aumentando o zoom inicial para 15 (era 13)
            map = L.map('map', {
                zoomControl: true, // Habilitando controles de zoom
                dragging: true,    // Habilitando arrastar o mapa
                scrollWheelZoom: true, // Habilitando zoom com roda do mouse
                attributionControl: false
            }).setView([-11.8548, -55.5017], 13); // Zoom aumentado para 15

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        }

        function createProviderCard(provider, isNewlyAvailable) {
            const isAvailableNow = provider.availability === "Disponível agora";
            const animationClass = isNewlyAvailable ? 'slide-in-top' : '';
            const offlineClass = isAvailableNow ? '' : 'provider-offline';
            const indicatorClass = isAvailableNow ? 'available' : 'offline';
            const buttonClass = isAvailableNow ? 'bg-primary hover:bg-primary/90' : 'bg-gray-400 hover:bg-gray-500';
            const buttonEnabled = isAvailableNow ? '' : 'disabled';
            
            // Verifica se tem contraproposta
            const hasContraproposta = provider.contraproposta && provider.contraproposta !== '' && provider.contraproposta !== '0';
            
            // Botões baseados na disponibilidade e contraproposta
            let buttonsHtml = '';
            
            if (hasContraproposta) {
                // Se tem contraproposta, mostra os dois botões
                buttonsHtml = `
                    <div class="grid grid-cols-2 gap-2 mt-3">
                        <button ${buttonEnabled} onclick="showContraproposta('${provider.id}', '${provider.name}', '${provider.contraproposta}')" class="py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors text-sm">
                            Ver Contraproposta
                        </button>
                        <button ${buttonEnabled} onclick="location.href='pegar_contato.php?codcadastro=${provider.id}&codpedido=<?php echo $_GET['codpedido']; ?>&nome=${provider.name}';" class="py-2 ${buttonClass} text-white font-medium rounded-lg transition-colors text-sm">
                            Pegar Contato
                        </button>
                    </div>
                `;
            } else {
                // Se não tem contraproposta, mostra apenas o botão de pegar contato
                buttonsHtml = `
                    <button ${buttonEnabled} onclick="location.href='pegar_contato.php?codcadastro=${provider.id}&codpedido=<?php echo $_GET['codpedido']; ?>&nome=${provider.name}';" class="w-full mt-3 py-2 ${buttonClass} text-white font-medium rounded-lg transition-colors">
                        Pegar Contato
                    </button>
                `;
            }

            return `
                <div class="bg-white rounded-lg shadow p-4 ${animationClass} ${offlineClass}" data-id="${provider.id}">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full overflow-hidden">
                                <img src="${provider.avatar}" alt="${provider.name}" class="w-full h-full object-cover">
                            </div>
                            <span class="online-indicator ${indicatorClass}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium"><a href="novomapa2.php">${provider.name}</a> </h3>
                            
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-.181h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-sm">${provider.rating}</span>
                            </div>
                            <p class="text-sm text-muted-foreground">${provider.profession}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-3 text-sm">
                        <span class="flex items-center gap-1 text-muted-foreground">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            ${provider.distance}
                        </span>
                        <span class="rounded-full p-2 border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-secondary/80 flex items-center gap-1 bg-green-100 text-green-800 ${isAvailableNow ? 'text-green-600' : 'text-gray-500'}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" strokeWidth="2"/>
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6l4 2"/>
                            </svg>
                            ${provider.availability}
                        </span>
                    </div>

                    ${buttonsHtml}
                </div>
            `;
        }

        // Função para mostrar o modal de contraproposta
        // Modifique a função showContraproposta para melhorar o tratamento de erros
function showContraproposta(id, name, contraproposta) {
    const modal = document.getElementById('contraproposta-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    
    modalTitle.textContent = `Contraproposta de ${name}`;
    
    // Exibe um indicador de carregamento
    modalContent.innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
    `;
    
    // Exibe o modal imediatamente
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    console.log(`Buscando contraproposta para prestador ID: ${id}, codpedido: <?php echo $_GET['codpedido']; ?>`);
    
    // Busca os detalhes da contraproposta via AJAX
    fetch(`get_contraproposta.php?id=${id}&codpedido=<?php echo $_GET['codpedido']; ?>`)
        .then(response => {
            console.log('Status da resposta:', response.status);
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            
            // Se não houver dados ou se não for bem-sucedido, exibe a contraproposta diretamente
            if (!data || !data.success) {
                // Exibe a contraproposta que já temos
                modalContent.innerHTML = `
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-700">Detalhes da Contraproposta:</h4>
                            <p class="mt-1">${contraproposta || 'Sem detalhes adicionais'}</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Formata o conteúdo da contraproposta
            let content = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-700">Detalhes da Contraproposta:</h4>
                        <p class="mt-1">${data.contraproposta || contraproposta || 'Sem detalhes adicionais'}</p>
                    </div>
            `;
            
            if (data.valor_min) {
                content += `
                    <div>
                        <h4 class="font-medium text-gray-700">Valor Mínimo:</h4>
                        <p class="mt-1 text-green-600 font-semibold">${data.valor_min}</p>
                    </div>
                `;
            }
            
            if (data.valor_max) {
                content += `
                    <div>
                        <h4 class="font-medium text-gray-700">Valor Máximo:</h4>
                        <p class="mt-1 text-green-600 font-semibold">${data.valor_max}</p>
                    </div>
                `;
            }
            
            content += `</div>`;
            modalContent.innerHTML = content;
        })
        .catch(error => {
            console.error('Erro ao buscar contraproposta:', error);
            
            // Em caso de erro, exibe a contraproposta que já temos
            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-700">Detalhes da Contraproposta:</h4>
                        <p class="mt-1">${contraproposta || 'Sem detalhes adicionais'}</p>
                    </div>
                    
                </div>
            `;
        });
}
        
        // Função para fechar o modal
        function closeModal() {
            const modal = document.getElementById('contraproposta-modal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Função para buscar prestadores do servidor
        function fetchProviders() {
            const spinner = document.getElementById('loading-spinner');
            const statusText = document.getElementById('status-text');
            
            spinner.classList.remove('hidden');
            statusText.textContent = 'Buscando prestadores...';
            
            // Adiciona um timestamp para evitar cache
            const timestamp = new Date().getTime();
            
            
            // Faz a requisição AJAX para o arquivo PHP
            fetch(`get_providers.php?codpedido=<?php echo $_GET['codpedido']; ?>&t=${timestamp}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na rede ao buscar prestadores');
                    }
                    return response.json();
                })
                .then(providers => {
                    console.log("Prestadores recebidos:", providers);
                    
                   
                     // Log para debug
                    
                    // Verifica se há dados válidos
                    if (!Array.isArray(providers)) {
                        console.error("Dados recebidos não são um array:", providers);
                        return;
                    }
                    
                    // Verifica e corrige as coordenadas
                    providers.forEach(provider => {
                        // Certifica-se de que location é um array com 2 elementos numéricos
                        if (!provider.location || !Array.isArray(provider.location) || provider.location.length !== 2) {
                            console.warn("Coordenadas inválidas para o prestador:", provider);
                            // Tenta extrair coordenadas dos campos lat e lon, se existirem
                            if (provider.lat !== undefined && provider.lon !== undefined) {
                                provider.location = [Number.parseFloat(provider.lat), Number.parseFloat(provider.lon)];
                            }
                        }
                    });
                    
                    updateProvidersDisplay(providers);
                    spinner.classList.add('hidden');
                    statusText.textContent = 'Atualizado';
                })
                .catch(error => {
                    console.error('Erro ao buscar prestadores:', error);
                    spinner.classList.add('hidden');
                    statusText.textContent = 'Erro ao atualizar';
                });
        }

        // Função para atualizar a exibição dos prestadores
        function updateProvidersDisplay(providers) {
            const providersList = document.getElementById('providers-list');
            
            // Separa os prestadores em disponíveis e aguardando
            const availableProviders = providers.filter(p => p.availability === "Disponível agora");
            const waitingProviders = providers.filter(p => p.availability !== "Disponível agora");
            
            // Identifica prestadores que acabaram de ficar disponíveis
            const newlyAvailableProviders = availableProviders.filter(p => {
                // Verifica se o prestador existia antes e não estava disponível
                return previousProviders[p.id] && 
                       previousProviders[p.id].availability !== "Disponível agora";
            });
            
            // Identifica prestadores que são completamente novos
            const brandNewProviders = availableProviders.filter(p => {
                return !previousProviders[p.id];
            });
            
            // Combina os dois grupos para notificação
            const allNewAvailableProviders = [...newlyAvailableProviders, ...brandNewProviders];
            
            // Se houver novos prestadores disponíveis, notifica o usuário
            if (allNewAvailableProviders.length > 0) {
                const statusText = document.getElementById('status-text');
                statusText.textContent = `${allNewAvailableProviders.length} novo(s) prestador(es) disponível(is)!`;
                setTimeout(() => {
                    statusText.textContent = 'Atualizado';
                }, 5000);
            }
            
            // Atualiza o registro de prestadores anteriores
            const currentProviders = {};
            providers.forEach(p => {
                currentProviders[p.id] = p;
            });
            
            // Limpa a lista atual
            providersList.innerHTML = '';
            
            // Adiciona os prestadores disponíveis primeiro
            availableProviders.forEach(provider => {
                // Verifica se o prestador acabou de ficar disponível
                const isNewlyAvailable = newlyAvailableProviders.some(p => p.id === provider.id) || 
                                         brandNewProviders.some(p => p.id === provider.id);
                
                providersList.innerHTML += createProviderCard(provider, isNewlyAvailable);
            });
            
            // Depois adiciona os que estão aguardando
            waitingProviders.forEach(provider => {
                providersList.innerHTML += createProviderCard(provider, false);
            });
            
            // Atualiza os marcadores no mapa
            updateMapMarkers(providers);
            
            // Atualiza o registro de prestadores anteriores para a próxima verificação
            previousProviders = currentProviders;
        }
        
        // Função para atualizar os marcadores no mapa
        function updateMapMarkers(providers) {
            console.log("Atualizando marcadores para", providers.length, "prestadores");
            
            // Remove marcadores antigos que não estão mais na lista
            Object.keys(mapMarkers).forEach(id => {
                const providerExists = providers.some(p => p.id.toString() === id);
                if (!providerExists) {
                    map.removeLayer(mapMarkers[id]);
                    delete mapMarkers[id];
                }
            });
            
            // Adiciona ou atualiza marcadores
            const validProviders = [];
            
            providers.forEach(provider => {
                const id = provider.id.toString();
                
                // Verifica se as coordenadas são válidas
                if (!provider.location || provider.location.length !== 2 || 
                    isNaN(provider.location[0]) || isNaN(provider.location[1])) {
                    console.warn("Coordenadas inválidas para o prestador:", provider);
                    return;
                }
                
                validProviders.push(provider);
                
                // Cria um ícone personalizado baseado no status
                const isAvailable = provider.availability === "Disponível agora";
                const iconColor = isAvailable ? '#22c55e' : '#9ca3af';
                
                // Usando o ícone padrão do Leaflet com cores personalizadas
                const icon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + (isAvailable ? 'green' : 'grey') + '.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });
                
                // Se o marcador já existe, atualiza sua posição e ícone
                if (mapMarkers[id]) {
                    mapMarkers[id].setLatLng(provider.location);
                    mapMarkers[id].setIcon(icon);
                } else {
                    // Caso contrário, cria um novo marcador
                    mapMarkers[id] = L.marker(provider.location, { icon: icon })
                        .bindPopup(`<b>${provider.name}</b><br>${provider.profession}<br>${provider.availability}`)
                        .addTo(map);
                }
            });
            
            console.log("Marcadores válidos:", validProviders.length);
            
            // Ajusta a visualização do mapa para incluir todos os marcadores
            if (validProviders.length > 0) {
                const bounds = [];
                validProviders.forEach(provider => {
                    bounds.push(provider.location);
                });
                
                console.log("Ajustando mapa para mostrar", bounds.length, "marcadores");
                
                // Só ajusta se houver pelo menos um marcador válido
                if (bounds.length > 0) {
                    try {
                        map.fitBounds(bounds, { padding: [20, 20], maxZoom: 15 });
                    } catch (e) {
                        console.error("Erro ao ajustar o mapa:", e);
                        // Tenta centralizar no primeiro marcador como fallback
                        if (bounds.length > 0) {
                            map.setView(bounds[0], 13);
                        }
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            
            // Busca prestadores imediatamente
            fetchProviders();
            
            // Configura o intervalo para buscar prestadores a cada segundo
            setInterval(fetchProviders, 5000);
            
            // Fecha o modal quando clicar fora dele
            document.getElementById('contraproposta-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }
)
</script>
</body>
</html>