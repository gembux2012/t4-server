<?php

namespace App\Controllers;

use App\Components\Auth\Identity;
use T4\Mvc\Controller;

class User
    extends Controller
{

    public function actionLogin($login = null)
    {
        if (null !== $login) {
            try {
                $identity = new Identity();
                $identity->authenticate($login);
               // $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
               // $this->redirect('/');
            } catch (\T4\Core\Exception $e) {
                $this->data->login_err = $e->getMessage();
            }
            $this->data->user = $login->email;
        }
    }

    public function actionLogout()
    {
        $identity = new Identity();
        $identity->logout();
        if ($this->app->user)
            $this->data->user=$this->app->user->email;
      //  $this->redirect('/');
    }

    public function actionRegister($register = null)
    {
        if (null !== $register) {
            try {
                $identity = new Identity();
                $user = $identity->register($register);
                $identity->login($user);
                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
                $this->redirect('/');
            } catch (\App\Components\Auth\MultiException $e) {
                $this->data->errors = $e;
            }
            $this->data->email = $register->email;
        }
    }

    public function actionGetUser()
    {
       if ($this->app->user)
        $this->data->user=$this->app->user->email;
    }

    public function actionGetLogin($login = null)
    {
        $err = false;
        if (null !== $login) {
            $user = \App\Models\User::findByEmail($login->email);
            if (empty($user)) {
                $this->data->errors = ['errlogin' => 'Пользователь c таким e-mail не зарегестрирован! '];
                $err = true;
            } else if (!\T4\Crypt\Helpers::checkPassword($login->password, $user->password)) {
                $this->data->errors = ['errpassword' => 'Неверный пароль!'];
                $err = true;
            }
            if (!$err) {
                $identity = new Identity();
                $identity->login($user);
                Application::getInstance()->user = $user;
            }
        }
    }
    /*
public function actionRestorePassword($restore = null)
{
    if (null !== $restore) {
        try {
            $identity = new Identity();
            $user = $identity->restorePassword($restore);
            if (null == Session::get('controlstring')) {
                $controlstring = ['controlstring' => ['string' => User::getRandomString(6, 'all'), 'start_time' => time()]];
                Session::set('controlstring', $controlstring);
                $mail = new Sender();
                $mail->sendMail($restore->email, 'Востановление пароля', 'Для восстановления пароля введите этот код: ' .
                    $controlstring['controlstring']['string']);
                $this->data->step = 1;
                $this->data->email = $restore->email;
            } else if ($restore->step == 2) {
                $identity->login($user);
                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
                $this->redirect('/');
            } else {
                $this->data->step = 2;
                $this->data->email = $restore->email;
            }

        } catch (\App\Components\Auth\MultiException $e) {

            $this->data->errors = $e;
            $this->data->email = $restore->email;
            foreach ($e as $error) {
                switch ($error->getCode()) {
                    case 103: {
                        $this->data->step = 1;
                        break;
                    }
                    case 104: {
                        $this->data->step = 100;
                        break;
                    }
                    case 101: {
                        $this->data->step = 2;
                        break;
                    }
                }
            }
        }
    } else {
        Session::clear('controlstring');
        $this->data->step = 0;
    }
}
*/

}