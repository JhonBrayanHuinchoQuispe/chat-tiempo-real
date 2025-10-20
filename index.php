<?php
// ===== CHAT MODERNO Y BONITO ===== 
// Versión 3.0 - DISEÑO COMPLETAMENTE NUEVO
// ACTUALIZACIÓN: CSS Moderno con gradientes y animaciones
// FORZAR REDESPLIEGUE COMPLETO
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
    <title>Chat en Tiempo Real</title>
    <link rel="stylesheet" href="static/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="static/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="contenedor-chat">
        <div class="cabecera">
            <h1>Chat en Tiempo Real</h1>
        </div>
        
        <div class="area-mensajes" id="mensajes">
            <!-- Los mensajes se cargarán aquí -->
        </div>
        
        <div class="area-entrada">
            <form class="formulario-mensaje" onsubmit="enviarMensaje(event)">
                <div class="campos-entrada">
                    <input type="text" id="usuario" name="usuario" placeholder="Tu nombre" required>
                    <textarea id="mensaje" name="mensaje" placeholder="Escribe un mensaje..." required></textarea>
                </div>
                <button id="enviar" type="submit">Enviar</button>
                <button id="actualizar" type="button" onclick="cargarMensajes()">Actualizar Chat</button>
            </form>
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