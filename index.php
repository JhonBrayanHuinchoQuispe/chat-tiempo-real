<?php
// ===== CONFIGURACIÓN DE BASE DE DATOS =====
function getDBConnection() {
    $host = $_ENV['DB_HOST'] ?? 'mysql-sistemasic.alwaysdata.net';
    $dbname = $_ENV['DB_NAME'] ?? 'sistemasic_chat';
    $username = $_ENV['DB_USER'] ?? '436286';
    $password = $_ENV['DB_PASS'] ?? 'brayan933783039';
    $port = $_ENV['DB_PORT'] ?? '3306';
    
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

// ===== ENDPOINT DE DIAGNÓSTICO =====
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    header('Content-Type: application/json');
    
    $diagnostico = [
        'timestamp' => date('Y-m-d H:i:s'),
        'environment_variables' => [
            'DB_HOST' => $_ENV['DB_HOST'] ?? 'NO_SET',
            'DB_NAME' => $_ENV['DB_NAME'] ?? 'NO_SET', 
            'DB_USER' => $_ENV['DB_USER'] ?? 'NO_SET',
            'DB_PASS' => $_ENV['DB_PASS'] ? 'SET' : 'NO_SET'
        ],
        'database_connection' => 'TESTING...'
    ];
    
    try {
        $pdo = getDBConnection();
        if ($pdo) {
            $diagnostico['database_connection'] = 'SUCCESS';
            
            // Verificar tabla mensajes
            $stmt = $pdo->query("SHOW TABLES LIKE 'mensajes'");
            $diagnostico['table_mensajes'] = $stmt->rowCount() > 0 ? 'EXISTS' : 'NOT_EXISTS';
            
            if ($diagnostico['table_mensajes'] === 'EXISTS') {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM mensajes");
                $result = $stmt->fetch();
                $diagnostico['messages_count'] = $result['count'];
            }
        } else {
            $diagnostico['database_connection'] = 'FAILED - PDO is null';
        }
    } catch (Exception $e) {
        $diagnostico['database_connection'] = 'ERROR: ' . $e->getMessage();
    }
    
    echo json_encode($diagnostico, JSON_PRETTY_PRINT);
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