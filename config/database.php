<?php
// la funcion declare(strict_types=1); obliga a PHP a respetar los tipos de datos declarados en las funciones.
declare(strict_types=1);

// Retorna un objeto de conexión PDO configurado con parámetros de alta seguridad.

function getPDOConnection(): PDO
{
    $host = '127.0.0.1'; // Se prefiere IP para evitar latencia de resolución DNS de localhost
    $db = 'jorge_cuellar_db_escuela';
    $user = 'jorge-cuellar';
    $pass = '4)ft5MQK)62J_pryS9'; 
    $charset = 'utf8mb4';

    // DSN (Data Source Name): Define controlador, host, nombre de la BD y cotejo de caracteres
    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false
        ];
// 1er parametro obliga a PDO a lanzar excepciones ante cualquier error SQL (evita fallos silenciosos)
// 2do parametro obliga a PDO a retornar las filas asociando nombres de columnas como claves de arreglo ($row['nombre'])
// 3er parametro DESACTIVA la emulación de sentencias preparadas, Obliga a MySQL a preparar las consultas de forma nativa en el motor, eliminando completamente el riesgo de inyección SQL (SQL Injection).

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // En producción nunca se expone el mensaje exacto al usuario por razones de seguridad
        error_log("Error crítico de conexión SQL: " . $e->getMessage());
        die("Error de conexión: No se pudo establecer comunicación con la base de datos.");
    }
}
