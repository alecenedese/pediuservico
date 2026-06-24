// Service Worker - Pediu Serviço PWA
const CACHE_NAME = 'pediuservico-v9';
const BASE_URL = '/pediuservico';
const urlsToCache = [
  BASE_URL + '/',
  BASE_URL + '/buscar.php',
  BASE_URL + '/global-font-size.css',
  BASE_URL + '/manifest.json'
];

// Instalação do Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache aberto');
        return cache.addAll(urlsToCache);
      })
      .catch(err => console.log('Erro ao cachear:', err))
  );
  self.skipWaiting();
});

// Ativação - limpa caches antigos
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Removendo cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Intercepta requisições - Network First strategy
self.addEventListener('fetch', event => {
  // Ignora requisições POST e API (não podem ser cacheadas)
  if (event.request.method !== 'GET') return;
  if (event.request.url.includes('/api/')) return;
  
  event.respondWith(
    fetch(event.request)
      .then(response => {
        if (response && response.status === 200 && response.type === 'basic') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        return caches.match(event.request);
      })
  );
});

// Push Notifications
self.addEventListener('push', event => {
  console.log('Push recebido:', event);
  
  let data = {
    title: 'Pediu Serviço',
    body: 'Você tem uma nova notificação',
    icon: BASE_URL + '/icons/icon-192x192.png',
    badge: BASE_URL + '/icons/icon-72x72.png',
    url: BASE_URL + '/index.php'
  };
  
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data.body = event.data.text();
    }
  }
  
  const options = {
    body: data.body,
    icon: data.icon || BASE_URL + '/icons/icon-192x192.png',
    badge: data.badge || BASE_URL + '/icons/icon-192x192.png',
    image: data.icon || BASE_URL + '/icons/icon-192x192.png',
    vibrate: [200, 100, 200, 100, 200],
    data: {
      url: data.url || BASE_URL + '/meus-orcamentos.php',
      dateOfArrival: Date.now()
    },
    actions: [
      { action: 'open', title: 'Ver Pedido' },
      { action: 'close', title: 'Fechar' }
    ],
    requireInteraction: true,
    renotify: true,
    tag: data.tag || 'notification-' + Date.now(),
    silent: false
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Clique na notificação
self.addEventListener('notificationclick', event => {
  console.log('Notificação clicada:', event);
  event.notification.close();
  
  if (event.action === 'close') {
    return;
  }
  
  // URL vem do payload do push
  var clickUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : null;
  
  // Se não tem URL, usa padrão
  if (!clickUrl) {
    clickUrl = BASE_URL + '/meus-orcamentos.php';
  }
  
  // Garante que a URL começa com /
  if (clickUrl.indexOf('http') !== 0 && clickUrl.indexOf('/') !== 0) {
    clickUrl = '/' + clickUrl;
  }
  
  // Monta URL absoluta
  var fullUrl;
  if (clickUrl.indexOf('http') === 0) {
    fullUrl = clickUrl;
  } else {
    fullUrl = self.location.origin + clickUrl;
  }
  console.log('Abrindo URL:', fullUrl);
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(function(windowClients) {
        // Try to find existing app window and focus it first, then navigate
        for (var i = 0; i < windowClients.length; i++) {
          var client = windowClients[i];
          if ('focus' in client) {
            return client.focus().then(function(focusedClient) {
              if (focusedClient && 'navigate' in focusedClient) {
                return focusedClient.navigate(fullUrl);
              }
            }).catch(function() {
              return clients.openWindow(fullUrl);
            });
          }
        }
        // No existing window found, open new one
        return clients.openWindow(fullUrl);
      })
      .catch(function(err) {
        console.error('Erro ao abrir URL da notificação:', err);
        return clients.openWindow(fullUrl);
      })
  );
});

// Fecha notificação
self.addEventListener('notificationclose', event => {
  console.log('Notificação fechada:', event);
});
