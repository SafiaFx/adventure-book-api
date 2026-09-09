<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Models\BooksModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BooksController
{
    public function __construct(private BooksModel $booksModel)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::write($response, $this->booksModel->getBooks());
    }
}
