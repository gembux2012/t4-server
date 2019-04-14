<?php

namespace App\Server;

require __DIR__ . '/../../t4/vendor/autoload.php';
require __DIR__ . '/../boot.php';
require __DIR__ . '/../autoload.php';

use React\EventLoop;
use React\Http\Response;
use React\Http\Server;
use React\Promise\Promise;
use Psr\Http\Message\ServerRequestInterface;
use T4\Mvc;

$app = \T4\Mvc\Application
        ::instance()
        ->setConfig(
            new \T4\Core\Config(ROOT_PATH_PROTECTED . '/config.php')
        )
        ->run();

$errorHandler = new ErrorHandler();

$loop = EventLoop\Factory::create();

$filesystem = \React\Filesystem\Filesystem::create($loop);
$givestatic = new GiveStatic($filesystem);


$server = new Server([

    function (ServerRequestInterface $request, callable $next) use($errorHandler){

        $promise =  new Promise(function ($resolve) use ($next, $request ) {
            $resolve($next($request));
        });
        return $promise->otherwise(function($e) use ($errorHandler){
            return $errorHandler($e);
        });
    },

    function (ServerRequestInterface $request, callable $next) use ( $givestatic) {

         return $givestatic($request, $next) ;
    },

    function (ServerRequestInterface $request) {
      //  throw new \Exception('uyiuyiuyiuyui');
        return new Response(
            200, ['Content-Type' => 'text/plain'], 'ok'
        );
    },

]) ;


$socket = new \React\Socket\Server(8008, $loop);
$server->listen($socket);
$loop->run();
