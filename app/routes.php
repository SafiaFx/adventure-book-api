<?php
declare(strict_types=1);

use App\Controllers\AuthorController;
use App\Controllers\BookController;
use App\Controllers\BooksController;
use App\Controllers\GetBooksByAuthorController;
use Slim\App;

return static function (App $app): void {
    $app->get('/authors', AuthorController::class);
    $app->get('/books', BooksController::class);
    $app->get('/books/{id}', BookController::class);
    $app->get('/authors/{id}/books', GetBooksByAuthorController::class);

    // Preserve the original academy project's URLs.
    $app->get('/', AuthorController::class);
    $app->get('/{id}/books', GetBooksByAuthorController::class);
};
