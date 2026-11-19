<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\SaudeController;
use App\Http\ErrorHandler;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\JwtAuthMiddleware;
use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory as Slim;
use Slim\Routing\RouteCollectorProxy;

final class AppFactory
{
    /** @param array<string, mixed> $substituicoes definições extras (usadas nos testes) */
    public static function criar(array $substituicoes = []): App
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(Dependencias::definicoes());
        if ($substituicoes !== []) {
            $builder->addDefinitions($substituicoes);
        }
        if (getenv('APP_ENV') === 'production') {
            $builder->enableCompilation(sys_get_temp_dir() . '/di-cache');
        }
        $container = $builder->build();

        $app = Slim::createFromContainer($container);
        $fabrica = $app->getResponseFactory();

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        $erros = $app->addErrorMiddleware((bool) $container->get('app.debug'), false, false);
        $erros->setDefaultErrorHandler(new ErrorHandler($fabrica));
        $app->add(new CorsMiddleware($fabrica, $container->get('cors.origens')));

        $app->get('/saude', SaudeController::class);

        $app->group('/api/v1', function (RouteCollectorProxy $v1) use ($container) {
            $v1->post('/auth/login', [AuthController::class, 'login']);

            $v1->group('', function (RouteCollectorProxy $privado) {
                $privado->get('/me', [AuthController::class, 'eu']);
                $privado->get('/clientes', [ClienteController::class, 'listar']);
                $privado->post('/clientes', [ClienteController::class, 'criar']);
                $privado->get('/clientes/{id}', [ClienteController::class, 'ver']);
            })->add($container->get(JwtAuthMiddleware::class));
        });

        return $app;
    }
}
