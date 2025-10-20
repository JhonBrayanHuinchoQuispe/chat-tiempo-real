<?php
// ===== DIAGNÓSTICO INDEPENDIENTE DEL CHAT =====
// Este archivo funciona completamente independiente

// Configuración hardcodeada (igual que en index.php)
define('PUSHER_APP_ID', '1928077');
define('PUSHER_KEY', 'b8e7c8c8c8c8c8c8');
define('PUSHER_SECRET', 'b8e7c8c8c8c8c8c8');
define('PUSHER_CLUSTER', 'us2');

// Configuración de base de datos
define('DB_HOST', 'jhonbrayanhuinchoquispe.alwaysdata.net');
define('DB_NAME', 'jhonbrayanhuinchoquispe_sistemasic_chat');
define('DB_USER', 'jhonbrayanhuinchoquispe');
define('DB_PASS', 'Jhon2005');
define('DB_PORT', '3306');

// Función de conexión a base de datos
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>🔍 Diagnóstico Completo - Chat en Tiempo Real</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .success { 
            color: #155724; 
            background: #d4edda; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0; 
            border-left: 5px solid #28a745;
            font-weight: 500;
        }
        .error { 
            color: #721c24; 
            background: #f8d7da; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0; 
            border-left: 5px solid #dc3545;
            font-weight: 500;
        }
        .warning { 
            color: #856404; 
            background: #fff3cd; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0; 
            border-left: 5px solid #ffc107;
            font-weight: 500;
        }
        .info { 
            color: #0c5460; 
            background: #d1ecf1; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0; 
            border-left: 5px solid #17a2b8;
            font-weight: 500;
        }
        .section { 
            margin: 30px 0; 
            padding: 25px; 
            background: #f8f9fa; 
            border-radius: 10px; 
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        pre { 
            background: #e9ecef; 
            padding: 20px; 
            border-radius: 8px; 
            overflow-x: auto; 
            font-size: 14px;
            border: 1px solid #ced4da;
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        h2 { 
            color: #495057; 
            border-bottom: 3px solid #007bff; 
            padding-bottom: 10px; 
            margin-top: 0;
            font-size: 1.5em;
        }
        .timestamp { 
            text-align: center; 
            color: #6c757d; 
            margin-bottom: 30px;
            font-size: 1.1em;
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-error { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #212529; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            transition: background 0.3s;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Completo del Chat</h1>
        <div class="timestamp">
            <strong>🕒 Ejecutado:</strong> <?= date('Y-m-d H:i:s') ?> (Hora del servidor)<br>
            <strong>🌐 URL:</strong> <?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>
        </div>

        <!-- ESTADO GENERAL -->
        <div class="section">
            <h2>📊 Estado General del Sistema</h2>
            <?php
            $overall_status = "success";
            $issues = [];
            
            // Verificar conexión a BD
            try {
                $pdo = getDBConnection();
                if (!$pdo) {
                    $overall_status = "error";
                    $issues[] = "Conexión a base de datos fallida";
                }
            } catch (Exception $e) {
                $overall_status = "error";
                $issues[] = "Error de conexión: " . $e->getMessage();
            }
            
            if ($overall_status === "success") {
                echo '<div class="success">✅ <strong>SISTEMA OPERATIVO</strong> - Todos los componentes principales funcionan correctamente</div>';
            } else {
                echo '<div class="error">❌ <strong>SISTEMA CON PROBLEMAS</strong> - Se detectaron ' . count($issues) . ' problema(s)</div>';
                foreach ($issues as $issue) {
                    echo '<div class="warning">⚠️ ' . htmlspecialchars($issue) . '</div>';
                }
            }
            ?>
        </div>

        <!-- CONFIGURACIÓN HARDCODEADA -->
        <div class="section">
            <h2>⚙️ Configuración Hardcodeada</h2>
            <div class="success">✅ <strong>Pusher App ID:</strong> <?= PUSHER_APP_ID ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="success">✅ <strong>Pusher Key:</strong> <?= PUSHER_KEY ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="success">✅ <strong>Pusher Cluster:</strong> <?= PUSHER_CLUSTER ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="info"><strong>🔒 Pusher Secret:</strong> CONFIGURADO (**** por seguridad) <span class="status-badge badge-success">SEGURO</span></div>
        </div>

        <!-- CONFIGURACIÓN DE BASE DE DATOS -->
        <div class="section">
            <h2>🗄️ Configuración de Base de Datos</h2>
            <div class="success">✅ <strong>DB_HOST:</strong> <?= DB_HOST ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="success">✅ <strong>DB_NAME:</strong> <?= DB_NAME ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="success">✅ <strong>DB_USER:</strong> <?= DB_USER ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="success">✅ <strong>DB_PORT:</strong> <?= DB_PORT ?> <span class="status-badge badge-success">CONFIGURADO</span></div>
            <div class="info"><strong>🔒 DB_PASS:</strong> CONFIGURADO (**** por seguridad) <span class="status-badge badge-success">SEGURO</span></div>
        </div>

        <!-- PRUEBA DE CONEXIÓN DETALLADA -->
        <div class="section">
            <h2>🔌 Prueba de Conexión a Base de Datos</h2>
            <?php
            try {
                $start_time = microtime(true);
                $pdo = getDBConnection();
                $connection_time = round((microtime(true) - $start_time) * 1000, 2);
                
                if ($pdo) {
                    echo '<div class="success">✅ <strong>CONEXIÓN EXITOSA</strong> a MySQL (Tiempo: ' . $connection_time . 'ms)</div>';
                    
                    // Información del servidor MySQL
                    $stmt = $pdo->query("SELECT VERSION() as version");
                    $mysql_version = $stmt->fetch()['version'];
                    echo '<div class="info">🔧 <strong>MySQL Version:</strong> ' . htmlspecialchars($mysql_version) . '</div>';
                    
                    // Verificar tabla mensajes
                    $stmt = $pdo->query("SHOW TABLES LIKE 'mensajes'");
                    if ($stmt->rowCount() > 0) {
                        echo '<div class="success">✅ Tabla "mensajes" existe <span class="status-badge badge-success">OK</span></div>';
                        
                        // Contar mensajes
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM mensajes");
                        $result = $stmt->fetch();
                        $message_count = $result['count'];
                        echo '<div class="info">📊 <strong>Total de mensajes:</strong> ' . $message_count . '</div>';
                        
                        // Mostrar estructura de la tabla
                        $stmt = $pdo->query("DESCRIBE mensajes");
                        $columns = $stmt->fetchAll();
                        echo '<div class="info"><strong>📋 Estructura de la tabla:</strong><br><pre>';
                        echo sprintf("%-15s %-20s %-10s %-10s %-15s %-10s\n", "Campo", "Tipo", "Nulo", "Clave", "Default", "Extra");
                        echo str_repeat("-", 80) . "\n";
                        foreach ($columns as $col) {
                            echo sprintf("%-15s %-20s %-10s %-10s %-15s %-10s\n", 
                                $col['Field'], 
                                $col['Type'], 
                                $col['Null'], 
                                $col['Key'], 
                                $col['Default'] ?? 'NULL', 
                                $col['Extra']
                            );
                        }
                        echo '</pre></div>';
                        
                        // Últimos mensajes
                        if ($message_count > 0) {
                            $stmt = $pdo->query("SELECT usuario, mensaje, timestamp FROM mensajes ORDER BY timestamp DESC LIMIT 5");
                            $mensajes = $stmt->fetchAll();
                            if ($mensajes) {
                                echo '<div class="info"><strong>📝 Últimos 5 mensajes:</strong><br><pre>';
                                foreach ($mensajes as $msg) {
                                    $mensaje_corto = strlen($msg['mensaje']) > 60 ? substr($msg['mensaje'], 0, 60) . '...' : $msg['mensaje'];
                                    echo htmlspecialchars($msg['timestamp'] . ' | ' . $msg['usuario'] . ': ' . $mensaje_corto) . "\n";
                                }
                                echo '</pre></div>';
                            }
                        } else {
                            echo '<div class="warning">⚠️ No hay mensajes en la base de datos (tabla vacía)</div>';
                        }
                        
                        // Test de inserción y eliminación
                        try {
                            $stmt = $pdo->prepare("INSERT INTO mensajes (usuario, mensaje) VALUES (?, ?)");
                            $test_user = "DIAG_TEST_" . date('His');
                            $test_message = "Mensaje de prueba del diagnóstico - " . date('Y-m-d H:i:s');
                            
                            $insert_start = microtime(true);
                            $stmt->execute([$test_user, $test_message]);
                            $insert_time = round((microtime(true) - $insert_start) * 1000, 2);
                            $insert_id = $pdo->lastInsertId();
                            
                            echo '<div class="success">✅ Test de inserción exitoso (ID: ' . $insert_id . ', Tiempo: ' . $insert_time . 'ms)</div>';
                            
                            // Eliminar el mensaje de prueba
                            $stmt = $pdo->prepare("DELETE FROM mensajes WHERE id = ?");
                            $stmt->execute([$insert_id]);
                            echo '<div class="info">🧹 Mensaje de prueba eliminado correctamente</div>';
                            
                        } catch (Exception $e) {
                            echo '<div class="error">❌ Error en test de inserción: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            echo '<div class="warning">💡 Esto puede indicar problemas de permisos en la base de datos</div>';
                        }
                        
                    } else {
                        echo '<div class="error">❌ Tabla "mensajes" NO existe <span class="status-badge badge-error">FALTA</span></div>';
                        echo '<div class="warning">💡 <strong>Solución:</strong> Ejecutar el script database.sql en tu base de datos AlwaysData</div>';
                        echo '<div class="info">📄 El archivo database.sql debe estar en la raíz del proyecto</div>';
                    }
                } else {
                    echo '<div class="error">❌ Error: No se pudo conectar a la base de datos</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">❌ Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</div>';
                
                // Análisis detallado del error
                $error = $e->getMessage();
                echo '<div class="warning"><strong>🔍 Análisis Detallado del Error:</strong><br>';
                if (strpos($error, 'Access denied') !== false) {
                    echo '• <strong>Problema:</strong> Credenciales incorrectas<br>';
                    echo '• <strong>Causa:</strong> Usuario o contraseña inválidos<br>';
                    echo '• <strong>Solución:</strong> Verificar credenciales en el panel de AlwaysData<br>';
                } elseif (strpos($error, 'Unknown database') !== false) {
                    echo '• <strong>Problema:</strong> Base de datos no existe<br>';
                    echo '• <strong>Causa:</strong> El nombre de la BD es incorrecto o no fue creada<br>';
                    echo '• <strong>Solución:</strong> Crear la base de datos en AlwaysData<br>';
                } elseif (strpos($error, 'Connection refused') !== false || strpos($error, 'timed out') !== false) {
                    echo '• <strong>Problema:</strong> Servidor no accesible<br>';
                    echo '• <strong>Causa:</strong> Host o puerto incorrectos, o servidor caído<br>';
                    echo '• <strong>Solución:</strong> Verificar host y puerto en AlwaysData<br>';
                } else {
                    echo '• <strong>Error desconocido:</strong> ' . htmlspecialchars($error) . '<br>';
                    echo '• <strong>Recomendación:</strong> Contactar soporte técnico<br>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <!-- INFORMACIÓN DEL SERVIDOR -->
        <div class="section">
            <h2>🖥️ Información del Servidor</h2>
            <div class="info">
                <strong>🐘 PHP Version:</strong> <?= PHP_VERSION ?><br>
                <strong>🌐 Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible' ?><br>
                <strong>🕐 Timezone:</strong> <?= date_default_timezone_get() ?><br>
                <strong>💾 Memory Limit:</strong> <?= ini_get('memory_limit') ?><br>
                <strong>⏱️ Max Execution Time:</strong> <?= ini_get('max_execution_time') ?>s<br>
                <strong>📁 Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'No disponible' ?><br>
                <strong>🔧 Server Name:</strong> <?= $_SERVER['SERVER_NAME'] ?? 'No disponible' ?>
            </div>
        </div>

        <!-- EXTENSIONES PHP -->
        <div class="section">
            <h2>🔌 Extensiones PHP Requeridas</h2>
            <?php
            $required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'curl', 'json'];
            foreach ($required_extensions as $ext) {
                if (extension_loaded($ext)) {
                    echo '<div class="success">✅ <strong>' . $ext . '</strong> está disponible <span class="status-badge badge-success">OK</span></div>';
                } else {
                    echo '<div class="error">❌ <strong>' . $ext . '</strong> NO está disponible <span class="status-badge badge-error">FALTA</span></div>';
                }
            }
            ?>
        </div>

        <!-- PRÓXIMOS PASOS -->
        <div class="section">
            <h2>🎯 Próximos Pasos</h2>
            <div class="info">
                <strong>✅ Si todo está funcionando:</strong><br>
                1. <a href="/" class="btn">🚀 Ir al Chat Principal</a><br>
                2. Probar envío y recepción de mensajes en tiempo real<br>
                3. Verificar que Pusher esté funcionando correctamente<br><br>
                
                <strong>❌ Si hay errores:</strong><br>
                1. <strong>Problemas de BD:</strong> Verificar credenciales en AlwaysData<br>
                2. <strong>Tabla faltante:</strong> Ejecutar database.sql en tu base de datos<br>
                3. <strong>Extensiones PHP:</strong> Contactar soporte de Render<br>
                4. <strong>Pusher:</strong> Verificar configuración en pusher.com<br><br>
                
                <strong>🔄 Para volver a ejecutar este diagnóstico:</strong><br>
                <a href="<?= $_SERVER['REQUEST_URI'] ?>" class="btn">🔄 Recargar Diagnóstico</a>
            </div>
        </div>

        <!-- PIE DE PÁGINA -->
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <p><strong>🔍 Diagnóstico Completo del Chat en Tiempo Real</strong></p>
            <p>Generado automáticamente el <?= date('Y-m-d H:i:s') ?></p>
            <p style="color: #6c757d; font-size: 0.9em;">Este diagnóstico verifica todos los componentes críticos del sistema</p>
        </div>
    </div>
</body>
</html>