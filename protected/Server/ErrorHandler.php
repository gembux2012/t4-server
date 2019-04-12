<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 12.04.19
 * Time: 11:45
 */

namespace App\Server;


use React\Http\Response;
use Throwable;

class ErrorHandler
{

    public function __invoke($e)
    {
        return $this->report($e);

    }

    private function report($e)
    {
        return new Response(
            500, ['Content-Type' => 'text/html'],
            $e->getMessage()
        );
    }
}