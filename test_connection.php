<?php
// Test de conexión a base de datos - Diagnóstico completo
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Conexión a Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Completo de Base de Datos</h1>
        
        <?php
        // 1. Verificar variables de entorno
        echo "<h2>1. Variables de Entorno</h2>";
        
        $db_host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'test';
        $db_user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
        $db_pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '';
        $db_port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
        
        echo "<div class='info'>";
        echo "<strong>DB_HOST:</strong> " . htmlspecialchars($db_host) . "<br>";
        echo "<strong>DB_NAME:</strong> " . htmlspecialchars($db_name) . "<br>";
        echo "<strong>DB_USER:</strong> " . htmlspecialchars($db_user) . "<br>";
        echo "<strong>DB_PASS:</strong> " . (empty($db_pass) ? "❌ VACÍA" : "✅ Configurada (" . strlen($db_pass) . " caracteres)") . "<br>";
        echo "<strong>DB_PORT:</strong> " . htmlspecialchars($db_port) . "<br>";
        echo "</div>";
        
        // 2. Verificar extensión MySQL
        echo "<h2>2. Extensión MySQL</h2>";
        if (extension_loaded('mysqli')) {
            echo "<div class='success'>✅ Extensión MySQLi está disponible</div>";
        } else {
            echo "<div class='error'>❌ Extensión MySQLi NO está disponible</div>";
        }
        
        if (extension_loaded('pdo_mysql')) {
            echo "<div class='success'>✅ Extensión PDO MySQL está disponible</div>";
        } else {
            echo "<div class='error'>❌ Extensión PDO MySQL NO está disponible</div>";
        }
        
        // 3. Test de conexión con MySQLi
        echo "<h2>3. Test de Conexión MySQLi</h2>";
        
        $connection = null;
        try {
            // Intentar conexión
            $connection = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
            
            if ($connection->connect_error) {
                echo "<div class='error'>";
                echo "❌ Error de conexión MySQLi:<br>";
                echo "<strong>Código:</strong> " . $connection->connect_errno . "<br>";
                echo "<strong>Mensaje:</strong> " . htmlspecialchars($connection->connect_error) . "<br>";
                echo "</div>";
            } else {
                echo "<div class='success'>✅ Conexión MySQLi exitosa</div>";
                
                // Verificar base de datos
                $result = $connection->query("SELECT DATABASE() as current_db");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<div class='info'><strong>Base de datos actual:</strong> " . htmlspecialchars($row['current_db']) . "</div>";
                }
                
                // Verificar tabla mensajes
                $result = $connection->query("SHOW TABLES LIKE 'mensajes'");
                if ($result && $result->num_rows > 0) {
                    echo "<div class='success'>✅ Tabla 'mensajes' existe</div>";
                    
                    // Contar mensajes
                    $result = $connection->query("SELECT COUNT(*) as total FROM mensajes");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "<div class='info'><strong>Total de mensajes:</strong> " . $row['total'] . "</div>";
                    }
                    
                    // Mostrar últimos 3 mensajes
                    $result = $connection->query("SELECT * FROM mensajes ORDER BY timestamp DESC LIMIT 3");
                    if ($result && $result->num_rows > 0) {
                        echo "<div class='info'><strong>Últimos mensajes:</strong><br>";
                        while ($row = $result->fetch_assoc()) {
                            echo "- " . htmlspecialchars($row['usuario']) . ": " . htmlspecialchars($row['mensaje']) . " (" . $row['timestamp'] . ")<br>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<div class='warning'>⚠️ Tabla 'mensajes' NO existe</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "❌ Excepción en conexión MySQLi:<br>";
            echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "</div>";
        }
        
        // 4. Test de conexión con PDO
        echo "<h2>4. Test de Conexión PDO</h2>";
        
        try {
            $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            
            echo "<div class='success'>✅ Conexión PDO exitosa</div>";
            
            // Test de inserción
            $stmt = $pdo->prepare("INSERT INTO mensajes (usuario, mensaje, timestamp) VALUES (?, ?, NOW())");
            $test_result = $stmt->execute(['SISTEMA', 'Test de conexión - ' . date('Y-m-d H:i:s')]);
            
            if ($test_result) {
                echo "<div class='success'>✅ Test de inserción exitoso</div>";
            } else {
                echo "<div class='error'>❌ Error en test de inserción</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "❌ Error de conexión PDO:<br>";
            echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
            echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "</div>";
        }
        
        // 5. Información del servidor
        echo "<h2>5. Información del Servidor</h2>";
        echo "<div class='info'>";
        echo "<strong>PHP Version:</strong> " . PHP_VERSION . "<br>";
        echo "<strong>Servidor:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "<br>";
        echo "<strong>Hora del servidor:</strong> " . date('Y-m-d H:i:s') . "<br>";
        echo "<strong>Timezone:</strong> " . date_default_timezone_get() . "<br>";
        echo "</div>";
        
        // 6. Test de DNS
        echo "<h2>6. Test de Resolución DNS</h2>";
        $ip = gethostbyname($db_host);
        if ($ip !== $db_host) {
            echo "<div class='success'>✅ DNS resuelve: $db_host → $ip</div>";
        } else {
            echo "<div class='error'>❌ No se puede resolver DNS para: $db_host</div>";
        }
        
        // Cerrar conexión
        if ($connection && !$connection->connect_error) {
            $connection->close();
        }
        ?>
        
        <h2>📋 Resumen de Diagnóstico</h2>
        <div class="info">
            <p><strong>Fecha del diagnóstico:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>Si ves errores arriba, copia toda esta información y compártela para obtener ayuda específica.</p>
        </div>
    </div>
</body>
</html>