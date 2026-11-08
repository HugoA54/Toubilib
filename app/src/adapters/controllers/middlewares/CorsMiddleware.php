<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private string $origine,
        private string $methodes,
        private string $headers,
    ) {
    }

    public function __invoke(ServerRequestInterface $rq, RequestHandlerInterface $next): ResponseInterface
    {
        $rs = $this->estPreflight($rq)
            ? $this->responseFactory->createResponse(204)
            : $next->handle($rq);

        return $rs
            ->withHeader('Access-Control-Allow-Origin', $this->origine)
            ->withHeader('Access-Control-Allow-Methods', $this->methodes)
            ->withHeader('Access-Control-Allow-Headers', $this->headers)
            ->withHeader('Access-Control-Expose-Headers', 'Location')
            ->withHeader('Access-Control-Max-Age', '3600');
    }

    private function estPreflight(ServerRequestInterface $rq): bool
    {
        return $rq->getMethod() === 'OPTIONS' && $rq->hasHeader('Access-Control-Request-Method');
    }
}
