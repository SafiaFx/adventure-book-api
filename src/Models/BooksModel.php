<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class BooksModel
{
    public function __construct(private PDO $db)
    {
    }

    public function getAuthors(): array
    {
        return $this->db->query('SELECT id, author FROM Authors ORDER BY id')->fetchAll();
    }

    public function getBooks(): array
    {
        return $this->db->query('SELECT id, book, author_id FROM Books ORDER BY id')->fetchAll();
    }

    public function getBook(int $bookId): ?array
    {
        $query = $this->db->prepare('SELECT id, book, author_id FROM Books WHERE id = :id');
        $query->bindValue(':id', $bookId, PDO::PARAM_INT);
        $query->execute();
        return $query->fetch() ?: null;
    }

    public function authorExists(int $authorId): bool
    {
        $query = $this->db->prepare('SELECT id FROM Authors WHERE id = :id');
        $query->bindValue(':id', $authorId, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchColumn() !== false;
    }

    public function getBooksByAuthorId(int $authorId): array
    {
        $query = $this->db->prepare(
            'SELECT Books.id, Books.book FROM Books
             INNER JOIN Authors ON Books.author_id = Authors.id
             WHERE Authors.id = :id ORDER BY Books.id'
        );
        $query->bindValue(':id', $authorId, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }
}
