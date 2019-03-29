<?php

namespace T4\Http;


use React\Http\Response;

class Helpers
{

    protected static function getUniversalDomainName($domain)
    {
        if (false !== strpos($domain, '.')) {
            return preg_replace('~^(www\.|)(.*)$~', '.$2', $domain);
        } else {
            // @fix Chrome security policy bug
            return '';
        }
    }

    public  static function setCookie($name, $value, $expire = 0, $max_age, $allSubDomains = true)
    {

        $domain = \T4\Mvc\Application::instance()->request->host;
        $cookie= \T4\Mvc\Application::instance()->request->getCookie();
        if ($allSubDomains)
            $domain = self::getUniversalDomainName($domain);
      //  setcookie($name, $value, $expire, '/', $domain, false, true);
         return ['Set-Cookie' => $name.'='. urlencode($value).'; expires='.$expire
                           .'; Max-Age='.$max_age.'; path=/; Domain='.$domain.'; HttpOnly' ];


        if ($expire > time()) {
            //$_COOKIE[$name] = $value;
            $cookie[$name] = $value;
        }

    }

    public static function issetCookie($name)
    {
        return isset(\T4\Mvc\Application::instance()->request->getCookie()[$name]);
    }

    public static function unsetCookie($name, $allSubDomains = true)
    {
        $app=\T4\Mvc\Application::instance();
        $domain = $app->request->host;;
        if ($allSubDomains)
            $domain = self::getUniversalDomainName($domain);
        //setcookie($name, '', time() - 60 * 60 * 24 * 30, '/', $domain, false, true);
        return ['Set-Cookie' => $name.'= ; 
                     expires='.date("D, j-M-Y H:i:s T",time() - 60 * 60 * 24 * 30).';'];

            //.'; Max-Age='.$max_age.'; path=/; Domain='.$domain.'; HttpOnly' ];
        unset($app->getCookie()[$name]);
    }

    public static function getCookie($name)
    {
        return\T4\Mvc\Application::instance()->request->getCookie()[$name];
    }

    public static function redirect($url)
    {
        if (false === strpos($url, 'http') && false === strpos($url, 'https')) {
            $protocol = \T4\Mvc\Application::instance()->request->protocol;
            $host = \T4\Mvc\Application::instance()->request->host;
            header('Location: ' . $protocol . '://' . $host . $url, true, 302);
        } else {
            header('Location: ' . $url, true, 302);
        }
        exit;
    }

    public function __invoke()
    {
        return new Response(
            200,
           // ['Content-Type' => 'text/plain'],
            self::$cookie
        );
    }

}