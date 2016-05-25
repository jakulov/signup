<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 16:24
 */

namespace jakulov\SignUp\Service;

use jakulov\SignUp\Application;
use jakulov\SignUp\Exception\SignUpException;
use jakulov\SignUp\Http\Request;
use jakulov\SignUp\Http\Response;
use jakulov\SignUp\Model\AuthToken;
use jakulov\SignUp\Model\User;

/**
 * Class UserService
 * @package jakulov\SignUp\Service
 */
class UserService
{
    /** @var Request */
    protected $request;
    /** @var User */
    protected $authUser;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return User|null
     * @throws SignUpException
     */
    public function getAuthUser()
    {
        if($this->authUser === null) {
            session_start();
            $authUserId = isset($_SESSION[User::SESSION_PARAM]) ? (int)$_SESSION[User::SESSION_PARAM] : 0;
            $bindIp = isset($_SESSION[User::SESSION_IP_KEY]) ? $_SESSION[User::SESSION_IP_KEY] : null;
            if($bindIp && $bindIp !== $this->request->getIp()) {
                $authUserId = 0;
                unset($_SESSION[User::SESSION_PARAM]);
                unset($_SESSION[User::SESSION_IP_KEY]);
            }
            if(!$authUserId) {
                $authUserToken = $this->request->getCookie(AuthToken::COOKIE_NAME);
                if($authUserToken) {
                    $authToken = AuthToken::findOneBy(['token' => $authUserToken]);
                    if($authToken && (!$authToken->ip || $authToken->ip === $this->request->getIp())) {
                        $authUserId = $authToken->userId;
                        $_SESSION[User::SESSION_PARAM] = $authUserId;
                        $this->sendTokenCookie($authToken->token);
                    }
                    if(!$authUserId) {
                        Response::unsetCookie(AuthToken::COOKIE_NAME);
                    }
                }
            }
            if($authUserId) {
                $this->authUser = User::find($authUserId);
                if(!$this->authUser) {
                    unset($_SESSION[User::SESSION_PARAM]);
                    unset($_SESSION[User::SESSION_IP_KEY]);
                    Response::unsetCookie(AuthToken::COOKIE_NAME);
                }
            }
            if(!$this->authUser) {
                $this->authUser = false;
            }
        }

        return $this->authUser;
    }

    /**
     * @param $token
     */
    protected function sendTokenCookie($token)
    {
        Response::setCookie(AuthToken::COOKIE_NAME, $token, time() + Application::getConfig()['user']['token_expires'], '/');
    }

    /**
     * @param User $user
     * @param bool $remember
     * @param bool $bindIp
     * @throws SignUpException
     */
    public function setAuthUser(User $user, $remember = false, $bindIp = false)
    {
        if(!isset($_SESSION)) {
            session_start();
        }
        $this->authUser = $user;
        $_SESSION[User::SESSION_PARAM] = $user->id;
        if($bindIp) {
            $_SESSION[User::SESSION_IP_KEY] = $this->request->getIp();
        }
        else {
            unset($_SESSION[User::SESSION_IP_KEY]);
        }
        if($remember) {
            $token = new AuthToken(true, $user);
            if($bindIp) {
                $token->ip = $this->request->getIp();
            }
            $token->save();
            if($token->id) {
                $this->sendTokenCookie($token->token);
            }
            else {
                throw new SignUpException('Unable to save auth token');
            }
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        if(!isset($_SESSION)) {
            session_start();
        }
        unset($_SESSION[User::SESSION_IP_KEY]);
        unset($_SESSION[User::SESSION_PARAM]);
        Response::unsetCookie(AuthToken::COOKIE_NAME);
    }

    /**
     * @param User $user
     * @return User
     * @throws SignUpException
     */
    public function startResetPassword(User $user)
    {
        $user->resetToken = AuthToken::generateRandomToken();
        if($user->save()) {
            $link = 'http://'. $this->request->getHost() .'/sign/in/reset?'. User::RESET_TOKEN_PARAM .'='. $user->resetToken;
            MailService::send($user->email, Language::get(USER_RESET_MAIL_SUBJECT), Language::get(USER_RESET_MAIL_BODY .' '. $link));

            return $user;
        }

        throw new SignUpException('Unable to save user');
    }

    /**
     * @param $email
     * @param $password
     * @return User|bool
     */
    public function checkCredentials($email, $password)
    {
        $user = User::findOneBy(['email' => $email]);
        if($user) {
            if(password_verify($password, $user->password)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * @param array $data
     * @return bool|User
     */
    public function signUpUser(array $data = [])
    {
        $user = new User();

        $user->about = $data['about'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->photo = str_replace('..', '/', $data['photo']);

        $user->save();

        if($user->id) {
            return $user;
        }

        return false;
    }
}