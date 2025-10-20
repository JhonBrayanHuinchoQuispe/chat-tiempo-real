<?php
// Test de conexión con usuario 436286 y diferentes contraseñas
header('Content-Type: application/json');

$passwords = [
    'brayan933783039',  // La contraseña que tienes
    '',                 // Contraseña vacía
    '436286',          // Mismo que el usuario
    'sistemasic',      // Nombre del proyecto
    'password',        // Contraseña común
    '123456'           // Contraseña común
];

$results = [];

foreach ($passwords as $index => $password) {
    try {
        $dsn = "mysql:host=mysql-sistemasic.alwaysdata.net;dbname=sistemasic_chat;charset=utf8mb4";
        $pdo = new PDO($dsn, '436286', $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $results[] = [
            'test' => $index + 1,
            'username' => '436286',
            'password' => $password === '' ? '(vacía)' : $password,
            'status' => 'ÉXITO ✅',
            'message' => '¡CONTRASEÑA CORRECTA ENCONTRADA!'
        ];
        
        // Si encontramos la contraseña correcta, paramos aquí
        break;
        
    } catch (PDOException $e) {
        $results[] = [
            'test' => $index + 1,
            'username' => '436286',
            'password' => $password === '' ? '(vacía)' : $password,
            'status' => 'ERROR ❌',
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>