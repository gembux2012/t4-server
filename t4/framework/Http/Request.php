<?php

namespace T4\Http;

use T4\Core\Std;
use T4\Core\Url;
use T4\Core\Session;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;


/**
 * Class Request
 * @package T4\Http
 * @property int $ip
 * @property string $protocol
 * @property string $method
 * @property string $host
 * @property string $path
 * @property string $extension
 * @property string $fullPath
 * @property \T4\Core\Url $url
 * @property \T4\Core\Std $get
 * @property \T4\Core\Std $post
 * @property \T4\Core\Std $body
 * @property \T4\Core\Std $files
 * @property \T4\Core\Std $headers
 */
class Request
    extends Std
{

    private $request;

    public function __construct(ServerRequestInterface $request)
    {
       $this->request = $request;
        /*
        $port = $_SERVER['SERVER_PORT'];
        if (!empty($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] !== 'off')
                $protocol = 'https';
            else
                $protocol = 'http';
        } else {
            if ($port == 443)
                $protocol = 'https';
            else
                $protocol = 'http';
        }
        $host = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_X_REWRITE_URL'];
        $path = $_SERVER['REQUEST_URI'];

*/
        $port=$request->getUri()->getPort();
        $this->url = new Url( $request->getUri()->getScheme()  . '://' . $request->getUri()->getHost() . ($port != 80 && $port != 443 ? ':' . $port : '') . $request->getRequestTarget());

        //$this->get = new Std($_GET);
        $this->get = new Std($request->getQueryParams());

       // $this->post = new Std($_POST);
        $this->post = new Std($request->getParsedBody());

        $input = file_get_contents('php://input');
        if ($input) {
            $decoded = @json_decode($input, true);
            if (JSON_ERROR_NONE == json_last_error()) {
                $this->body = new Std($decoded);
            } else {
                $this->body = $input;
            }
        }

        $this->files = new Std();
        $files = $request->getUploadedFiles();
        if (!empty($files)) {
            foreach ($files as $name => $data) {
                if (is_array($data['error'])) {
                    $file = [];
                    foreach ($data['error'] as $n => $error) {
                        $file[$n] = new Std();
                        $file[$n]->error = $error;
                        $file[$n]->name = $data['name'][$n];
                        $file[$n]->tmp_name = $data['tmp_name'][$n];
                        $file[$n]->size = $data['size'][$n];
                    }
                    $this->files->$name = $file;
                } else {
                    $this->files->$name = new Std($data);
                }
            }
        }

        $this->headers = new Std($this->getHttpHeaders());
    }

    public function getProtocol()
    {
        return $this->url->protocol;
    }

    public function getMethod()
    {
        return strtolower($this->request->getMethod());
    }

    public function getHost()
    {
        return $this->url->host;
    }

    public function getCookie()
    {
        return $this->request->getCookieParams();
    }

    public function getHeader($name)
    {
        return $this->request->getHeaderLine($name);
    }


    public function getPath()
    {
        $path = parse_url($this->request->getRequestTarget(), PHP_URL_PATH);
        $path = preg_replace('~/+$~', '', $path);
        return $path;
    }

    public function getFullPath()
    {
        return $this->host . '!' . $this->path;
    }

    public function getExtension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function existsGetData()
    {
        return 0 !== count($this->get);
    }

    public function existsPostData()
    {
        return 0 !== count($this->post);
    }

    public function existsFilesData()
    {
        return 0 !== count($this->files);
    }

    public function isUploaded($file)
    {
        return isset($this->files->{$file}) && (is_array($this->files->{$file}) || \UPLOAD_ERR_OK == $this->files->{$file}->error);
    }

    public function isUploadedArray($file)
    {
        return $this->isUploaded($file) && is_array($this->files->{$file});
    }

    protected function getHttpHeaders()
    {
        if (function_exists('getallheaders'))
            return getallheaders();

        $ret = [];
        $headers = $this->request->getHeaders();
        foreach ($headers as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $ret[$key] = $value;
            }
        }
        return $ret;
    }



    public function getIp()
    {
        if (isset($this->request->getServerParams()['REMOTE_ADDR'])) {
            return ip2long($this->request->getServerParams()['REMOTE_ADDR']);
        } else {
            return false;
        }
    }

}

