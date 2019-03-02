<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 01.03.19
 * Time: 10:27
 */


namespace T4\Mvc;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\ChildProcess\Process;
use T4\Fs\Helpers;


class GiveStatic
{

    public function __invoke($file)
    {

        if (file_exists(Helpers::getRealPath($file))) {
            $childProcess = new Process('cat '.$file, Helpers::getRealPath($file));

            return new Response(200, [], $childProcess->stdout

            );
        }
    }

}