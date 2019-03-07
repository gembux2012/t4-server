<?php

namespace T4\Mvc;

use T4\Core\Exception;
use T4\Core\Std;


class Front
{

    const FORMATS = ['html', 'xml', 'json'];
    const FORMAT_DEFAULT = 'html';

    protected $app;
    protected $controller;

    public function __construct(IApplication $app, Controller $controller = null)
    {
        $this->app = $app;
        $this->controller = $controller;
    }

    public function getTemplateFileName(Route $route, $format = null)
    {
        $format = $format ?: $route->format;
        return $route->action . '.' . $format;
    }

    public function output(Route $route, Std $data, $format = null)
    {
      //  $header= \T4\Mvc\Application::instance()->headers->append();
        $format = $format ?: $route->format;
        $format = $format ?: self::FORMAT_DEFAULT;
        if (!in_array($format, self::FORMATS)) {
            throw new Exception('Invalid output format');
        }

        $template = $this->getTemplateFileName($route, $format);

        switch ($format) {
            case 'json':
                \T4\Mvc\Application::instance()->headers =['Content-Type' => 'application/json','charset' => 'utf-8'];
                \T4\Mvc\Application::instance()->status =200;
                \T4\Mvc\Application::instance()->body = json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);

//                $this->controller->view->response =
  //                  ['status' => 200, 'header' => $header, 'body' => json_encode($data->toArray(), JSON_UNESCAPED_UNICODE)];
                break;
            case 'xml':
                \T4\Mvc\Application::instance()->headers =['Content-Type' => 'text/xml','charset' => 'utf-8'];
                $this->controller->view->display($template, $data);
                break;
            default:
            case 'html':
            \T4\Mvc\Application::instance()->headers =['Content-Type' => 'text/html','charset' => 'utf-8'];
                $this->controller->view->display($template, $data);
               break;
        }
    }

}