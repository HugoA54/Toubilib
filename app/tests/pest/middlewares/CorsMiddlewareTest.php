<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use toubilib\adapters\controllers\middlewares\CorsMiddleware;

function corsHandler(bool &$appele): RequestHandlerInterface {
    return new class($appele) implements RequestHandlerInterface {
        public function __construct(private bool &$appele) {}

        public function handle(ServerRequestInterface $request): ResponseInterface {
            $this->appele = true;
            return (new ResponseFactory())->createResponse(200);
        }
    };
}

function corsMiddleware(): CorsMiddleware {
    return new CorsMiddleware(new ResponseFactory(), '*', 'GET, POST, OPTIONS', 'Content-Type');
}

it("devrait ajouter les headers CORS à une requête normale", function() {
    $appele = false;
    $rq = (new ServerRequestFactory())->createServerRequest('GET', '/praticiens');

    $rs = corsMiddleware()($rq, corsHandler($appele));

    expect($appele)->toBeTrue()
        ->and($rs->getStatusCode())->toBe(200)
        ->and($rs->getHeaderLine('Access-Control-Allow-Origin'))->toBe('*')
        ->and($rs->getHeaderLine('Access-Control-Allow-Methods'))->toBe('GET, POST, OPTIONS');
});

it("devrait répondre 204 à un preflight sans appeler la suite", function() {
    $appele = false;
    $rq = (new ServerRequestFactory())->createServerRequest('OPTIONS', '/rdvs')
        ->withHeader('Access-Control-Request-Method', 'POST');

    $rs = corsMiddleware()($rq, corsHandler($appele));

    expect($appele)->toBeFalse()
        ->and($rs->getStatusCode())->toBe(204)
        ->and($rs->getHeaderLine('Access-Control-Allow-Origin'))->toBe('*');
});
