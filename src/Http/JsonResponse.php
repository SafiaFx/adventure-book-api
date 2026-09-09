<?php
declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    public static function write(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }
}
