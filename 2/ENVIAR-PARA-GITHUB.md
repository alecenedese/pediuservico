# 🚀 ENVIAR PROJETO PARA O GITHUB - GUIA COMPLETO

## 📋 PRÉ-REQUISITOS

### 1️⃣ INSTALAR GIT
Se o Git não estiver instalado:

**Download:** https://git-scm.com/download/win

**Instalação:**
1. Execute o instalador
2. Use as opções padrão
3. Reinicie o terminal após instalar

**Verificar se instalou:**
```cmd
git --version
```

---

### 2️⃣ CRIAR CONTA NO GITHUB
Se ainda não tiver:
1. Acesse: https://github.com
2. Clique em "Sign up"
3. Crie sua conta

---

## 🔧 CONFIGURAÇÃO INICIAL DO GIT

Abra o terminal e configure seu nome e email:

```cmd
git config --global user.name "Seu Nome"
git config --global user.email "seu-email@exemplo.com"
```

---

## 📝 CRIAR .gitignore (IMPORTANTE!)

**ANTES** de enviar, crie um arquivo `.gitignore` para NÃO enviar arquivos sensíveis:

```
# Arquivos sensíveis - NÃO ENVIAR!
config.php
*.keystore
*.jks
*.pem
credentials.json
.env
*.log

# Pastas grandes
node_modules/
vendor/
.expo/
.git/
build/
dist/

# Arquivos temporários
*.tmp
*.bak
*.swp
*~
.DS_Store
Thumbs.db

# Certificados e chaves
*.key
*.crt
*.p12
upload-key.jks
meu-keystore.keystore
nova-upload-key.jks

# Backups e compactados
*.zip
*.rar
*.7z
*.tar
*.gz

# APK/AAB compilados (opcional - podem ser grandes)
*.apk
*.aab

# Logs
logs/
*.log
npm-debug.log*
yarn-debug.log*
yarn-error.log*
```

---

## 🎯 PASSO A PASSO PARA ENVIAR

### **PASSO 1: Criar repositório no GitHub**

1. Acesse: https://github.com/new
2. Nome do repositório: `maoamiga` (ou o nome que preferir)
3. Descrição: "Sistema de pedidos e orçamentos"
4. **Privado ou Público:** ESCOLHA **PRIVADO** (por segurança)
5. **NÃO** marque "Add a README file"
6. Clique em "Create repository"

---

### **PASSO 2: Inicializar Git localmente**

Abra o terminal na pasta do projeto:

```cmd
cd c:\projetos\maoamiganovo
git init
```

---

### **PASSO 3: Adicionar arquivos**

```cmd
git add .
```

**⚠️ CUIDADO:** Isso adiciona TODOS os arquivos. Se esqueceu de criar o `.gitignore`, PARE AQUI e crie primeiro!

---

### **PASSO 4: Fazer o primeiro commit**

```cmd
git commit -m "Primeiro commit - Sistema Mão Amiga"
```

---

### **PASSO 5: Conectar ao GitHub**

Substitua `SEU_USUARIO` e `NOME_DO_REPO` pelos seus dados:

```cmd
git remote add origin https://github.com/SEU_USUARIO/NOME_DO_REPO.git
```

Exemplo:
```cmd
git remote add origin https://github.com/joaosilva/maoamiga.git
```

---

### **PASSO 6: Renomear branch para main (GitHub usa main por padrão)**

```cmd
git branch -M main
```

---

### **PASSO 7: Enviar para o GitHub**

```cmd
git push -u origin main
```

**Vai pedir autenticação:**
- **Usuário:** seu username do GitHub
- **Senha:** use um **Personal Access Token** (não a senha da conta)

---

## 🔑 CRIAR TOKEN DE ACESSO (Personal Access Token)

GitHub não aceita mais senha normal. Você precisa de um token:

1. Acesse: https://github.com/settings/tokens
2. Clique em "Generate new token" → "Generate new token (classic)"
3. Nome: "Git Token"
4. Validade: 90 dias (ou mais)
5. Marque: **repo** (todas as opções de repo)
6. Clique em "Generate token"
7. **COPIE O TOKEN** (só aparece uma vez!)
8. Use esse token como senha no `git push`

---

## 🎉 COMANDOS RESUMIDOS (DEPOIS DE INSTALAR GIT)

