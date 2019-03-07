<?php

namespace App\Components\Auth;

use App\Models\User;
use App\Models\UserSession;
use T4\Mvc\Application;
use T4\Auth\Exception;
use T4\Core\Session;
use T4\Http\Request;

class Identity
    extends \T4\Auth\Identity
{
    const  ERROR_INVALID_CAPTCHA = 102;
    const ERROR_INVALID_CODE = 103;
    const ERROR_INVALID_TIME = 104;
    const ERROR_INVALID_NAME = 105;
    const AUTH_COOKIE_NAME = 'T4auth';

    public function authenticate($data)
    {


        if (empty($data->email)) {
            throw new Exception('Не введен e-mail ?',  self::ERROR_INVALID_NAME);
        }
        if ( empty($data->password)) {
            throw new Exception('Не введен пароль ?',  self::ERROR_INVALID_NAME);
        }



        $user = User::findByEmail($data->email);
        if (empty($user)) {
            throw new Exception('Имя пользователя не существует ',  self::ERROR_INVALID_NAME);
        }



        if (!password_verify($data->password, $user->password)) {
              //var_dump($user->password,\T4\Crypt\Helpers::hashPassword('123456'));die;
            throw new Exception('Неверный пароль ');
        }



        $this->login($user);
        Application::Instance()->user = $user;
        return $user;
    }

    public function getUser()
    {
        if (!\T4\Http\Helpers::issetCookie(self::AUTH_COOKIE_NAME))
            return null;

        $hash = \T4\Http\Helpers::getCookie(self::AUTH_COOKIE_NAME);
        $session = UserSession::findByHash($hash);
        if (empty($session)) {
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return null;
        }


        if ($session->userAgentHash != md5(\T4\Mvc\Application::Instance()->request->getHeader('User-Agent'))) {
            $session->delete();
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return null;
        }

        return $session->user;

    }

    public function register($data)
    {
        $errors = new MultiException();

        if (empty($data->email)) {
            $errors->add('Не введен e-mail', self::ERROR_INVALID_EMAIL);
        }

        if (empty($data->password)) {
            $errors->add('Не введен пароль', self::ERROR_INVALID_PASSWORD);
        }

        if (empty($data->password2)) {
            $errors->add('Не введено подтверждение пароля', self::ERROR_INVALID_PASSWORD);
        }

        if ($data->password2 != $data->password) {
            $errors->add('Введенные пароли не совпадают', self::ERROR_INVALID_PASSWORD);
        }

        if (!$errors->isEmpty())
            throw $errors;

        $user = User::findByEmail($data->email);
        if (!empty($user)) {
            $errors->add('Такой e-mail уже зарегистрирован', self::ERROR_INVALID_EMAIL);
        }

        if (!$errors->isEmpty())
            throw $errors;

        $app = Application::Instance();
        if ($app->config->extensions->captcha->register) {
            if (empty($data->captcha)) {
                $errors->add('Не введена строка с картинки', self::ERROR_INVALID_CAPTCHA);
            } else {
                if (!$app->extensions->captcha->checkKeyString($data->captcha)) {
                    $errors->add('Неверные символы с картинки', self::ERROR_INVALID_CAPTCHA);
                }
            }
        }

        if (!$errors->isEmpty())
            throw $errors;

        $user = new User();
        $user->email = $data->email;
        $user->password = \T4\Crypt\Helpers::hashPassword($data->password);
        $user->save();

        return $user;
    }

    /**
     * @param \App\Models\User $user
     */
    public function login($user)
    {
        $app = Application::Instance();
        $expire = isset($app->config->auth) && isset($app->config->auth->expire) ?
            time() + $app->config->auth->expire :
            0;

        $hash = md5(time() . $user->password);
        date_default_timezone_set('GMT');

        \T4\Http\Helpers::setCookie(self::AUTH_COOKIE_NAME, $hash, date("D, j-M-Y H:i:s T",$expire)
        ,$expire);

        $session = new UserSession();
        $session->hash = $hash;
        $session->userAgentHash = md5(\T4\Mvc\Application::Instance()->request->getHeader('User-Agent'));

        $session->user = $user;
        $session->save();
    }

    public function logout()
    {
        if (!\T4\Http\Helpers::issetCookie(self::AUTH_COOKIE_NAME))
            return;

        $hash = \T4\Http\Helpers::getCookie(self::AUTH_COOKIE_NAME);
        $session = UserSession::findByHash($hash);
        if (empty($session)) {
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return;
        }

        $session->delete();
        \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);

        $app = Application::Instance();
        $app->user = null;
    }

}