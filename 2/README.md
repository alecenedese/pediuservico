# 🤝 Sistema Mão Amiga

Sistema completo de pedidos e orçamentos conectando consumidores e prestadores de serviço.

## 📋 Sobre o Projeto

Plataforma web e mobile que permite:
- 📝 Consumidores solicitarem serviços
- 💼 Prestadores enviarem orçamentos
- 💬 Chat entre consumidor e prestador
- 📱 Notificações push em tempo real
- ⭐ Sistema de avaliações
- 🪙 Sistema de moedas virtuais
- ✅ Verificação de perfil

---

## 🚀 Tecnologias Utilizadas

### Backend
- PHP 7.4+
- MySQL 5.7+
- API RESTful

### Frontend
- HTML5, CSS3, JavaScript
- Bootstrap / CSS customizado
- jQuery

### Mobile
- React Native
- Expo
- WebView integrado

### Notificações
- Expo Push Notifications
- OneSignal (PWA)

---

## 📦 Requisitos do Sistema

- **PHP:** 7.4 ou superior
- **MySQL:** 5.7 ou superior
- **Node.js:** 16+ (para o app mobile)
- **Composer:** (opcional, se usar dependências PHP)
- **Git:** para versionamento

---

## 🔧 Instalação

### 1️⃣ Clone o repositório

```bash
git clone https://github.com/SEU_USUARIO/maoamiga.git
cd maoamiga
```

### 2️⃣ Configure o banco de dados

```bash
# Copie o arquivo de exemplo
cp config.php.example config.php

# Edite config.php com suas credenciais
nano config.php
```

**config.php:**
```php
$host = "localhost";
$usuario = "seu_usuario";
$senha = "sua_senha";
$banco = "maoamiga";
```

### 3️⃣ Importe o banco de dados

```bash
# Se tiver arquivo SQL
mysql -u root -p maoamiga < database.sql
```

Ou execute os scripts SQL:
- `criar-tabela-push-tokens.sql` (notificações)
- Outros scripts SQL na pasta raiz

### 4️⃣ Configure permissões (Linux/Mac)

```bash
chmod 755 uploads/
chmod 755 imagens/
```

---

## 📱 App Android (Expo)

### Instalação

```bash
cd app-android
npm install
```

### Desenvolvimento

```bash
npm start
# Escaneie o QR Code com Expo Go
```

### Build Production

```bash
# APK para testes
npx eas build --platform android --profile preview

# AAB para Google Play
npx eas build --platform android --profile production
```

---

## 📖 Estrutura do Projeto

```
maoamiga/
├── adm/                        # Área administrativa
├── api/                        # APIs (push, integrations)
├── app-android/                # App React Native
├── imagens/                    # Assets e uploads
├── uploads/                    # Uploads de usuários
├── *.php                       # Páginas principais
├── config.php.example          # Exemplo de configuração
├── .gitignore                  # Arquivos ignorados
└── README.md                   # Este arquivo
```

---

## 🔑 Configurações Importantes

### Notificações Push

1. Execute o SQL:
```bash
mysql -u root -p maoamiga < criar-tabela-push-tokens.sql
```

2. Configure as credenciais no arquivo de notificações

3. Teste:
```
https://seu-dominio.com/verificar-tokens.php
```

### Sistema de Moedas

Sistema de créditos virtuais onde prestadores gastam moedas para:
- Enviar orçamentos
- Firmar acordos
- Acessar contatos

---

## 🧪 Testes

### Verificar instalação

1. Acesse: `https://seu-dominio.com/`
2. Faça login como consumidor ou prestador
3. Teste criar um pedido
4. Teste enviar um orçamento

### Dashboard de tokens push

```
https://seu-dominio.com/verificar-tokens.php
```

---

## 📚 Documentação Adicional

- `NOTIFICACOES-PUSH-GUIA.md` - Guia completo de notificações
- `ENVIAR-PARA-GITHUB.md` - Como versionar o projeto
- `EXECUTAR-AGORA.md` - Setup rápido do app mobile
- `DEBUG-APP-ANDROID.md` - Debug do app nativo

---

## 🔒 Segurança

**Arquivos sensíveis protegidos (não enviados ao GitHub):**
- `config.php` - Credenciais do banco
- `*.jks`, `*.keystore` - Certificados Android
- `credentials.json` - Credenciais de APIs

**Sempre:**
- Use `mysqli_real_escape_string()` em queries
- Valide entradas do usuário
- Mantenha senhas fortes
- Use HTTPS em produção

---

## 🐛 Troubleshooting

### Erro: "config.php not found"
```bash
cp config.php.example config.php
# Configure suas credenciais
```

### Erro: "Table doesn't exist"
```bash
# Execute os scripts SQL
mysql -u root -p maoamiga < criar-tabela-push-tokens.sql
```

### App Android não compila
```bash
cd app-android
rm -rf node_modules
npm install
```

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

---

## 📝 Licença

Este projeto é proprietário. Todos os direitos reservados.

---

## 👤 Autor

**Equipe Mão Amiga**

- Website: https://gessomt.app.br/pediuservico/
- Email: contato@gessomt.app.br

---

## 📊 Status do Projeto

🚧 **Em Desenvolvimento Ativo**

### Features Implementadas
- ✅ Sistema de pedidos e orçamentos
- ✅ Chat em tempo real
- ✅ Notificações push (web + mobile)
- ✅ Sistema de moedas
- ✅ Verificação de perfil
- ✅ Geolocalização
- ✅ Upload de fotos
- ✅ Sistema de avaliações

### Próximas Features
- 🔄 Dashboard de analytics
- 🔄 Integração com pagamentos
- 🔄 App iOS
- 🔄 Sistema de assinatura

---

## 📞 Suporte

Para suporte, envie um email para contato@gessomt.app.br ou abra uma issue.

---

**Desenvolvido com ❤️ pela equipe Mão Amiga**
