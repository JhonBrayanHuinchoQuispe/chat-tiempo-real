let usuariosColores = {};
let contadorUsuarios = 0;

function cargarMensajes() {
    fetch('?action=messages')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            const container = document.getElementById('mensajes');
            if (!container) return;
            
            container.innerHTML = '';
            
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    // Asignar tipo de usuario consistente
                    if (!usuariosColores[msg.usuario]) {
                        contadorUsuarios++;
                        usuariosColores[msg.usuario] = (contadorUsuarios % 2) + 1;
                    }
                    
                    const div = document.createElement('div');
                    div.className = 'mensaje usuario-' + usuariosColores[msg.usuario];
                    
                    // Formatear timestamp a hora 
                    const fecha = new Date(msg.timestamp);
                    const tiempo = fecha.toLocaleTimeString('es-PE', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    
                    div.innerHTML = `
                        <div class="contenido-mensaje-wrapper">
                            <div class="usuario">${msg.usuario}</div>
                            <div class="contenido-mensaje">${msg.mensaje}</div>
                            <div class="timestamp">${tiempo}</div>
                        </div>
                    `;
                    container.appendChild(div);
                });
                container.scrollTop = container.scrollHeight;
            } else {
                container.innerHTML = '<div class="sin-mensajes">No hay mensajes aún</div>';
            }
        })
        .catch(error => {
            console.error('Error de conexión:', error);
        });
}

function enviarMensaje(event) {
    if (event) event.preventDefault();
    
    const usuario = document.getElementById('usuario').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();
    
    if (!usuario || !mensaje) {
        return false;
    }
    
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('usuario', usuario);
    formData.append('mensaje', mensaje);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            return;
        }
        
        document.getElementById('mensaje').value = '';
        setTimeout(cargarMensajes, 300);
    })
    .catch(error => {
        console.error('Error de envío:', error);
    });
    
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    cargarMensajes();
    setInterval(cargarMensajes, 3000);
    
    document.getElementById('mensaje').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje();
        }
    });
});