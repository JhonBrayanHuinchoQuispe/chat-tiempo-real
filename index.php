<?php
// ===== CHAT SIMPLE SIN PUSHER ===== 
// Versión 2.0 - Forzar redespliegue
// ACTUALIZACIÓN: Sincronización GitHub - Sin Pusher
header('Content-Type: text/html; charset=utf-8');

// Configuración de base de datos
function getDB() {
    try {
        $pdo = new PDO(
            'mysql:host=mysql-sistemasic.alwaysdata.net;dbname=sistemasic_chat',
            '436286',
            'brayan933783039',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (Exception $e) {
        error_log("Error de conexión DB: " . $e->getMessage());
        return null;
    }
}

// API para enviar mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    header('Content-Type: application/json');
    
    try {
        $usuario = trim($_POST['usuario'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');
        
        if (empty($usuario) || empty($mensaje)) {
            echo json_encode(['error' => 'Usuario y mensaje son requeridos']);
            exit;
        }
        
        $pdo = getDB();
        if (!$pdo) {
            echo json_encode(['error' => 'Error de conexión a BD']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO mensajes (usuario, mensaje) VALUES (?, ?)");
        $stmt->execute([$usuario, $mensaje]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}

// API para obtener mensajes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'messages') {
    header('Content-Type: application/json');
    
    try {
        $pdo = getDB();
        if (!$pdo) {
            echo json_encode(['error' => 'Error de conexión a BD']);
            exit;
        }
        
        $stmt = $pdo->query("SELECT usuario, mensaje, timestamp FROM mensajes ORDER BY timestamp DESC LIMIT 50");
        $mensajes = $stmt->fetchAll();
        
        echo json_encode(['messages' => array_reverse($mensajes)]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Simple</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #4267B2; color: white; padding: 20px; text-align: center; }
        .messages { height: 400px; overflow-y: auto; padding: 20px; border-bottom: 1px solid #eee; }
        .message { margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; }
        .message strong { color: #4267B2; }
        .form { padding: 20px; }
        .form input, .form textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .form button { width: 100%; padding: 12px; background: #4267B2; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .form button:hover { background: #365899; }
        .status { padding: 10px; text-align: center; font-size: 14px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Chat Simple</h1>
            <p>Sin tiempo real - Actualización manual</p>
        </div>
        
        <div class="messages" id="messages">
            <div class="message">
                <strong>Sistema:</strong> Cargando mensajes...
            </div>
        </div>
        
        <div class="form">
            <input type="text" id="usuario" placeholder="Tu nombre" required>
            <textarea id="mensaje" placeholder="Escribe tu mensaje..." rows="3" required></textarea>
            <button onclick="enviarMensaje()">Enviar Mensaje</button>
            <button onclick="cargarMensajes()" style="background: #42b883; margin-top: 10px;">Actualizar Chat</button>
        </div>
        
        <div class="status" id="status"></div>
    </div>

    <script>
        function mostrarStatus(mensaje, tipo = 'info') {
            const status = document.getElementById('status');
            status.textContent = mensaje;
            status.className = 'status ' + tipo;
            setTimeout(() => status.textContent = '', 3000);
        }

        function cargarMensajes() {
            fetch('?action=messages')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        mostrarStatus('Error: ' + data.error, 'error');
                        return;
                    }
                    
                    const container = document.getElementById('messages');
                    container.innerHTML = '';
                    
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            const div = document.createElement('div');
                            div.className = 'message';
                            div.innerHTML = `<strong>${msg.usuario}:</strong> ${msg.mensaje} <small>(${msg.timestamp})</small>`;
                            container.appendChild(div);
                        });
                        container.scrollTop = container.scrollHeight;
                    } else {
                        container.innerHTML = '<div class="message"><strong>Sistema:</strong> No hay mensajes aún</div>';
                    }
                    
                    mostrarStatus('Mensajes actualizados', 'success');
                })
                .catch(error => {
                    mostrarStatus('Error de conexión: ' + error.message, 'error');
                });
        }

        function enviarMensaje() {
            const usuario = document.getElementById('usuario').value.trim();
            const mensaje = document.getElementById('mensaje').value.trim();
            
            if (!usuario || !mensaje) {
                mostrarStatus('Por favor completa todos los campos', 'error');
                return;
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
                    mostrarStatus('Error: ' + data.error, 'error');
                    return;
                }
                
                document.getElementById('mensaje').value = '';
                mostrarStatus('Mensaje enviado', 'success');
                setTimeout(cargarMensajes, 500);
            })
            .catch(error => {
                mostrarStatus('Error de envío: ' + error.message, 'error');
            });
        }

        // Cargar mensajes al inicio
        cargarMensajes();
        
        // Auto-actualizar cada 10 segundos
        setInterval(cargarMensajes, 10000);
    </script>
</body>
</html>