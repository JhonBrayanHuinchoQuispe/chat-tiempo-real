<?php
// ===== DIAGNÓSTICO SIMPLE Y DIRECTO =====
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔍 Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .success { color: green; background: #d4edda; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .error { color: red; background: #f8d7da; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .info { color: blue; background: #d1ecf1; padding: 10px; margin: 5px 0; border-radius: 5px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DIAGNÓSTICO DEL SERVIDOR</h1>
        
        <div class="success">✅ <strong>PHP FUNCIONANDO</strong></div>
        <div class="info"><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></div>
        <div class="info"><strong>Versión PHP:</strong> <?= phpversion() ?></div>
        
        <h2>🌍 Variables de Entorno</h2>
        <pre><?php
        echo "PUSHER_APP_ID: " . (getenv('PUSHER_APP_ID') ?: 'NO DEFINIDA') . "\n";
        echo "PUSHER_KEY: " . (getenv('PUSHER_KEY') ?: 'NO DEFINIDA') . "\n";
        echo "PUSHER_SECRET: " . (getenv('PUSHER_SECRET') ?: 'NO DEFINIDA') . "\n";
        echo "PUSHER_CLUSTER: " . (getenv('PUSHER_CLUSTER') ?: 'NO DEFINIDA') . "\n";
        echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NO DEFINIDA') . "\n";
        echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NO DEFINIDA') . "\n";
        echo "DB_USER: " . (getenv('DB_USER') ?: 'NO DEFINIDA') . "\n";
        echo "DB_PASS: " . (getenv('DB_PASS') ? 'CONFIGURADA' : 'NO DEFINIDA') . "\n";
        ?></pre>
        
        <h2>🗄️ Test de Base de Datos</h2>
        <?php
        try {
            // Configuración hardcodeada
            $host = 'jhonbrayanhuinchoquispe.alwaysdata.net';
            $dbname = 'jhonbrayanhuinchoquispe_sistemasic_chat';
            $username = 'jhonbrayanhuinchoquispe';
            $password = 'brayan933783039';
            
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            echo '<div class="success">✅ <strong>CONEXIÓN BD EXITOSA</strong></div>';
            
            // Test de tabla
            $stmt = $pdo->query("SHOW TABLES LIKE 'mensajes'");
            if ($stmt->rowCount() > 0) {
                echo '<div class="success">✅ <strong>TABLA "mensajes" EXISTE</strong></div>';
                
                // Contar mensajes
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM mensajes");
                $count = $stmt->fetch()['total'];
                echo '<div class="info"><strong>Total mensajes:</strong> ' . $count . '</div>';
            } else {
                echo '<div class="error">❌ <strong>TABLA "mensajes" NO EXISTE</strong></div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ <strong>ERROR BD:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <h2>📊 Información del Servidor</h2>
        <pre><?php
        echo "Servidor: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "\n";
        echo "Memoria límite: " . ini_get('memory_limit') . "\n";
        echo "Tiempo máximo: " . ini_get('max_execution_time') . "s\n";
        echo "Zona horaria: " . date_default_timezone_get() . "\n";
        ?></pre>
        
        <div class="info">
            <strong>🔗 URLs de prueba:</strong><br>
            • <a href="status.php">status.php</a> (este archivo)<br>
            • <a href="index.php">index.php</a> (aplicación principal)<br>
            • <a href="index.php?test">index.php?test</a> (diagnóstico integrado)
        </div>
    </div>
</body>
</html>