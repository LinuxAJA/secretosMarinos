<?php
/**
 * ============================================================================
 * Database.php — Conexión PDO con patrón Singleton
 * ============================================================================
 * ¿Qué es Singleton?
 * Garantiza UNA sola instancia de la conexión a la BD durante toda
 * la petición HTTP. Evita abrir muchas conexiones innecesarias.
 *
 * ¿Cómo se usa?
 *   $pdo = Database::getInstance()->getConnection();
 *   $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
 *   $stmt->execute([$id]);
 * ============================================================================
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    /** @var Database|null Única instancia de esta clase */
    private static ?Database $instance = null;

    /** @var PDO|null Conexión activa a MySQL */
    private ?PDO $connection = null;

    /**
     * Constructor privado: nadie puede hacer "new Database()" desde fuera.
     * Eso obliga a pasar siempre por getInstance().
     */
    private function __construct()
    {
        // Carga el array de config/database.php
        $config = require CONFIG_PATH . '/database.php';

        // DSN = Data Source Name (cadena de conexión de PDO)
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        try {
            // Crea la conexión PDO con usuario, clave y opciones
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            // En local mostramos el error; en producción se registraría en logs
            if (defined('APP_ENV') && APP_ENV === 'local') {
                die('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
            }

            die('No se pudo conectar a la base de datos. Intenta más tarde.');
        }
    }

    /**
     * Devuelve (o crea) la única instancia de Database.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Devuelve el objeto PDO listo para consultas preparadas.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Impide clonar el Singleton (new clone).
     */
    private function __clone(): void
    {
    }

    /**
     * Impide deserializar el Singleton (unserialize).
     */
    public function __wakeup(): void
    {
        throw new \RuntimeException('No se puede deserializar el Singleton Database.');
    }
}
