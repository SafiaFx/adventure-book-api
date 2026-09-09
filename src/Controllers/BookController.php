<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Http\RouteId;
use App\Models\BooksModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final class BookController
{
    public function __construct(private BooksModel $booksModel)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $book = $this->booksModel->getBook(RouteId::parse($request, $args));
        if ($book === null) {
            throw new HttpNotFoundException($request, 'Book not found.');
        }
        return JsonResponse::write($response, $book);
    }
}
