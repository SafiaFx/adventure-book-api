<?php
declare(strict_types=1);

// Deliberately refuse to replace an existing database.
if ((getenv('DB_DRIVER') ?: 'sqlite') !== 'sqlite') {
    fwrite(STDERR, "This helper creates SQLite databases only. Use the MySQL SQL files for MySQL.\n");
    exit(1);
}
$path = getenv('DB_PATH') ?: dirname(__DIR__) . '/var/books.sqlite';
if (file_exists($path)) {
    fwrite(STDOUT, "Database already exists; left unchanged.\n");
    exit(0);
}
if (!is_dir(dirname($path))) {
    mkdir(dirname($path), 0775, true);
}
$db = new PDO('sqlite:' . $path, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');
$db->beginTransaction();
try {
    $db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sqlite.sql'));
    $db->exec(file_get_contents(dirname(__DIR__) . '/database/02-seed.sql'));
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    $db = null;
    unlink($path);
    throw $error;
}
fwrite(STDOUT, "Created database with 3 authors and 4 books.\n");
