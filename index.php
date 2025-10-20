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
        <div class="encabezado">
            <h1>Chat en Tiempo Real</h1>
        </div>
        
        <div id="mensajes" class="area-mensajes">
            <!-- Los mensajes aparecerán aquí -->
        </div>
        
        <div class="area-entrada">
            <form onsubmit="return enviarMensaje(event);">
                <div class="grupo-entrada">
                    <input type="text" id="usuario" placeholder="Tu nombre" required>
                </div>
                <div class="grupo-entrada">
                    <textarea id="mensaje" placeholder="Escribe un mensaje" required></textarea>
                </div>
                <div class="botones">
                    <button type="submit" class="btn btn-primario">Enviar</button>
                    <button type="button" class="btn btn-secundario" onclick="cargarMensajes()">Actualizar</button>
                </div>
            </form>
            <div id="status" class="status"></div>
        </div>
    </div>

    <script>
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
                            div.innerHTML = `
                                <div class="usuario">${msg.usuario}</div>
                                <div class="contenido-mensaje">${msg.mensaje}</div>
                                <div class="timestamp">${msg.timestamp}</div>
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

        // Cargar mensajes al inicio
        document.addEventListener('DOMContentLoaded', function() {
            cargarMensajes();
            
            // Auto-actualizar cada 5 segundos (más frecuente)
            setInterval(cargarMensajes, 5000);
        });
    </script>
</body>
</html>