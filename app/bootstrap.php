<?php
declare(strict_types=1);

use App\Factories\PDOFactory;
use App\Http\JsonResponse;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Factory\AppFactory;

// Tests inject an isolated database; normal requests use environment configuration.
return static function (?PDO $database = null): App {
    $builder = new ContainerBuilder();
    $builder->addDefinitions([PDO::class => $database ?? DI\factory(PDOFactory::class)]);
    $app = AppFactory::create(container: $builder->build());
    (require __DIR__ . '/routes.php')($app);
    $app->addRoutingMiddleware();
    $errors = $app->addErrorMiddleware(false, true, true);
    $errors->setDefaultErrorHandler(function (
        ServerRequestInterface $request,
        Throwable $exception
    ) use ($app) {
        $status = $exception instanceof HttpException ? $exception->getCode() : 500;
        $message = $status < 500 ? $exception->getMessage() : 'Internal server error.';
        if ($status >= 500) {
            error_log((string) $exception);
        }
        $response = JsonResponse::write($app->getResponseFactory()->createResponse(), [
            'error' => ['status' => $status, 'message' => $message],
        ], $status);
        if ($exception instanceof HttpMethodNotAllowedException) {
            $response = $response->withHeader('Allow', implode(', ', $exception->getAllowedMethods()));
        }
        return $response;
    });
    return $app;
};
