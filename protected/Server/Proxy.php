<?php

use Psr\Http\Message\RequestInterface;
use React\EventLoop\Factory;
use React\Http\Server;
use RingCentral\Psr7;
use React\HttpClient\Client;
use React\HttpClient\Response;


$loop = Factory::create();
$client = new Client($loop);
$server = new Server(function (RequestInterface $request) use($client) {


    $host = (string)$request->getUri()->withScheme('')->withPath('')->withQuery('');
    $target = (string)$request->getUri()->withScheme('')->withHost('')->withPort(8009);
    if ($target === '') {
        $target = $request->getMethod() === 'OPTIONS' ? '*' : '/';
    }
    $outgoing = $request->withRequestTarget($target)->withHeader('Host', $host);
    //$request = $client->request('GET', 'http://php.net/');
    $request->



});

$socket = new \React\Socket\Server(8007, $loop);
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();