
<?php

$host = 'localhost';
$banco = 'saboroso';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Não foi possível conectar ao banco de dados.');
}
$youtubeApiKey = 'AIzaSyDygWzvnx5kBm7Kqqsaff4ooE4p3BIdID8';
$resendApiKey = 're_5oq7vWS6_JXXnztL3dnq7Vx4wCstWo15P';
$resendOwnerEmail = 'leticiarnogueiraa@gmail.com';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
