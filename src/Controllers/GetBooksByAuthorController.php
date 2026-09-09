<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Http\RouteId;
use App\Models\BooksModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final class GetBooksByAuthorController
{
    public function __construct(private BooksModel $booksModel)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = RouteId::parse($request, $args);
        if (!$this->booksModel->authorExists($id)) {
            throw new HttpNotFoundException($request, 'Author not found.');
        }
        return JsonResponse::write($response, $this->booksModel->getBooksByAuthorId($id));
    }
}
