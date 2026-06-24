@echo off
chcp 65001 > nul
echo ========================================
echo 🚀 SETUP GITHUB - MAO AMIGA
echo ========================================
echo.

REM Verificar se o Git está instalado
git --version > nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Git não está instalado!
    echo.
    echo Download: https://git-scm.com/download/win
    echo.
    echo Instale o Git e execute este script novamente.
    pause
    exit /b 1
)

echo ✅ Git instalado!
echo.

REM Verificar se .gitignore existe
if not exist ".gitignore" (
    echo ⚠️ .gitignore não encontrado!
    echo Criando .gitignore...
    echo Arquivo criado anteriormente, verifique se existe!
    pause
    exit /b 1
)

echo ✅ .gitignore encontrado!
echo.

REM Verificar se config.php existe (não deve ser enviado)
if exist "config.php" (
    echo ⚠️ ATENÇÃO: config.php encontrado!
    echo Certifique-se que está no .gitignore
    echo.
)

REM Perguntar nome do usuário
echo Digite seu nome para commits:
set /p GIT_NAME=Nome: 

REM Perguntar email
echo.
echo Digite seu email:
set /p GIT_EMAIL=Email: 

REM Configurar Git
echo.
echo Configurando Git...
git config --global user.name "%GIT_NAME%"
git config --global user.email "%GIT_EMAIL%"
echo ✅ Git configurado!
echo.

REM Verificar se já é um repositório Git
if exist ".git" (
    echo ⚠️ Este projeto já é um repositório Git!
    echo.
    choice /C SN /M "Deseja continuar mesmo assim? (S/N)"
    if errorlevel 2 (
        echo Operação cancelada.
        pause
        exit /b 0
    )
) else (
    echo Inicializando repositório Git...
    git init
    echo ✅ Repositório inicializado!
    echo.
)

REM Adicionar arquivos
echo Adicionando arquivos...
git add .
echo ✅ Arquivos adicionados!
echo.

REM Mostrar status
echo Status do repositório:
echo.
git status
echo.

REM Perguntar se quer fazer commit
choice /C SN /M "Fazer commit agora? (S/N)"
if errorlevel 2 (
    echo.
    echo ⚠️ Commit não realizado.
    echo Execute manualmente: git commit -m "Primeiro commit"
    pause
    exit /b 0
)

REM Fazer commit
echo.
echo Fazendo commit...
git commit -m "Primeiro commit - Sistema Mao Amiga"
echo ✅ Commit realizado!
echo.

REM Renomear branch para main
echo Renomeando branch para main...
git branch -M main
echo ✅ Branch renomeada!
echo.

REM Perguntar URL do repositório
echo ========================================
echo IMPORTANTE: Crie o repositório no GitHub ANTES!
echo https://github.com/new
echo ========================================
echo.
echo Digite a URL do seu repositório:
echo Exemplo: https://github.com/usuario/maoamiga.git
echo.
set /p REPO_URL=URL: 

if "%REPO_URL%"=="" (
    echo ❌ URL não informada!
    echo.
    echo Execute manualmente:
    echo git remote add origin https://github.com/usuario/repo.git
    echo git push -u origin main
    pause
    exit /b 1
)

REM Adicionar remote
echo.
echo Adicionando remote...
git remote add origin %REPO_URL%
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️ Remote já existe ou URL inválida
    echo Tentando atualizar...
    git remote set-url origin %REPO_URL%
)
echo ✅ Remote configurado!
echo.

REM Perguntar se quer fazer push
echo ========================================
echo ATENÇÃO: Você precisará de um TOKEN!
echo https://github.com/settings/tokens
echo ========================================
echo.
choice /C SN /M "Fazer push para o GitHub agora? (S/N)"
if errorlevel 2 (
    echo.
    echo ⚠️ Push não realizado.
    echo.
    echo Execute manualmente:
    echo git push -u origin main
    echo.
    echo Quando pedir senha, use o TOKEN do GitHub!
    pause
    exit /b 0
)

REM Fazer push
echo.
echo Fazendo push...
echo.
echo ⚠️ Use o TOKEN como senha, não a senha da conta!
echo.
git push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo ✅ SUCESSO!
    echo ========================================
    echo.
    echo Seu projeto está no GitHub!
    echo Acesse: %REPO_URL:~0,-4%
    echo.
) else (
    echo.
    echo ========================================
    echo ❌ ERRO NO PUSH
    echo ========================================
    echo.
    echo Possíveis causas:
    echo 1. Token inválido ou expirado
    echo 2. URL do repositório incorreta
    echo 3. Problemas de conexão
    echo.
    echo Tente novamente:
    echo git push -u origin main
    echo.
)

pause
