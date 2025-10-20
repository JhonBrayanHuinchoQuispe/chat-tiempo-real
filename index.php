<?php
// Actualización forzada para Render - 2025-10-19 23:45:00
header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set('America/Lima');

function getDB() {
    try {
        $pdo = new PDO(
            'mysql:host=mysql-sistemasic.alwaysdata.net;dbname=sistemasic_chat',
            '436286',
            'brayan933783039',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $pdo->exec("SET time_zone = '-05:00'");
        return $pdo;
    } catch (Exception $e) {
        error_log("Error de conexión DB: " . $e->getMessage());
        return null;
    }
}

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
    <link rel="stylesheet" href="static/css/style.css?v=20251019234500">
    <link rel="icon" href="static/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="contenedor-chat">
        <div class="encabezado">
             <h1>Chat en Tiempo Real</h1>
         </div>
        
        <div id="mensajes" class="area-mensajes">
        </div>
        
        <div class="area-entrada">
            <form onsubmit="return enviarMensaje(event);">
                <div class="fila-entrada">
                    <input type="text" id="usuario" placeholder="Tu nombre" required>
                    <textarea id="mensaje" placeholder="Escribe un mensaje..." required></textarea>
                    <button type="submit" class="btn btn-primario">Enviar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="static/js/chat.js"></script>
</body>
</html>