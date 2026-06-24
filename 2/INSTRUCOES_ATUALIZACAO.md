# Instruções de Atualização - USERVICE

## Melhorias Implementadas

### 1. ✅ Barra de Rolagem nas Subcategorias do Prestador
- **Arquivos modificados:** `edicao.php`, `edicao2.php`
- **Mudança:** Aumentada altura máxima de 150px para 250px e alterado `overflow-y: auto` para `overflow-y: scroll`
- **Resultado:** Barra de rolagem sempre visível quando há muitas subcategorias

### 2. ✅ Tamanho de Fonte Padronizado
- **Arquivo criado:** `global-font-size.css`
- **Arquivo modificado:** `topo2.php` (incluído o CSS global)
- **Mudança:** Fontes aumentadas em todo o aplicativo para melhor legibilidade
- **Resultado:** Tamanho de fonte consistente e maior em todo o sistema

### 3. ✅ Tela de Aguardo Após Cliente Aceitar Orçamento
- **Arquivo criado:** `aguarda-prestador.php`
- **Arquivo modificado:** `pegar_contato.php`
- **Mudança:** Cliente é redirecionado para tela de aguardo após aceitar orçamento
- **Resultado:** Cliente aguarda na tela até prestador firmar acordo

### 4. ✅ Timer de 10 Minutos para Prestador Firmar Acordo
- **Arquivos criados:**
  - `aguarda-prestador.php` (tela com timer)
  - `verifica_acordo.php` (verifica status do acordo)
  - `marcar-perdido.php` (marca como perdido após expiração)
  - `criar_tabela_timer.sql` (script SQL)
- **Mudança:** Timer de 10:00 minutos com verificação automática
- **Resultado:** Após 10 minutos sem confirmação, mostra mensagem: "O prestador parece que não conseguiu firmar acordo, escolha o orçamento de outro prestador"

### 5. ✅ Status 'Aceito' Apenas Após Débito de Moeda
- **Arquivo modificado:** `debitar_moedas.php`
- **Mudança:** Status só muda para 's' (confirmado) APÓS débito bem-sucedido
- **Resultado:** Cliente só vê como "aceito" quando prestador realmente debitar a moeda

### 6. ✅ Layout do Chat com Formatação dos Cards de Orçamentos
- **Arquivos modificados:** `chat.php`, `chat2.php`
- **Mudança:** Aplicadas mesmas cores e formatação dos cards de orçamentos
- **Resultado:** Chat com visual consistente com resto do aplicativo

## ⚠️ AÇÃO NECESSÁRIA - Executar SQL

Para que o sistema de timer funcione corretamente, você precisa executar o seguinte SQL no banco de dados:

```sql
CREATE TABLE IF NOT EXISTS timer_acordo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codpedido VARCHAR(50) NOT NULL,
    codcadastro VARCHAR(50) NOT NULL,
    tempo_expiracao DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'aguardando',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codpedido (codpedido),
    INDEX idx_codcadastro (codcadastro),
    INDEX idx_status (status)
);
```

### Como executar:

1. Acesse o phpMyAdmin ou seu gerenciador de banco de dados
2. Selecione o banco de dados do USERVICE
3. Vá na aba "SQL"
4. Cole o código acima
5. Clique em "Executar"

Ou execute o arquivo `criar_tabela_timer.sql` que foi criado.

## Fluxo Atualizado

### Quando Cliente Aceita Orçamento:

1. Cliente clica em "Aceitar Orçamento"
2. Sistema redireciona para `aguarda-prestador.php`
3. Timer de 10:00 minutos começa a contar
4. Sistema verifica a cada 2 segundos se prestador confirmou
5. **Se prestador debitar moeda:** Cliente é redirecionado automaticamente para o chat
6. **Se timer expirar (10 minutos):** Mostra mensagem de erro e botão para voltar

### Quando Prestador Confirma:

1. Prestador vê notificação de que cliente aceitou
2. Prestador clica para debitar moeda
3. Sistema debita moeda E marca status como 's' (confirmado)
4. Cliente na tela de aguardo é automaticamente redirecionado para chat

## Arquivos Criados

- `global-font-size.css` - CSS global para padronização de fontes
- `aguarda-prestador.php` - Tela de aguardo com timer
- `verifica_acordo.php` - API para verificar status do acordo
- `marcar-perdido.php` - API para marcar acordo como perdido
- `criar_tabela_timer.sql` - Script SQL para criar tabela
- `INSTRUCOES_ATUALIZACAO.md` - Este arquivo

## Arquivos Modificados

- `edicao.php` - Barra de rolagem subcategorias
- `edicao2.php` - Barra de rolagem subcategorias
- `topo2.php` - Incluído CSS global
- `pegar_contato.php` - Redirecionamento para tela de aguardo
- `debitar_moedas.php` - Status 's' apenas após débito
- `chat.php` - Formatação dos cards
- `chat2.php` - Formatação dos cards

## Testes Recomendados

1. **Teste de Subcategorias:** Cadastre prestador com muitas subcategorias e verifique barra de rolagem
2. **Teste de Fontes:** Navegue pelas páginas e verifique se fontes estão maiores e consistentes
3. **Teste de Timer:** Cliente aceita orçamento e aguarda 10 minutos sem prestador confirmar
4. **Teste de Confirmação:** Cliente aceita e prestador confirma antes de 10 minutos
5. **Teste de Chat:** Verifique se layout do chat está com cores dos cards de orçamentos

## Observações Importantes

- O timer funciona com JavaScript no lado do cliente e verificação no servidor
- A tabela `timer_acordo` é essencial para o funcionamento do sistema
- O status 'ac' significa "aceito pelo cliente, aguardando prestador"
- O status 's' significa "confirmado pelo prestador (moeda debitada)"
- O status 'p' significa "perdido/expirado"

## Suporte

Se houver algum problema, verifique:
1. Se a tabela `timer_acordo` foi criada corretamente
2. Se o arquivo `global-font-size.css` está acessível
3. Se os cookies de sessão estão funcionando
4. Logs de erro do PHP para debugging
