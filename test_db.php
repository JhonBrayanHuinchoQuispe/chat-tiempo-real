<?php
header('Content-Type: application/json');

// Probando con el usuario existente 436286 y diferentes contraseñas
$passwords = [
    'brayan933783039',
    '',  // contraseña vacía
    '436286',  // mismo que el usuario
    'sistemasic',
    'password',
    '123456'
];

$results = [];

foreach ($passwords as $index => $password) {
    try {
        $pdo = new PDO(
            "mysql:host=mysql-sistemasic.alwaysdata.net;dbname=sistemasic_chat;charset=utf8mb4",
            '436286',
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Si llegamos aquí, la conexión fue exitosa
        $results[] = [
            'test' => $index + 1,
            'host' => 'mysql-sistemasic.alwaysdata.net',
            'dbname' => 'sistemasic_chat',
            'username' => '436286',
            'password' => $password === '' ? '(vacía)' : $password,
            'status' => 'SUCCESS ✅',
            'message' => 'Conexión exitosa - ¡ESTA ES LA CONTRASEÑA CORRECTA!'
        ];
        
        // Probamos una consulta simple
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $results[count($results)-1]['tables'] = $tables;
        
        break; // Si encontramos la contraseña correcta, no seguimos probando
        
    } catch (PDOException $e) {
        $results[] = [
            'test' => $index + 1,
            'host' => 'mysql-sistemasic.alwaysdata.net',
            'dbname' => 'sistemasic_chat',
            'username' => '436286',
            'password' => $password === '' ? '(vacía)' : $password,
            'status' => 'ERROR ❌',
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>