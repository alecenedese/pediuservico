# Roteiro de Testes - Pediu Serviço
**Base URL:** https://gessomt.app.br/pediuservico/

---

## PRÉ-REQUISITOS
- [ ] Executar `criar_coluna_custo_moedas.sql` no banco de dados
- [ ] Executar `criar_tabela_moedas_extrato.sql` no banco de dados
- [ ] Incrementar CACHE_NAME no `sw.js` (ex: `pediuservico-v9`) para forçar atualização do Service Worker

---

## TESTE 1: Fluxo Completo - Cliente cria pedido e aceita proposta (AUTO-ACCEPT)

### Como PRESTADOR (com moedas):
1. Acesse `login.php` e logue como prestador
2. Vá em `meus-orcamentos.php` (Meus Serviços)
3. Verifique se o badge vermelho aparece com contagem de pedidos novos
4. Abra um pedido e envie proposta (botão "Enviar Proposta")
5. Confirme que aparece distância em KM (não endereço)
6. Confirme que fotos e áudio do pedido aparecem corretamente

### Como CLIENTE:
1. Acesse `login-unificado.php` ou `buscar.php` e logue como cliente
2. Vá em `meus-orcamentos-cli.php` (Minhas Buscas)
3. Verifique se badge vermelho aparece com propostas pendentes
4. Abra o pedido e veja as propostas
5. Clique "Aceitar" → deve aparecer dialog de confirmação
6. Confirme a aceitação
7. **Se prestador tem moedas suficientes (auto-accept):**
   - [ ] Deve redirecionar DIRETO para `chat.php` (não `aguarda-prestador.php`)
   - [ ] Moedas devem ser debitadas automaticamente do prestador
   - [ ] Push notification deve chegar ao prestador: "Acordo Firmado!"
8. **Se prestador NÃO tem moedas:**
   - [ ] Deve redirecionar para `aguarda-prestador.php`
   - [ ] Push notification: "Proposta Aceita!"
   - [ ] Timer de 10 minutos aparece

---

## TESTE 2: Chat funciona corretamente

### No chat (após acordo firmado):
1. [ ] Header padrão (`header-app.php`) aparece no topo
2. [ ] Bottom nav aparece na parte inferior
3. [ ] Iframe do chat carrega corretamente (URL relativa, não `gessomt.app.br` hardcoded)
4. [ ] Enviar mensagem de texto funciona
5. [ ] Botão "📍 Enviar Minha Localização":
   - [ ] Pede permissão de localização
   - [ ] Se negada, mostra alerta claro
   - [ ] Se permitida, envia localização com mapa Google Maps
6. [ ] Botão "💬 WhatsApp":
   - [ ] Link abre WhatsApp com número correto do outro usuário
   - [ ] URL formato `https://wa.me/55XXXXXXXXXXX`
7. [ ] Upload de fotos funciona (URL relativa para `insertFotos.php`)

---

## TESTE 3: Débito de moedas manual (prestador sem auto-accept)

### Como PRESTADOR (na tela de "Cliente aceitou"):
1. [ ] Mostra custo correto da categoria (ex: "Deseja debitar 3 moeda(s)?")
2. [ ] Mostra saldo atual
3. [ ] Se saldo suficiente → botão "SIM" → debita e firma acordo
4. [ ] Se saldo insuficiente → botão redireciona para compra de moedas
5. [ ] Após débito, alerta mostra quantidade correta debitada

---

## TESTE 4: Extrato de Moedas

### Como PRESTADOR em `minhasmoedas.php`:
1. [ ] Header padrão aparece (não `topo2.php` antigo)
2. [ ] Bottom nav aparece
3. [ ] Saldo de moedas correto
4. [ ] Botão "EXTRATO DE COMPRAS" abre seção
5. [ ] **Se tabela `moedas_extrato` existe:**
   - [ ] Créditos aparecem em VERDE com ⬆️
   - [ ] Débitos aparecem em VERMELHO com ⬇️
   - [ ] Descrição e data corretas
6. [ ] **Se tabela não existe:** mostra fallback com compras da tabela `pagamento`

---

## TESTE 5: Push Notifications

### Clique na notificação:
1. [ ] Ao receber push, botão "Ver Pedido" aparece
2. [ ] Clicar "Ver Pedido" → navega para URL correta
3. [ ] Se app já aberto → navega na mesma aba
4. [ ] Se app fechado → abre nova aba

---

## TESTE 6: PWA Install

1. [ ] No Chrome Android: banner de instalação aparece após 3s
2. [ ] Botão "Instalar" funciona (ou mostra instruções)
3. [ ] No iPhone Safari: banner com "Como instalar" aparece
4. [ ] Após dismissar, não reaparece por 24h
5. [ ] Se já instalado, banner não aparece

---

## TESTE 7: Badges de mensagens não lidas

1. [ ] Título da página mostra `(X)` quando há mensagens não lidas
2. [ ] Badge vermelho aparece nos itens do bottom nav
3. [ ] Atualiza a cada 30 segundos
4. [ ] Desaparece quando mensagens são lidas no chat

---

## TESTE 8: Fontes e UI geral

1. [ ] `global-font-size.css` carregado em todas as páginas
2. [ ] Fontes consistentes entre páginas
3. [ ] Bottom nav não sobrepõe conteúdo (padding-bottom presente)
4. [ ] Botão "Voltar" azul padrão (#0ea5e9) no header

---

## TESTE 9: verifica_status.php (CRÍTICO - bug do codpedido=456)

1. Acesse diretamente: `verifica_status.php?codpedido=NUMERO_REAL`
2. [ ] Deve retornar JSON com `aceito` correto para o pedido informado
3. [ ] NÃO deve retornar dados do pedido 456 hardcoded

---

## BUGS CONHECIDOS (pré-existentes, não corrigidos nesta sessão)
- Várias páginas ainda usam `topo2.php` em vez de `header-app.php` (escopo futuro)
- URLs de assets CSS/JS em `perfil.php` e `menu.php` apontam para `gessomt.app.br` (correto para produção)
- Fotos antigas no banco de chat ainda referenciam `viclocacoessinop.com.br`
