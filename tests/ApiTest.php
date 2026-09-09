<?php
declare(strict_types=1);

namespace Tests;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;

final class ApiTest extends TestCase
{
    private PDO $db;
    private App $app;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->db->exec('PRAGMA foreign_keys = ON');
        $this->db->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sqlite.sql'));
        $this->db->exec(file_get_contents(dirname(__DIR__) . '/database/02-seed.sql'));
        $build = require dirname(__DIR__) . '/app/bootstrap.php';
        $this->app = $build($this->db);
    }

    private function request(string $path, int $status = 200, string $method = 'GET'): array
    {
        $response = $this->app->handle((new ServerRequestFactory())->createServerRequest($method, $path));
        self::assertSame($status, $response->getStatusCode());
        self::assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    public function testListsAuthorsAndPreservesHomepageAlias(): void
    {
        $authors = $this->request('/authors');
        self::assertCount(3, $authors);
        self::assertSame(['id' => 1, 'author' => 'Jules Verne'], $authors[0]);
        self::assertSame($authors, $this->request('/'));
    }

    public function testListsBooks(): void
    {
        self::assertCount(4, $this->request('/books'));
    }

    public function testRetrievesOneBookAsAnObject(): void
    {
        self::assertSame(['id' => 3, 'book' => 'Treasure Island', 'author_id' => 2], $this->request('/books/3'));
    }

    public function testFiltersBooksByAuthorAndPreservesLegacyAlias(): void
    {
        $books = $this->request('/authors/1/books');
        self::assertSame([1, 2], array_column($books, 'id'));
        self::assertSame($books, $this->request('/1/books'));
    }

    public function testExistingAuthorWithNoBooksHasAnEmptyList(): void
    {
        $this->db->exec("INSERT INTO Authors (id, author) VALUES (4, 'Example author')");
        self::assertSame([], $this->request('/authors/4/books'));
    }

    public function testMissingBookReturns404(): void
    {
        self::assertSame(['error' => ['status' => 404, 'message' => 'Book not found.']], $this->request('/books/999', 404));
    }

    public function testMissingAuthorReturns404(): void
    {
        self::assertSame('Author not found.', $this->request('/authors/999/books', 404)['error']['message']);
    }

    public static function invalidIds(): array
    {
        $cases = [];
        foreach (['abc', '0', '-1', '1.5', '01', '99999999999999999999999999', '1%20OR%201%3D1'] as $id) {
            foreach (["/books/$id", "/authors/$id/books", "/$id/books"] as $path) {
                $cases[$path] = [$path];
            }
        }
        return $cases;
    }

    #[DataProvider('invalidIds')]
    public function testRejectsInvalidIds(string $path): void
    {
        self::assertSame('ID must be a positive integer.', $this->request($path, 400)['error']['message']);
        self::assertCount(4, $this->request('/books'));
    }

    public function testUnknownRouteReturnsJson404(): void
    {
        self::assertSame(404, $this->request('/unknown/route', 404)['error']['status']);
    }

    public function testUnsupportedMethodHasAnAllowHeader(): void
    {
        $response = $this->app->handle((new ServerRequestFactory())->createServerRequest('POST', '/books'));
        self::assertSame(405, $response->getStatusCode());
        self::assertStringContainsString('GET', $response->getHeaderLine('Allow'));
        self::assertSame(405, $this->request('/books', 405, 'POST')['error']['status']);
    }

    public function testInternalErrorsDoNotExposeDatabaseDetails(): void
    {
        $this->db->exec('DROP TABLE Books');
        self::assertSame(['error' => ['status' => 500, 'message' => 'Internal server error.']], $this->request('/books', 500));
    }
}
