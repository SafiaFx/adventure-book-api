<?php
declare(strict_types=1);

namespace App\Factories;

use PDO;
use RuntimeException;

final class PDOFactory
{
    public function __invoke(): PDO
    {
        $driver = getenv('DB_DRIVER') ?: 'sqlite';
        if ($driver === 'sqlite') {
            $path = getenv('DB_PATH') ?: dirname(__DIR__, 2) . '/var/books.sqlite';
            if (!is_file($path)) {
                throw new RuntimeException('Database is missing. Run composer db:setup first.');
            }
            $db = new PDO('sqlite:' . $path);
            $db->exec('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $name = getenv('DB_NAME') ?: 'book_app';
            $db = new PDO(
                "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
                getenv('DB_USER') ?: 'book_api',
                getenv('DB_PASSWORD') ?: '',
                [PDO::ATTR_EMULATE_PREPARES => false]
            );
        } else {
            throw new RuntimeException('DB_DRIVER must be sqlite or mysql.');
        }
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    }
}
