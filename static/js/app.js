// Configuración de Pusher
const pusherKey = document.querySelector('meta[name="pusher-key"]').getAttribute('content');
const pusherCluster = document.querySelector('meta[name="pusher-cluster"]').getAttribute('content');

let pusher, canal;
let ultimoMensajeId = null;
let modoLocal = false;

function escaparHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

function crearBurbujaMensaje(usuario, texto, esMio = false) {
    const burbuja = document.createElement('div');
    burbuja.className = `mensaje ${esMio ? 'mensaje-propio' : 'mensaje-otro'}`;
    
    const contenido = document.createElement('div');
    contenido.className = 'contenido-mensaje';
    
    const nombreUsuario = document.createElement('div');
    nombreUsuario.className = 'nombre-usuario';
    nombreUsuario.textContent = usuario;
    
    const textoMensaje = document.createElement('div');
    textoMensaje.className = 'texto-mensaje';
    textoMensaje.textContent = texto;
    
    contenido.appendChild(nombreUsuario);
    contenido.appendChild(textoMensaje);
    burbuja.appendChild(contenido);
    
    return burbuja;
}

function agregarMensaje(usuario, texto, esMio = false, id = null) {
    const areaMensajes = document.getElementById('mensajes');
    const burbuja = crearBurbujaMensaje(usuario, texto, esMio);
    if (id) {
        burbuja.setAttribute('data-id', id);
    }
    areaMensajes.appendChild(burbuja);
    areaMensajes.scrollTop = areaMensajes.scrollHeight;
}

// Cargar mensajes desde el servidor
async function cargarMensajes() {
    try {
        const response = await fetch('api.php');
        const data = await response.json();
        
        if (data.mensajes) {
            const areaMensajes = document.getElementById('mensajes');
            const usuarioActual = document.getElementById('usuario').value.trim();
            
            areaMensajes.innerHTML = '';
            
            data.mensajes.forEach(mensaje => {
                const esMio = mensaje.usuario === usuarioActual;
                agregarMensaje(mensaje.usuario, mensaje.texto, esMio, mensaje.id);
                ultimoMensajeId = mensaje.id;
            });
        }
    } catch (error) {
        console.error('Error cargando mensajes:', error);
    }
}

// Verificar nuevos mensajes
async function verificarNuevosMensajes() {
    if (!modoLocal) return;
    
    try {
        const response = await fetch('api.php');
        const data = await response.json();
        
        if (data.mensajes) {
            const usuarioActual = document.getElementById('usuario').value.trim();
            
            data.mensajes.forEach(mensaje => {
                if (!ultimoMensajeId || mensaje.id > ultimoMensajeId) {
                    const esMio = mensaje.usuario === usuarioActual;
                    agregarMensaje(mensaje.usuario, mensaje.texto, esMio, mensaje.id);
                    ultimoMensajeId = mensaje.id;
                }
            });
        }
    } catch (error) {
        console.error('Error verificando mensajes:', error);
    }
}

async function enviarMensaje() {
    const campoUsuario = document.getElementById('usuario');
    const campoTexto = document.getElementById('texto');
    
    const usuario = campoUsuario.value.trim();
    const texto = campoTexto.value.trim();
    
    if (!usuario || !texto) {
        alert('Por favor, ingresa tu nombre y un mensaje');
        return;
    }
    
    try {
        const respuesta = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                usuario: usuario,
                texto: texto
            })
        });
        
        if (respuesta.ok) {
            const resultado = await respuesta.json();
            
            // Si estamos en modo local, agregar el mensaje inmediatamente
            if (modoLocal && resultado.mensaje) {
                const esMio = true;
                agregarMensaje(resultado.mensaje.usuario, resultado.mensaje.texto, esMio, resultado.mensaje.id);
                ultimoMensajeId = resultado.mensaje.id;
            }
            
            campoTexto.value = '';
            campoTexto.focus();
        } else {
            const error = await respuesta.json();
            console.error('Error del servidor:', error);
            alert('Error: ' + (error.error || 'No se pudo enviar el mensaje'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

function inicializarPusher() {
    if (!pusherKey || pusherKey === 'TU_PUSHER_KEY') {
        console.error('Pusher no configurado correctamente');
        inicializarModoLocal();
        return;
    }
    
    try {
        pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            forceTLS: true
        });
        
        pusher.connection.bind('connected', function() {
            console.log('Pusher conectado exitosamente');
        });
        
        pusher.connection.bind('failed', function() {
            console.error('Conexión con Pusher falló');
        });
        
        canal = pusher.subscribe('chat');
        
        // Cuando llega un mensaje nuevo
        canal.bind("nuevo_mensaje", function (datos) {
            const usuarioActual = document.getElementById('usuario').value.trim();
            const esMio = datos.usuario === usuarioActual;
            agregarMensaje(datos.usuario, datos.texto, esMio, datos.id);
            ultimoMensajeId = datos.id;
        });
        
        pusher.connection.bind('error', function(err) {
            console.error('Error de Pusher:', err);
        });
        
    } catch (error) {
        console.error('Error conectando con Pusher:', error);
        inicializarModoLocal();
    }
}

function inicializarModoLocal() {
    modoLocal = true;
    console.warn('MODO LOCAL ACTIVADO - Pusher no disponible');
    
    cargarMensajes();
    setInterval(verificarNuevosMensajes, 5000);
}

// Configuración inicial
document.addEventListener('DOMContentLoaded', function() {
    inicializarPusher();
    
    const botonEnviar = document.getElementById('enviar');
    const campoTexto = document.getElementById('texto');
    
    botonEnviar.addEventListener('click', enviarMensaje);
    
    campoTexto.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            enviarMensaje();
        }
    });
    
    document.getElementById('usuario').focus();
});