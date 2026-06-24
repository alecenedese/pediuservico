// Configuração de Push Notifications - Pediu Serviço
// Usando Web Push API com VAPID

const BASE_URL = '/pediuservico';

const PushHelper = {
  // VAPID Public Key - será gerada pelo servidor
  publicVapidKey: '', // Será preenchido dinamicamente
  
  // Verifica se o navegador suporta
  isSupported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
  },
  
  // Solicita permissão de notificação
  async requestPermission() {
    if (!this.isSupported()) {
      console.log('Push notifications não suportadas');
      return false;
    }
    
    const permission = await Notification.requestPermission();
    console.log('Permissão de notificação:', permission);
    return permission === 'granted';
  },
  
  // Registra o Service Worker
  async registerServiceWorker() {
    try {
      const registration = await navigator.serviceWorker.register(BASE_URL + '/sw.js');
      console.log('Service Worker registrado:', registration);
      return registration;
    } catch (error) {
      console.error('Erro ao registrar Service Worker:', error);
      return null;
    }
  },
  
  // Converte a chave VAPID para Uint8Array
  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, '+')
      .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  },
  
  // Inscreve para push notifications
  async subscribe(userId, userType = 'prestador') {
    try {
      // Busca a chave VAPID do servidor
      const configResponse = await fetch(BASE_URL + '/api/push-config.php');
      const config = await configResponse.json();
      this.publicVapidKey = config.publicKey;
      
      const registration = await navigator.serviceWorker.ready;
      
      // Verifica se já está inscrito
      let subscription = await registration.pushManager.getSubscription();
      
      // Força re-inscrição se a subscription existente está num SW diferente
      // ou se não existe subscription ainda
      if (subscription) {
        // Verifica se o escopo do SW mudou (ex: /sw.js -> /pediuservico/sw.js)
        var swScope = registration.scope || '';
        var needsResubscribe = swScope.indexOf('/pediuservico') === -1;
        if (needsResubscribe) {
          console.log('SW escopo mudou, re-inscrevendo push...');
          await subscription.unsubscribe();
          subscription = null;
        }
      }
      
      if (!subscription) {
        // Cria nova inscrição
        subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: this.urlBase64ToUint8Array(this.publicVapidKey)
        });
        console.log('Nova inscrição criada:', subscription);
      }
      
      // Envia a inscrição para o servidor
      const response = await fetch(BASE_URL + '/api/push-subscribe.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          subscription: subscription,
          user_id: userId,
          user_type: userType
        })
      });
      
      const result = await response.json();
      console.log('Inscrição salva no servidor:', result);
      return result.success;
      
    } catch (error) {
      console.error('Erro ao inscrever para push:', error);
      return false;
    }
  },
  
  // Cancela inscrição
  async unsubscribe(userId) {
    try {
      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.getSubscription();
      
      if (subscription) {
        await subscription.unsubscribe();
        
        // Remove do servidor
        await fetch(BASE_URL + '/api/push-unsubscribe.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            user_id: userId
          })
        });
        
        console.log('Inscrição cancelada');
        return true;
      }
      return false;
    } catch (error) {
      console.error('Erro ao cancelar inscrição:', error);
      return false;
    }
  },
  
  // Verifica status da inscrição
  async getSubscriptionStatus() {
    try {
      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.getSubscription();
      return subscription !== null;
    } catch (error) {
      return false;
    }
  },
  
  // Inicializa tudo
  async init(userId, userType = 'prestador') {
    if (!this.isSupported()) {
      console.log('Navegador não suporta Push Notifications');
      return { supported: false, subscribed: false };
    }
    
    // Registra service worker
    await this.registerServiceWorker();
    
    // Solicita permissão
    const hasPermission = await this.requestPermission();
    if (!hasPermission) {
      return { supported: true, permission: false, subscribed: false };
    }
    
    // Inscreve
    const subscribed = await this.subscribe(userId, userType);
    return { supported: true, permission: true, subscribed };
  }
};

// Exporta para uso global
window.PushHelper = PushHelper;
