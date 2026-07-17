<?php
/**
 * ============================================================================
 * database.php — Credenciales y opciones de MySQL/MariaDB (XAMPP)
 * ============================================================================
 * Este archivo SOLO contiene datos de conexión. La lógica de conexión
 * (PDO, Singleton) vive en app/core/Database.php
 *
 * IMPORTANTE: en producción, no subas contraseñas reales a repositorios públicos.
 * En XAMPP local el usuario root suele venir sin contraseña.
 * ============================================================================
 */

if (!defined('SECRETOS_MARINOS')) {
    die('Acceso no permitido.');
}

return [
    // Host del servidor MySQL (en XAMPP suele ser localhost o 127.0.0.1)
    'host' => '127.0.0.1',

    // Puerto por defecto de MySQL/MariaDB
    'port' => '3306',

    // Nombre de la base de datos (debe existir o crearse con schema.sql)
    'dbname' => 'secretos_marinos',

    // Usuario MySQL (XAMPP por defecto: root)
    'username' => 'root',

    // Contraseña MySQL (XAMPP por defecto: vacía)
    'password' => '',

    // Charset recomendado para español y emojis
    'charset' => 'utf8mb4',

    // Opciones PDO: errores como excepciones, resultados asociativos, sin emulación
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
