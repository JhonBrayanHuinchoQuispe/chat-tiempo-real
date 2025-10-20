<?php
// ===== CONFIGURACIÓN COMPLETA HARDCODEADA =====

// Configuración de Pusher (hardcodeada para Render)
define('PUSHER_APP_ID', '2062974');
define('PUSHER_KEY', 'ed728998da5272b7373e');
define('PUSHER_SECRET', '49cbb5047f31a0b4f03c');
define('PUSHER_CLUSTER', 'sa1');

// ===== CONFIGURACIÓN DE BASE DE DATOS =====
function getDBConnection() {
    // CONFIGURACIÓN HARDCODEADA PARA RENDER
    $host = 'jhonbrayanhuinchoquispe.alwaysdata.net';  // HOST CORRECTO
    $dbname = 'jhonbrayanhuinchoquispe_sistemasic_chat';  // BD CORRECTA
    $username = 'jhonbrayanhuinchoquispe';  // USUARIO CORRECTO
    $password = 'brayan933783039';  // PASSWORD CORRECTO
    $port = '3306';
    
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::MYSQL_ATTR_SSL_CA => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión BD: " . $e->getMessage());
        return null;
    }
}

// ===== ENDPOINT DE DIAGNÓSTICO COMPLETO =====
if (isset($_GET['diagnostico']) || isset($_GET['debug'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>🔍 Diagnóstico Completo - Chat en Tiempo Real</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .success { color: #28a745; background: #d4edda; padding: 12px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #28a745; }
            .error { color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #dc3545; }
            .warning { color: #856404; background: #fff3cd; padding: 12px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #ffc107; }
            .info { color: #0c5460; background: #d1ecf1; padding: 12px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #17a2b8; }
            .section { margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6; }
            pre { background: #e9ecef; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 14px; }
            h1 { color: #333; text-align: center; margin-bottom: 30px; }
            h2 { color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 8px; margin-top: 0; }
            .timestamp { text-align: center; color: #6c757d; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔍 Diagnóstico Completo del Chat</h1>
            <div class="timestamp">
                <strong>Ejecutado:</strong> <?= date('Y-m-d H:i:s') ?> (Hora del servidor)
            </div>

            <!-- CONFIGURACIÓN HARDCODEADA -->
            <div class="section">
                <h2>⚙️ Configuración Hardcodeada</h2>
                <div class="success">✅ <strong>Pusher App ID:</strong> <?= PUSHER_APP_ID ?></div>
                <div class="success">✅ <strong>Pusher Key:</strong> <?= PUSHER_KEY ?></div>
                <div class="success">✅ <strong>Pusher Cluster:</strong> <?= PUSHER_CLUSTER ?></div>
                <div class="info"><strong>🔒 Pusher Secret:</strong> CONFIGURADO (****)</div>
            </div>

            <!-- CONFIGURACIÓN DE BASE DE DATOS -->
            <div class="section">
                <h2>🗄️ Configuración de Base de Datos</h2>
                <div class="success">✅ <strong>DB_HOST:</strong> jhonbrayanhuinchoquispe.alwaysdata.net</div>
                <div class="success">✅ <strong>DB_NAME:</strong> jhonbrayanhuinchoquispe_sistemasic_chat</div>
                <div class="success">✅ <strong>DB_USER:</strong> jhonbrayanhuinchoquispe</div>
                <div class="success">✅ <strong>DB_PORT:</strong> 3306</div>
                <div class="info"><strong>🔒 DB_PASS:</strong> CONFIGURADO (****)</div>
            </div>

            <!-- PRUEBA DE CONEXIÓN -->
            <div class="section">
                <h2>🔌 Prueba de Conexión a Base de Datos</h2>
                <?php
                try {
                    $pdo = getDBConnection();
                    if ($pdo) {
                        echo '<div class="success">✅ <strong>CONEXIÓN EXITOSA</strong> a MySQL</div>';
                        
                        // Verificar tabla mensajes
                        $stmt = $pdo->query("SHOW TABLES LIKE 'mensajes'");
                        if ($stmt->rowCount() > 0) {
                            echo '<div class="success">✅ Tabla "mensajes" existe</div>';
                            
                            // Contar mensajes
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM mensajes");
                            $result = $stmt->fetch();
                            echo '<div class="info">📊 <strong>Total de mensajes:</strong> ' . $result['count'] . '</div>';
                            
                            // Mostrar estructura de la tabla
                            $stmt = $pdo->query("DESCRIBE mensajes");
                            $columns = $stmt->fetchAll();
                            echo '<div class="info"><strong>📋 Estructura de la tabla:</strong><br><pre>';
                            foreach ($columns as $col) {
                                echo sprintf("%-15s %-20s %s\n", $col['Field'], $col['Type'], $col['Key'] ? "({$col['Key']})" : "");
                            }
                            echo '</pre></div>';
                            
                            // Últimos mensajes
                            $stmt = $pdo->query("SELECT usuario, mensaje, timestamp FROM mensajes ORDER BY timestamp DESC LIMIT 5");
                            $mensajes = $stmt->fetchAll();
                            if ($mensajes) {
                                echo '<div class="info"><strong>📝 Últimos 5 mensajes:</strong><br><pre>';
                                foreach ($mensajes as $msg) {
                                    echo htmlspecialchars($msg['timestamp'] . ' | ' . $msg['usuario'] . ': ' . substr($msg['mensaje'], 0, 50) . (strlen($msg['mensaje']) > 50 ? '...' : '')) . "\n";
                                }
                                echo '</pre></div>';
                            } else {
                                echo '<div class="warning">⚠️ No hay mensajes en la base de datos</div>';
                            }
                            
                            // Test de inserción
                            try {
                                $stmt = $pdo->prepare("INSERT INTO mensajes (usuario, mensaje) VALUES (?, ?)");
                                $test_user = "TEST_" . date('His');
                                $test_message = "Mensaje de prueba - " . date('Y-m-d H:i:s');
                                $stmt->execute([$test_user, $test_message]);
                                echo '<div class="success">✅ Test de inserción exitoso (ID: ' . $pdo->lastInsertId() . ')</div>';
                                
                                // Eliminar el mensaje de prueba
                                $stmt = $pdo->prepare("DELETE FROM mensajes WHERE usuario = ? AND mensaje = ?");
                                $stmt->execute([$test_user, $test_message]);
                                echo '<div class="info">🧹 Mensaje de prueba eliminado</div>';
                            } catch (Exception $e) {
                                echo '<div class="error">❌ Error en test de inserción: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            
                        } else {
                            echo '<div class="error">❌ Tabla "mensajes" NO existe</div>';
                            echo '<div class="warning">💡 <strong>Solución:</strong> Ejecutar el script database.sql en tu base de datos AlwaysData</div>';
                        }
                    } else {
                        echo '<div class="error">❌ Error: No se pudo conectar a la base de datos</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="error">❌ Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    
                    // Análisis del error
                    $error = $e->getMessage();
                    echo '<div class="warning"><strong>🔍 Análisis del Error:</strong><br>';
                    if (strpos($error, 'Access denied') !== false) {
                        echo '• <strong>Credenciales incorrectas:</strong> Usuario o contraseña inválidos<br>';
                        echo '• <strong>Solución:</strong> Verificar credenciales en AlwaysData';
                    } elseif (strpos($error, 'Unknown database') !== false) {
                        echo '• <strong>Base de datos no existe:</strong> El nombre de la BD es incorrecto<br>';
                        echo '• <strong>Solución:</strong> Crear la base de datos en AlwaysData';
                    } elseif (strpos($error, 'Connection refused') !== false || strpos($error, 'timed out') !== false) {
                        echo '• <strong>Servidor no accesible:</strong> Host o puerto incorrectos<br>';
                        echo '• <strong>Solución:</strong> Verificar host en AlwaysData';
                    } else {
                        echo '• <strong>Error desconocido:</strong> ' . htmlspecialchars($error);
                    }
                    echo '</div>';
                }
                ?>
            </div>

            <!-- INFORMACIÓN DEL SERVIDOR -->
            <div class="section">
                <h2>🖥️ Información del Servidor</h2>
                <div class="info">
                    <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
                    <strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible' ?><br>
                    <strong>Timezone:</strong> <?= date_default_timezone_get() ?><br>
                    <strong>Memory Limit:</strong> <?= ini_get('memory_limit') ?><br>
                    <strong>Max Execution Time:</strong> <?= ini_get('max_execution_time') ?>s
                </div>
            </div>

            <!-- PRÓXIMOS PASOS -->
            <div class="section">
                <h2>🎯 Próximos Pasos</h2>
                <div class="info">
                    <strong>Si todo está bien:</strong><br>
                    1. <a href="/" style="color: #007bff; text-decoration: none;">🚀 Ir al Chat</a><br>
                    2. Probar envío y recepción de mensajes<br><br>
                    
                    <strong>Si hay errores:</strong><br>
                    1. Verificar credenciales en AlwaysData<br>
                    2. Asegurar que la base de datos existe<br>
                    3. Ejecutar database.sql si es necesario
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ===== API ENDPOINTS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
    
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');
    
    if ($action === 'leer' || $_SERVER['REQUEST_METHOD'] === 'GET') {
        // Leer mensajes
        $pdo = getDBConnection();
        if (!$pdo) {
            echo json_encode(['error' => 'Error de conexión a la base de datos']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, usuario, mensaje as texto, timestamp FROM mensajes ORDER BY timestamp DESC LIMIT 50");
            $stmt->execute();
            $mensajes = $stmt->fetchAll();
            
            foreach ($mensajes as &$mensaje) {
                $mensaje['timestamp'] = date('Y-m-d H:i:s', strtotime($mensaje['timestamp']));
            }
            
            echo json_encode(['mensajes' => array_reverse($mensajes)]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al leer mensajes: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'enviar' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Enviar mensaje
        $input = json_decode(file_get_contents('php://input'), true);
        $usuario = $input['usuario'] ?? '';
        $texto = $input['texto'] ?? '';
        
        if (empty($usuario) || empty($texto)) {
            echo json_encode(['error' => 'Usuario y mensaje son requeridos']);
            exit;
        }
        
        $pdo = getDBConnection();
        if (!$pdo) {
            echo json_encode(['error' => 'Error de conexión a la base de datos']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO mensajes (usuario, mensaje, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([
                $usuario, 
                $texto, 
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            if ($result) {
                $id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT id, usuario, mensaje as texto, timestamp FROM mensajes WHERE id = ?");
                $stmt->execute([$id]);
                $mensaje = $stmt->fetch();
                $mensaje['timestamp'] = date('Y-m-d H:i:s', strtotime($mensaje['timestamp']));
                
                echo json_encode(['success' => true, 'mensaje' => $mensaje]);
            } else {
                echo json_encode(['error' => 'Error al guardar el mensaje']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al guardar mensaje: ' . $e->getMessage()]);
        }
        exit;
    }
}

// ===== INTERFAZ HTML =====
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat en Tiempo Real</title>
    <link rel="icon" href="static/favicon.ico" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .contenedor-chat {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
            height: 600px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .encabezado-chat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .encabezado-chat h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .area-mensajes {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .mensaje {
            background: white;
            margin-bottom: 15px;
            padding: 12px 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mensaje-usuario {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }

        .mensaje-texto {
            color: #333;
            line-height: 1.4;
        }

        .mensaje-tiempo {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }

        .area-entrada {
            padding: 20px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .area-entrada input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #eee;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .area-entrada input:focus {
            border-color: #667eea;
        }

        .area-entrada button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .area-entrada button:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #ff6b6b;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="contenedor-chat">
        <div class="encabezado-chat">
            <h1>Chat en Tiempo Real</h1>
        </div>
        
        <div class="area-mensajes" id="mensajes">
        </div>
        
        <div class="area-entrada">
            <input type="text" id="usuario" placeholder="Tu nombre" maxlength="50">
            <input type="text" id="texto" placeholder="Escribe un mensaje" maxlength="500">
            <button id="enviar">Enviar</button>
        </div>
    </div>

    <script>
        let mensajes = [];
        
        function cargarMensajes() {
            fetch('?action=leer')
                .then(response => response.json())
                .then(data => {
                    if (data.mensajes) {
                        mensajes = data.mensajes;
                        mostrarMensajes();
                    } else if (data.error) {
                        mostrarError(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarError('Error al cargar mensajes');
                });
        }
        
        function mostrarMensajes() {
            const contenedor = document.getElementById('mensajes');
            contenedor.innerHTML = '';
            
            mensajes.forEach(mensaje => {
                const div = document.createElement('div');
                div.className = 'mensaje';
                div.innerHTML = `
                    <div class="mensaje-usuario">${escapeHtml(mensaje.usuario)}</div>
                    <div class="mensaje-texto">${escapeHtml(mensaje.texto)}</div>
                    <div class="mensaje-tiempo">${mensaje.timestamp}</div>
                `;
                contenedor.appendChild(div);
            });
            
            contenedor.scrollTop = contenedor.scrollHeight;
        }
        
        function enviarMensaje() {
            const usuario = document.getElementById('usuario').value.trim();
            const texto = document.getElementById('texto').value.trim();
            
            if (!usuario || !texto) {
                mostrarError('Por favor, completa todos los campos');
                return;
            }
            
            fetch('?action=enviar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ usuario, texto })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('texto').value = '';
                    cargarMensajes();
                } else {
                    mostrarError(data.error || 'Error al enviar mensaje');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Error al enviar mensaje');
            });
        }
        
        function mostrarError(mensaje) {
            const contenedor = document.getElementById('mensajes');
            const div = document.createElement('div');
            div.className = 'error';
            div.textContent = mensaje;
            contenedor.appendChild(div);
            contenedor.scrollTop = contenedor.scrollHeight;
            
            setTimeout(() => {
                div.remove();
            }, 5000);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Event listeners
        document.getElementById('enviar').addEventListener('click', enviarMensaje);
        
        document.getElementById('texto').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                enviarMensaje();
            }
        });
        
        // Cargar mensajes al inicio
        cargarMensajes();
        
        // Actualizar mensajes cada 3 segundos
        setInterval(cargarMensajes, 3000);
    </script>
</body>
</html>