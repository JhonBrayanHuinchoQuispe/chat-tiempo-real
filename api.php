<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Endpoint temporal de diagnóstico
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
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

function leerMensajes() {
    $pdo = getDBConnection();
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, usuario, mensaje as texto, timestamp FROM mensajes ORDER BY timestamp DESC LIMIT 50");
        $stmt->execute();
        $mensajes = $stmt->fetchAll();
        
        // Convertir timestamp a formato string y revertir orden
        foreach ($mensajes as &$mensaje) {
            $mensaje['timestamp'] = date('Y-m-d H:i:s', strtotime($mensaje['timestamp']));
        }
        
        return array_reverse($mensajes);
    } catch (PDOException $e) {
        error_log("Error al leer mensajes: " . $e->getMessage());
        return [];
    }
}

function guardarMensaje($usuario, $texto, $ip = null, $userAgent = null) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO mensajes (usuario, mensaje, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$usuario, $texto, $ip, $userAgent]);
        
        if ($result) {
            // Obtener el mensaje recién insertado
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT id, usuario, mensaje as texto, timestamp FROM mensajes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Error al guardar mensaje: " . $e->getMessage());
        return false;
    }
}

// Obtener mensajes
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mensajes = leerMensajes();
    echo json_encode(['mensajes' => $mensajes]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['usuario']) || !isset($data['texto'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$usuario = trim($data['usuario']);
$texto = trim($data['texto']);

if (empty($usuario) || empty($texto)) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario y texto son requeridos']);
    exit;
}

if (strlen($usuario) > 50 || strlen($texto) > 500) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario o texto demasiado largo']);
    exit;
}

// Validación básica de seguridad
if (preg_match('/<script|javascript:|on\w+=/i', $usuario . $texto)) {
    http_response_code(400);
    echo json_encode(['error' => 'Contenido no permitido']);
    exit;
}

// Rate limiting básico
session_start();
$ahora = time();
if (isset($_SESSION['ultimo_mensaje']) && ($ahora - $_SESSION['ultimo_mensaje']) < 1) {
    http_response_code(429);
    echo json_encode(['error' => 'Demasiados mensajes. Espera un momento.']);
    exit;
}
$_SESSION['ultimo_mensaje'] = $ahora;

// Obtener información del cliente
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

// Limpiar datos
$usuario_limpio = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');
$texto_limpio = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

// Guardar mensaje en la base de datos
$mensaje_guardado = guardarMensaje($usuario_limpio, $texto_limpio, $ip_address, $user_agent);

if (!$mensaje_guardado) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar el mensaje']);
    exit;
}

// Formatear mensaje para respuesta
$mensaje = [
    'id' => $mensaje_guardado['id'],
    'usuario' => $mensaje_guardado['usuario'],
    'texto' => $mensaje_guardado['texto'],
    'timestamp' => date('Y-m-d H:i:s', strtotime($mensaje_guardado['timestamp']))
];

// Enviar a Pusher si está configurado
$pusher_configurado = defined('PUSHER_APP_ID') && defined('PUSHER_KEY') && defined('PUSHER_SECRET') && 
                     PUSHER_APP_ID !== 'TU_APP_ID' && PUSHER_KEY !== 'TU_PUSHER_KEY';

if ($pusher_configurado) {
    $pusher_url = "https://api-" . PUSHER_CLUSTER . ".pusher.com/apps/" . PUSHER_APP_ID . "/events";
    
    $pusher_data = [
        'name' => 'nuevo_mensaje',
        'channel' => 'chat',
        'data' => json_encode($mensaje)
    ];
    
    $auth_timestamp = time();
    $auth_version = '1.0';
    $body = json_encode($pusher_data);
    $body_md5 = md5($body);
    
    $auth_params = [
        'auth_key' => PUSHER_KEY,
        'auth_timestamp' => $auth_timestamp,
        'auth_version' => $auth_version,
        'body_md5' => $body_md5
    ];
    
    ksort($auth_params);
    $query_string = http_build_query($auth_params);
    $string_to_sign = "POST\n/apps/" . PUSHER_APP_ID . "/events\n" . $query_string;
    $auth_signature = hash_hmac('sha256', $string_to_sign, PUSHER_SECRET);
    
    $final_url = $pusher_url . "?" . $query_string . "&auth_signature=" . $auth_signature;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($body)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    error_log("Respuesta Pusher - HTTP: " . $http_code . " - Contenido: " . $response);
    
    if ($curl_error) {
        error_log("Error cURL: " . $curl_error);
    }
}

echo json_encode(['success' => true, 'mensaje' => $mensaje]);
?>