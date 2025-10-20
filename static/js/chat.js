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
                // Obtener el nombre del usuario actual
                const usuarioActual = document.getElementById('usuario').value.trim();
                
                data.messages.forEach(msg => {
                    const div = document.createElement('div');
                    
                    // Determinar si es el usuario actual o no
                    if (usuarioActual && msg.usuario === usuarioActual) {
                        // Mensaje del usuario actual - a la derecha
                        div.className = 'mensaje usuario-1';
                    } else {
                        // Mensaje de otros usuarios - a la izquierda
                        div.className = 'mensaje usuario-2';
                    }
                    
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
        alert('Por favor, ingresa tu nombre y un mensaje');
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
            alert('Error al enviar mensaje: ' + data.error);
            return;
        }
        
        // Limpiar el campo de mensaje
        document.getElementById('mensaje').value = '';
        
        // Recargar mensajes inmediatamente
        cargarMensajes();
    })
    .catch(error => {
        console.error('Error de envío:', error);
        alert('Error de conexión. Verifica tu internet.');
    });
    
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Cargar nombre guardado del usuario
    const nombreGuardado = localStorage.getItem('chatUsuario');
    if (nombreGuardado) {
        document.getElementById('usuario').value = nombreGuardado;
    }
    
    // Guardar nombre cuando cambie
    document.getElementById('usuario').addEventListener('blur', function() {
        const nombre = this.value.trim();
        if (nombre) {
            localStorage.setItem('chatUsuario', nombre);
            // Recargar mensajes para actualizar posicionamiento
            cargarMensajes();
        }
    });
    
    cargarMensajes();
    setInterval(cargarMensajes, 3000);
    
    document.getElementById('mensaje').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje();
        }
    });
});