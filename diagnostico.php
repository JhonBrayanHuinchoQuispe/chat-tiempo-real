<?php
// ===== DIAGNÓSTICO COMPLETO DE BASE DE DATOS =====
header('Content-Type: text/html; charset=utf-8');

// Función de conexión
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
        return ['error' => $e->getMessage()];
    }
}
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
        .var-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #dee2e6; }
        .var-name { font-weight: bold; }
        .var-value { font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Completo del Chat</h1>
        <div class="timestamp">
            <strong>Ejecutado:</strong> <?= date('Y-m-d H:i:s') ?> (Hora del servidor)
        </div>

        <!-- VARIABLES DE ENTORNO -->
        <div class="section">
            <h2>📋 Variables de Entorno</h2>
            <?php
            $env_vars = [
                'DB_HOST' => $_ENV['DB_HOST'] ?? 'NO_CONFIGURADO',
                'DB_NAME' => $_ENV['DB_NAME'] ?? 'NO_CONFIGURADO',
                'DB_USER' => $_ENV['DB_USER'] ?? 'NO_CONFIGURADO',
                'DB_PASS' => isset($_ENV['DB_PASS']) ? 'CONFIGURADO (****)' : 'NO_CONFIGURADO',
                'DB_PORT' => $_ENV['DB_PORT'] ?? 'NO_CONFIGURADO'
            ];
            
            foreach ($env_vars as $key => $value) {
                $class = (strpos($value, 'NO_CONFIGURADO') !== false) ? 'error' : 'success';
                echo "<div class=\"var-item\">";
                echo "<span class=\"var-name\">$key:</span>";
                echo "<span class=\"var-value $class\">$value</span>";
                echo "</div>";
            }
            ?>
        </div>

        <!-- EXTENSIONES PHP -->
        <div class="section">
            <h2>🔧 Extensiones PHP</h2>
            <?php
            $extensions = ['pdo', 'pdo_mysql', 'mysqli', 'json'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                $class = $loaded ? 'success' : 'error';
                $status = $loaded ? '✅ CARGADA' : '❌ NO CARGADA';
                echo "<div class=\"$class\"><strong>$ext:</strong> $status</div>";
            }
            ?>
        </div>

        <!-- CONEXIÓN A BASE DE DATOS -->
        <div class="section">
            <h2>🗄️ Prueba de Conexión a Base de Datos</h2>
            <?php
            $connection = getDBConnection();
            
            if (is_array($connection) && isset($connection['error'])) {
                echo "<div class=\"error\">";
                echo "<strong>❌ ERROR DE CONEXIÓN:</strong><br>";
                echo htmlspecialchars($connection['error']);
                echo "</div>";
                
                // Análisis del error
                $error = $connection['error'];
                echo "<div class=\"warning\">";
                echo "<strong>🔍 Análisis del Error:</strong><br>";
                if (strpos($error, 'Access denied') !== false) {
                    echo "• <strong>Credenciales incorrectas:</strong> Usuario o contraseña inválidos<br>";
                    echo "• <strong>Solución:</strong> Verificar DB_USER y DB_PASS en Render";
                } elseif (strpos($error, 'Unknown database') !== false) {
                    echo "• <strong>Base de datos no existe:</strong> El nombre de la BD es incorrecto<br>";
                    echo "• <strong>Solución:</strong> Verificar DB_NAME en Render";
                } elseif (strpos($error, 'Connection refused') !== false || strpos($error, 'timed out') !== false) {
                    echo "• <strong>Servidor no accesible:</strong> Host o puerto incorrectos<br>";
                    echo "• <strong>Solución:</strong> Verificar DB_HOST y DB_PORT en Render";
                } else {
                    echo "• <strong>Error desconocido:</strong> " . htmlspecialchars($error);
                }
                echo "</div>";
            } else {
                echo "<div class=\"success\">✅ <strong>CONEXIÓN EXITOSA</strong> a MySQL</div>";
                
                try {
                    // Verificar tabla mensajes
                    $stmt = $connection->query("SHOW TABLES LIKE 'mensajes'");
                    if ($stmt->rowCount() > 0) {
                        echo "<div class=\"success\">✅ Tabla 'mensajes' existe</div>";
                        
                        // Contar mensajes
                        $stmt = $connection->query("SELECT COUNT(*) as count FROM mensajes");
                        $result = $stmt->fetch();
                        echo "<div class=\"info\">📊 <strong>Total de mensajes:</strong> " . $result['count'] . "</div>";
                        
                        // Mostrar estructura de la tabla
                        $stmt = $connection->query("DESCRIBE mensajes");
                        $columns = $stmt->fetchAll();
                        echo "<div class=\"info\">";
                        echo "<strong>📋 Estructura de la tabla:</strong><br>";
                        echo "<pre>";
                        foreach ($columns as $col) {
                            echo sprintf("%-15s %-20s %s\n", $col['Field'], $col['Type'], $col['Key'] ? "({$col['Key']})" : "");
                        }
                        echo "</pre>";
                        echo "</div>";
                        
                        // Últimos mensajes
                        $stmt = $connection->query("SELECT usuario, mensaje, timestamp FROM mensajes ORDER BY timestamp DESC LIMIT 5");
                        $mensajes = $stmt->fetchAll();
                        if ($mensajes) {
                            echo "<div class=\"info\">";
                            echo "<strong>📝 Últimos 5 mensajes:</strong><br>";
                            echo "<pre>";
                            foreach ($mensajes as $msg) {
                                echo htmlspecialchars($msg['timestamp'] . ' | ' . $msg['usuario'] . ': ' . substr($msg['mensaje'], 0, 50) . (strlen($msg['mensaje']) > 50 ? '...' : '')) . "\n";
                            }
                            echo "</pre>";
                            echo "</div>";
                        } else {
                            echo "<div class=\"warning\">⚠️ No hay mensajes en la base de datos</div>";
                        }
                        
                        // Test de inserción
                        try {
                            $stmt = $connection->prepare("INSERT INTO mensajes (usuario, mensaje) VALUES (?, ?)");
                            $test_user = "TEST_" . date('His');
                            $test_message = "Mensaje de prueba - " . date('Y-m-d H:i:s');
                            $stmt->execute([$test_user, $test_message]);
                            echo "<div class=\"success\">✅ Test de inserción exitoso (ID: " . $connection->lastInsertId() . ")</div>";
                            
                            // Eliminar el mensaje de prueba
                            $stmt = $connection->prepare("DELETE FROM mensajes WHERE usuario = ? AND mensaje = ?");
                            $stmt->execute([$test_user, $test_message]);
                            echo "<div class=\"info\">🧹 Mensaje de prueba eliminado</div>";
                        } catch (Exception $e) {
                            echo "<div class=\"error\">❌ Error en test de inserción: " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                        
                    } else {
                        echo "<div class=\"error\">❌ Tabla 'mensajes' NO existe</div>";
                        echo "<div class=\"warning\">";
                        echo "<strong>💡 Solución:</strong> Ejecutar el script database.sql en tu base de datos AlwaysData<br>";
                        echo "El script está en tu repositorio: database.sql";
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class=\"error\">❌ Error al verificar tabla: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
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

        <!-- RECOMENDACIONES -->
        <div class="section">
            <h2>💡 Próximos Pasos</h2>
            <div class="info">
                <strong>Si hay errores de conexión:</strong><br>
                1. Verificar variables de entorno en Render<br>
                2. Confirmar credenciales en AlwaysData<br>
                3. Asegurar que la tabla 'mensajes' existe<br><br>
                
                <strong>Si todo está bien:</strong><br>
                1. Probar el chat en: <a href="/">Ir al Chat</a><br>
                2. Verificar que los mensajes se guarden correctamente
            </div>
        </div>
    </div>
</body>
</html>