<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01.03.19
 * Time: 10:27
 */


namespace App\Server;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use T4\Fs\Helpers;


class GiveStatic
{
    private $loop;

    public function __construct( $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(ServerRequestInterface $request,  $next)
    {
        $file = pathinfo($request->getUri()->getPath(), PATHINFO_EXTENSION);
        if ( '' == $file ||   'json' ==$file) {
            return $next($request);

        }
        $ext = pathinfo($request->getRequestTarget(), PATHINFO_EXTENSION);
        $file = Helpers::getRealPath(urldecode($request->getUri()->getPath()));

        if (file_exists($file)) {

            switch ($ext) {

                case 'css':
                    $content_type = 'text/css';
                    break;
                case 'js':
                    $content_type = 'application/javascript';
                    break;
                default :
                    $content_type = mime_content_type($file);
            }

            $last_modified_time = filemtime($file);
            $etag = md5_file($file);
            $header = [
                'Modified-Since' => gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT",
                'Etag' => $etag,
                'Content-Type' => $content_type
            ];


            if (@strtotime($request->getHeaderLine('If-Modified-Since')) == $last_modified_time ||
                $request->getHeaderLine('If-None-Match') == $etag
            ) {
                return new Response(304);
            } else {

                //$filesystem = \React\Filesystem\Filesystem::create($loop);

                $file_out = $this ->filesystem -> file($file);

                return $file_out->open('r')->then(
                    function (\React\Filesystem\Stream\ReadableStream $stream) use ($header) {
                        return new Response(200, $header, $stream);
                    },
                    function (Exception $exception) {
                        echo $exception->getMessage() . PHP_EOL;
                    }
                );

            }
        } else {
            return new Response(500,
                ['Content-Type' => 'text/plain',
                    'charset' => 'utf-8'
                ],
                'fail not found');
        }

    }
}