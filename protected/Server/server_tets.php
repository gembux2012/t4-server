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
use vakata\database\Exception;


$errorHandler = new ErrorHandler();
/*
$promise = function (ServerRequestInterface $request, callable $next) use($errorHandler){
    try {
    return  new Promise(function ($resolve) use ($next, $request ) {
        $resolve($next($request));
        });
    }catch (\Throwable $exception) {
            return $errorHandler->handle($exception);
        };

    /*
    return $promise->then(NULL, function (Exception $e) {
        return new Response(
            500,
            array(),
            'Internal error: ' . $e->getMessage()
        );

    })->otherwise(function ($e) {
        return new Response(
            500,
            array(),
            'Internal error: ' . $e->getMessage()
        );
    });

//};


$ware = function (ServerRequestInterface $request, callable $next) {
    return new Response(
        200, ['Content-Type' => 'text/plain'], 'sddfg'
    );
};
*/
$loop = EventLoop\Factory::create();

$errorHandler = new ErrorHandler();
$server = new Server([

    function (ServerRequestInterface $request, callable $next) use($errorHandler){

            $promise =  new Promise(function ($resolve) use ($next, $request ) {
                $resolve($next($request));
            });
            return $promise->otherwise(function($e) use ($errorHandler){
                return $errorHandler($e);
            });
       },
    function (ServerRequestInterface $request) {
          throw new \Exception('uyiuyiuyiuyui');
        return new Response(
            200, ['Content-Type' => 'text/plain'], 'sddfg'
        );
    }
    ]) ;


$socket = new \React\Socket\Server(8008, $loop);
$server->listen($socket);
$loop->run();
