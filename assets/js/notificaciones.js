// Configuración de Firebase
const firebaseConfig = {
    apiKey: "TU_API_KEY_AQUI",
    authDomain: "TU_AUTH_DOMAIN_AQUI",
    projectId: "TU_PROJECT_ID_AQUI",
    storageBucket: "TU_STORAGE_BUCKET_AQUI",
    messagingSenderId: "TU_SENDER_ID_AQUI",
    appId: "TU_APP_ID_AQUI"
};

// Inicializar Firebase
firebase.initializeApp(firebaseConfig);

// Obtener instancia de Firebase Cloud Messaging
const messaging = firebase.messaging();

// Solicitar permiso para notificaciones
async function solicitarPermisoNotificaciones() {
    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            console.log('Permiso de notificaciones concedido');
            return true;
        } else {
            console.log('Permiso de notificaciones denegado');
            return false;
        }
    } catch (error) {
        console.error('Error al solicitar permiso de notificaciones:', error);
        return false;
    }
}

// Obtener token FCM
async function obtenerTokenFCM() {
    try {
        const token = await messaging.getToken({
            vapidKey: 'TU_VAPID_KEY_AQUI'
        });
        
        if (token) {
            console.log('Token FCM obtenido:', token);
            return token;
        } else {
            console.log('No se pudo obtener el token FCM');
            return null;
        }
    } catch (error) {
        console.error('Error al obtener token FCM:', error);
        return null;
    }
}

// Actualizar token FCM en el servidor
async function actualizarTokenFCM(token) {
    try {
        const response = await fetch('<?= BASE_URL ?>notificacion/actualizarFCMToken', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `token=${token}`
        });
        
        const data = await response.json();
        if (data.success) {
            console.log('Token FCM actualizado en el servidor');
            return true;
        } else {
            console.error('Error al actualizar token FCM:', data.error);
            return false;
        }
    } catch (error) {
        console.error('Error al actualizar token FCM:', error);
        return false;
    }
}

// Manejar mensajes en primer plano
messaging.onMessage((payload) => {
    console.log('Mensaje recibido:', payload);
    
    // Mostrar notificación
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/assets/img/logo.png',
        badge: '/assets/img/badge.png',
        data: payload.data
    };
    
    // Verificar si el navegador soporta notificaciones
    if ('Notification' in window) {
        new Notification(notificationTitle, notificationOptions);
    }
});

// Manejar clic en notificación
self.addEventListener('notificationclick', (event) => {
    console.log('Notificación clickeada:', event);
    
    event.notification.close();
    
    // Si hay una URL en los datos, abrirla
    if (event.notification.data && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});

// Manejar actualización del token
messaging.onTokenRefresh(async () => {
    console.log('Token FCM actualizado');
    const token = await obtenerTokenFCM();
    if (token) {
        await actualizarTokenFCM(token);
    }
});

// Inicializar notificaciones
async function inicializarNotificaciones() {
    // Verificar si el navegador soporta notificaciones
    if (!('Notification' in window)) {
        console.log('Este navegador no soporta notificaciones');
        return;
    }
    
    // Verificar si ya tenemos permiso
    if (Notification.permission === 'granted') {
        const token = await obtenerTokenFCM();
        if (token) {
            await actualizarTokenFCM(token);
        }
    } else if (Notification.permission !== 'denied') {
        // Solicitar permiso si no está denegado
        const permisoConcedido = await solicitarPermisoNotificaciones();
        if (permisoConcedido) {
            const token = await obtenerTokenFCM();
            if (token) {
                await actualizarTokenFCM(token);
            }
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', inicializarNotificaciones); 