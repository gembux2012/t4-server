<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01.03.19
 * Time: 10:27
 */


namespace T4\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use React\ChildProcess\Process;
use React\Http\Response;
use T4\Fs\Helpers;


class GiveStatic
{

    public function __invoke(ServerRequestInterface $request, $loop)
    {

        $ext = pathinfo($request->getRequestTarget(), PATHINFO_EXTENSION);
        $file = Helpers::getRealPath(urldecode($request->getUri()->getPath()));

        if (file_exists($file)){

        switch ($ext) {

            case 'css':
                $content_type = 'text/css';
                break;
            case 'js':
                $content_type = 'application/javascript';
                break;
            default :
                $content_type = 'application/octet-stream';
        }

        $last_modified_time = filemtime($file);
        $etag = md5_file($file);
        $header = [
            "Last-Modified" => gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT",
            "Etag" => $etag,
            'Content-Type' => $content_type
        ];


        if (@strtotime($request->getHeaderLine('If-Modified-Since')) == $last_modified_time ||
            $request->getHeaderLine('If-None-Match') == $etag
        ) {
            return new Response(304);
        } else {
         /*   $status = 200;
            //$childProcess = new Process('cat ' . $file);
            //$childProcess->start($loop);
            return new Response($status,
                $header,
                $childProcess->stdout
            );
         */
            $filesystem = \React\Filesystem\Filesystem::create($loop);
                    $file = $filesystem->file($file);
                    return $file->open('r')->then(
                        function (\React\Filesystem\Stream\ReadableStream $stream) {
                            return new Response(200, ['Content-Type' => 'video/mp4'], $stream);
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