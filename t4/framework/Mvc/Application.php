<?php

namespace T4\Mvc;

use App\Models\User;
use React\Promise\RejectedPromise;
use T4\Console\TRunCommand;
use T4\Core\Exception;
use T4\Core\ISingleton;
use T4\Core\Session;
use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Core\TStdGetSet;
use T4\Dbal\Connection;
use T4\Http\E403Exception;
use T4\Http\E404Exception;
use T4\Http\Request;
use T4\Threads\Helpers;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Stream\ThroughStream;
use React\Promise\Promise;

/**
 * Class Application
 * @package T4\Mvc

 * @property string $path
 * @property string $routeConfigPath
 *
 * @property \T4\Core\Config $config
 * @property \T4\Core\Std $extensions
 * @property \T4\Dbal\Connections|\T4\Dbal\Connection[] $db
 *
 * @property \T4\Mvc\IRouter $router

 *
 * @property \App\Models\User $user
 * @property \T4\Mvc\Module[] $modules
 * @property \T4\Mvc\AssetsManager $assets
 * @property \T4\Core\Flash $flash
 */
class Application
    implements
        ISingleton,
        IApplication
{
    use
        TStdGetSet,
        TSingleton,
        TApplicationPaths,
        TApplicationMagic;

    use TRunCommand;

    public $request =null;


    public $response ;



    protected function init()
    {
        Session::init();
        $this->initExtensions();


    }

    protected function initExtensions()
    {
        $this->extensions = new Std;
        if (isset($this->config->extensions)) {
            foreach ($this->config->extensions as $extension => $config) {

                if (!empty($config->class)) {
                    $extensionClassName = $config->class;
                } else {
                    $extensionClassName = 'Extensions\\' . ucfirst($extension) . '\\Extension';
                    if (class_exists('\\App\\' . $extensionClassName)) {
                        $extensionClassName = '\\App\\' . $extensionClassName;
                    } else {
                        $extensionClassName = '\\T4\\Mvc\\' . $extensionClassName;
                    }
                }

                $this->extensions->{$extension} = new $extensionClassName($config);
                if (!isset($config->autoload) || true == $config->autoload) {
                    $this->extensions->{$extension}->init();
                }
            }
        }
    }


    public function run()
    {



        $this->init();
        $loop = \React\EventLoop\Factory::create();
        $count=0;

               $server = new \React\Http\Server(array(function   (

            ServerRequestInterface $request, callable $next) use ($loop,&$count){
                    $count++;
                   if ($count ==1) {
                       $time1=time();
                   }

                   if ($count%2 === 0) {
                       $time2 = time();

                       if ($time2 - $time1 >= 300) {
                           unset($this->db);
                           $this->db = new Connections($this->config->db);
                       }
                       $count=0;
                   }


            $promise = new Promise(function ($resolve) use ($next, $request) {

                $resolve($next($request));

            });

            /*
            return $promise->then( null, function (Exception $e){
                return new Response(
                    500,
                    array(),
                    'Internal error: ' . $e->getMessage()
                );
            } */

            return $promise->otherwise(function($e){
                switch (get_class($e)) {
                    case "T4\Http\E404Exception":
                        {
                            if (!empty($this->config->errors['404'])) {
                                $route = new Route($this->config->errors['404']);
                                $route->params->message = $e->getMessage();
                                return $this->runRoute($route, 'html');
                            }

                        }

                    case "T4\Http\E403Exception":
                        {
                            if (!empty($this->config->errors['403'])) {
                                $route = new Route($this->config->errors['403']);
                                $route->params->message = $e->getMessage();
                                return $this->runRoute($route, 'html');
                            }

                        }

                    default:
                        {
                            return new Response(
                                500,
                                array(),
                                'Internal error: ' . $e->getMessage()
                            );
                        }
                }
            });
              },


            function (ServerRequestInterface $request )  use ($loop) {



                $file = pathinfo($request->getRequestTarget(), PATHINFO_EXTENSION);
                if ($file != '' && $file != 'json') {
                    $givestatic = new GiveStatic();
                    return $givestatic($request, $loop);

                }


                $this->request = new Request($request);
                $route = $this->router->parseRequest($this->request);
                return $this->runRoute($route);

            }




        ));



        $socket = new \React\Socket\Server(8008, $loop);
        $server->listen($socket);
        $server->on('error', function (Exception $e){
           echo $e->getMesage();
        });
        $loop->run();

    }


    /**
     * @param \T4\Mvc\Route|string $route
     * @param string $format
     * @return Response
     * @throws ControllerException
     * @throws E403Exception
     * @throws Exception
     */
    public function runRoute(Route $route, $format = null)
    {
        if (null === $format) {
            $format = $route->format;
        }
        $controller = $this->createController($route->module, $route->controller);
        $controller->action($route->action, $route->params);
        $data = $controller->getData();

        $front = new Front($this, $controller);
        $front->output($route, $data, $format);
        $view = $controller->view;
        return $view();

    }

    public function AppResponse($status,$headers,$body){

        return new Response(
            $status,
            $headers,
            $body
        );


    }

    /**
     * @param callable $callback
     * @param array $args
     * @throws \T4\Threads\Exception
     * @return int Child process PID
     */
    public function runLater(callable $callback, $args = [])
    {
        return Helpers::run($callback, $args);
    }


    /**
     * @param null $module
     * @param $controller
     * @return bool
     */
    public function existsController($module = null, $controller)
    {
        $controllerClassName = (empty($module) ? '\\App\\Controllers\\' : '\\App\\Modules\\' . ucfirst($module) . '\\Controllers\\') . ucfirst($controller);
        return $this->existsModule($module) && class_exists($controllerClassName) && is_subclass_of($controllerClassName, Controller::class);
    }

    /**
     * @param string $module
     * @param string $controller
     * @throws \T4\Core\Exception
     * @return \T4\Mvc\Controller
     */
    public function createController($module = null, $controller)
    {
        if (!$this->existsController($module ?:  null, $controller)) {
            throw new Exception('Controller ' . $controller . ' does not exist');
        }

        if (empty($module))
            $controllerClass = '\\App\\Controllers\\' . $controller;
        else
            $controllerClass = '\\App\\Modules\\' . ucfirst($module) . '\\Controllers\\' . ucfirst($controller);

        $controller = new $controllerClass;

        $view = new View('twig', $controller->getTemplatePaths());
        $controller->view = $view;
        $view->setController($controller);

        return $controller;
    }

    /**
     * @param string $path
     * @param string $template Шаблон блока
     * @param array $params Параметры, передаваемые блоку
     * @throws \T4\Core\Exception
     * @return string Результат рендера блока
     */
    public function callBlock($path, $template = '', $params = [])
    {
        $route = new Route($path);
        $route->params->merge($params);

        $canonicalPath = $route->toString();

        if (isset($this->config->blocks) && isset($this->config->blocks[$canonicalPath])) {
            $blockOptions = $this->config->blocks[$canonicalPath];
        } else {
            $blockOptions = [];
        }

        $getBlock = function () use ($template, $route) {
            $controller = $this->createController($route->module, $route->controller);
            $controller->action($route->action, $route->params);
            return $controller->view->render(
                $route->action . (!empty($template) ? '.' . $template : '') . '.block.html',
                $controller->getData()
            );
        };

        if (!empty($blockOptions['cache'])) {
            $cache = \T4\Cache\Factory::getInstance();
            $key = md5($canonicalPath . serialize($route->params) . $template);
            if (!empty($blockOptions['cache']['time'])) {
                return $cache($key, $getBlock, $blockOptions['cache']['time']);
            } else {
                return $cache($key, $getBlock);
            }
        } else {
            return $getBlock();
        }

    }

    public function action404($message = null)
    {
       /*
        header("HTTP/1.0 404 Not Found", true, 404);
        if (!empty($this->config->errors['404'])) {
            $route = new Route($this->config->errors['404']);
            $route->params->message = $message;
            $this->runRoute($route, 'html');
        } else {
            echo $message;
        }
       */

        return new Response(
            404,
            array(
                'Content-Type' => 'text/plain'
            ),
            $message
        );
    }

    public function action403($message = null)
    {
        header('HTTP/1.0 403 Forbidden', true, 403);
        if (!empty($this->config->errors['403'])) {
            $route = new Route($this->config->errors['403']);
            $route->params->message = $message;
            $this->runRoute($route, 'html');
        } else {
            echo $message;
        }
    }

    

}