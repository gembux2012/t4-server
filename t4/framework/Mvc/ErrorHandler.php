<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 26.02.19
 * Time: 9:57
 */

namespace T4\Mvc;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use Throwable;


class ErrorHandler
{
    private $loop;
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }
    public function handle(Throwable $throwable)
    {
        $this->report($throwable);
        return $this->process($throwable);
    }
    private function report(Throwable $throwable)
    {
        echo 'Error: ' . $throwable->getMessage() . PHP_EOL;
    }
    private function process(Throwable $throwable)
    {
        $process = new Process('cat pages/error.html');
        $process->start($this->loop);
        return new Response(
            500, ['Content-Type' => 'text/html'], $process->stdout
        );
    }
}