```cmd
cd c:\projetos\maoamiganovo

REM Criar .gitignore primeiro!

git init
git add .
git commit -m "Primeiro commit"
git remote add origin https://github.com/SEU_USUARIO/SEU_REPO.git
git branch -M main
git push -u origin main
```

---

## 🔄 PRÓXIMAS ATUALIZAÇÕES

Depois do primeiro envio, quando fizer alterações:

```cmd
cd c:\projetos\maoamiganovo
git add .
git commit -m "Descrição das alterações"
git push
```

---

## ⚠️ ARQUIVOS QUE **NÃO DEVEM** IR PARA O GITHUB

**MUITO IMPORTANTE:** Estes arquivos contêm dados sensíveis!

❌ `config.php` - Senha do banco de dados
❌ `*.keystore`, `*.jks` - Certificados do app
❌ `credentials.json` - Credenciais de API
❌ `*.pem` - Certificados
❌ `.env` - Variáveis de ambiente

**Se já enviou algum desses, precisa:**
1. Remover do GitHub
2. Trocar as senhas/chaves
3. Adicionar ao .gitignore

---

## 📱 IGNORAR PASTA DO APP ANDROID (OPCIONAL)

Se a pasta `app-android` é muito grande, adicione ao `.gitignore`:

```
app-android/node_modules/
app-android/build/
app-android/.gradle/
app-android/*.apk
app-android/*.aab
```

Ou ignore tudo:
```
app-android/
```

---

## 🔍 VERIFICAR O QUE VAI SER ENVIADO

Antes de fazer `git add .`, veja o que será adicionado:

```cmd
git status
```

Mostra todos os arquivos que serão incluídos.

---

## 🛠️ CRIAR config.php.example (BOA PRÁTICA)

Para colaboradores saberem como configurar, crie um arquivo de exemplo:

**config.php.example:**
```php
<?php
// Configuração do banco de dados
$host = "localhost";
$usuario = "SEU_USUARIO";
$senha = "SUA_SENHA";
$banco = "NOME_DO_BANCO";

$con = mysqli_connect($host, $usuario, $senha, $banco);

if (!$con) {
    die("Erro de conexão: " . mysqli_connect_error());
}
?>
```

Adicione uma instrução no README:
```
Copie config.php.example para config.php e configure suas credenciais.
```

---

## 📖 CRIAR README.md

Crie um arquivo `README.md` na raiz do projeto:

```markdown
# 🛠️ Sistema Mão Amiga

Sistema de pedidos e orçamentos para prestadores de serviço.

## 📋 Requisitos

- PHP 7.4+
- MySQL 5.7+
- Node.js 16+ (para o app mobile)

## 🚀 Instalação

1. Clone o repositório
2. Copie `config.php.example` para `config.php`
3. Configure suas credenciais do banco de dados
4. Importe o banco de dados (se tiver SQL)
5. Execute o projeto

## 📱 App Android

O app está na pasta `app-android/`.

```

---

## ✅ CHECKLIST FINAL

Antes de enviar:

- [ ] Git instalado
- [ ] Conta no GitHub criada
- [ ] `.gitignore` criado
- [ ] `config.php` adicionado ao `.gitignore`
- [ ] Arquivos sensíveis não serão enviados
- [ ] Repositório criado no GitHub (privado)
- [ ] Token de acesso gerado

Depois de enviar:

- [ ] Projeto aparece no GitHub
- [ ] Arquivos sensíveis NÃO estão lá
- [ ] README.md criado (opcional)

---

## 🆘 PROBLEMAS COMUNS

### Erro: "Git não é reconhecido"
**Solução:** Instale o Git e reinicie o terminal.

### Erro: "Permission denied"
**Solução:** Use token de acesso, não senha.

### Enviou arquivo sensível por engano
**Solução:**
```cmd
git rm --cached config.php
git commit -m "Remove config.php"
git push
```
**Depois troque as senhas!**

### Repositório muito grande
**Solução:** Adicione mais pastas ao `.gitignore`:
- `node_modules/`
- `*.apk`
- `*.aab`

---

## 🎯 RESULTADO ESPERADO

Após seguir este guia:
- ✅ Projeto no GitHub
- ✅ Arquivos sensíveis protegidos
- ✅ Versionamento ativo
- ✅ Backup na nuvem
- ✅ Colaboração facilitada

---

**PRONTO! Agora é só seguir os passos! 🚀**
