<?php
declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

final class RouteId
{
    public static function parse(ServerRequestInterface $request, array $args): int
    {
        $value = $args['id'] ?? '';
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!preg_match('/^[1-9][0-9]*$/D', $value) || $id === false) {
            throw new HttpBadRequestException($request, 'ID must be a positive integer.');
        }
        return $id;
    }
}
