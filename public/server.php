<?php

require __DIR__ . '/../t4/vendor/autoload.php';
require __DIR__ . '/../protected/boot.php';
require __DIR__ . '/../protected/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Stream\ThroughStream;
use React\Promise\Promise;
use React\EventLoop;
use React\Http\Io\HttpBodyStream;




$loop = EventLoop\Factory::create();

$server = new \React\Http\Server(array(function   (

    ServerRequestInterface $request, callable $next) use ($loop) {

    $promise = new Promise(function ($resolve) use ($next, $request) {

        $resolve($next($request));
    });

    return $promise->then(NULL, function (Exception $e) {
        return new Response(
            500,
            array(),
            'Internal error: ' . $e->getMessage()
        );

    })->otherwise(function ($e){
        return new Response(
            500,
            array(),
            'Internal error: ' . $e->getMessage()
        );
    });
},

    function (ServerRequestInterface $request, callable $next) {
      $response= new Response();

      $response=$next($request);
      $body= 'hgjhgjhggjhgjhghj';

        return $response->withBody(\RingCentral\Psr7\stream_for($body));


    },

      function (ServerRequestInterface $request ) {
          return new Response(
              200, ['Content-Type' => 'text/plain'],'sddfg'
          );


      }
));




    $socket = new \React\Socket\Server(8008, $loop);
    $server->listen($socket);
    $loop->run();
