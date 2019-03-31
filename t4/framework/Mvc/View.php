<?php

namespace T4\Mvc;

use T4\Html\Meta;
use T4\Mvc\Renderers\Vanilla;
use React\Http\Response;

class View
{


    /**
     * @var \T4\Mvc\ARenderer
     */
    protected $renderer;

    /**
     * @var \T4\Html\Meta
     */
    public $meta;

    private  $body = null;
    private  $headers = null;
    private $status =200;
    public function __construct($renderer = '', $paths = [])
    {
        $this->meta = new Meta();
        if (empty($renderer) || 'vanilla' == $renderer) {
            $this->renderer = new Vanilla($paths);
        } else {
            $class = '\T4\Mvc\Renderers\\' . ucfirst($renderer);
            if (class_exists($class)) {
                $this->renderer = new $class($paths);
            } else {
                $this->renderer = new Vanilla($paths);
            }
        }
        if (method_exists($this->renderer, 'setView')) {
            $this->renderer->setView($this);
        }
    }

    public function addTemplatePath($path)
    {
        $this->renderer->addTemplatePath($path);
    }

    /**
     * @var \T4\Mvc\Controller
     */
    protected $controller;

    public function setController(Controller $controller)
    {
        $this->controller = $controller;
        if (method_exists($this->renderer, 'setController')) {
            $this->renderer->setController($controller);
        }
    }

    public function render($template, $data = [])
    {

        return $this->postProcess(
            $this->renderer->render($template, $data)
        );
    }

  public function SetHeaders($headers){
      $this->headers = $headers;
  }

    public function SetStatus($status){
        $this->status = $status;
    }
    public function display($template, $data = [], $format )
    {
        if($this->status ==302)
            return;
        $app = \T4\Mvc\Application::instance();
        switch ($format) {
            case 'json':
                {
                    $this->headers = isset($this->headers) ? $this->headers +=['Content-Type' => 'application/json', 'charset' => 'utf-8']
                        : ['Content-Type' => 'application/json', 'charset' => 'utf-8'];
                    $this->body = json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

                    break;
                }
            case 'xml':
                {
                    $this->headers += ['Content-Type' => 'text/xml', 'charset' => 'utf-8'];
                    $this->body = $this->render($template, $data);

                    break;
                }
            default:
            case 'html':
            {

                $this->headers = isset($this->headers) ? $this->headers +=['Content-Type' => 'text/html', 'charset' => 'utf-8']
                    : ['Content-Type' => 'text/html', 'charset' => 'utf-8'];
                $this->body = $this->render($template, $data);

                break;
            }
        }


    }

    public function __invoke($status=0, $headers =[], $body = '') {
        $_headers=!empty($headers) ? : $this->headers;
        $_body=!empty($body) ? : $this->body;
        $_status = $status == 0 ?  $this->status : $status;
        return new Response(
            $_status,
            $_headers,
            $_body

        );

    }

    protected function postProcess($content)
    {
        $content = $this->parseTags($content);
        return $content;
    }

    const TAG_PATTERN = '~<t4:(?P<tag>[^>\s]+)[\s]*(?P<params>[\s\S]*?)(/>|>)((?P<html>[\s\S]*?)</t4:(?P=tag)>)?~i';

    protected function parseTags($content)
    {
        preg_match_all(self::TAG_PATTERN, $content, $m);
        foreach ($m['tag'] as $n => $tag) {
            $tagClassName = '\T4\Mvc\Tags\\' . ucfirst($tag);
            $tag = new $tagClassName($m['params'][$n], $m['html'][$n]);
            try {
                $content = str_replace($m[0][$n], $tag->render(), $content);
            } catch (Exception $e) {
                echo $e->getMessage();
                $content = str_replace($m[0][$n], '', $content);
            }
        }
        return $content;
    }

}