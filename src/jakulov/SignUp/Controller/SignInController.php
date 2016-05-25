<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 15:06
 */

namespace jakulov\SignUp\Controller;

use jakulov\SignUp\Container;
use jakulov\SignUp\Exception\SignUpException;
use jakulov\SignUp\Model\User;
use jakulov\SignUp\Service\Language;
use jakulov\SignUp\Validator\ResetValidator;
use jakulov\SignUp\Validator\Validator;

/**
 * Class SignInController
 * @package jakulov\SignUp\Controller
 */
class SignInController extends Controller
{
    /**
     * @return \jakulov\SignUp\Http\Response
     * @throws SignUpException
     */
    protected function signInAction()
    {
        if($this->getAuthUser()) {
            return $this->redirect('/');
        }

        $data = $this->getRequest()->getRequest();
        $validator = new Validator(['email' => [Validator::FILTER_NOT_EMPTY, Validator::FILTER_VALID_EMAIL], 'password' => Validator::FILTER_NOT_EMPTY]);
        $errors = [];
        if($this->getRequest()->getMethod() === 'POST') {
            if($validator->validate($data)) {
                $user = Container::getInstance()->getUserService()->checkCredentials($data['email'], $data['password']);
                if($user) {
                    Container::getInstance()->getUserService()->setAuthUser($user, isset($data['remember']), isset($data['bind_ip']));
                    $this->addFlash('success', Language::get(USER_SUCCESS_LOGIN));

                    return $this->redirect('/');
                }
            }
            else {
                $errors = $validator->getValidationErrors();
            }
        }

        return $this->render('sign_in', [
            'validator' => $validator,
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * @return \jakulov\SignUp\Http\Response
     */
    protected function signOutAction()
    {
        Container::getInstance()->getUserService()->logout();

        return $this->redirect('/sign/in');
    }

    /**
     * @return \jakulov\SignUp\Http\Response
     * @throws \jakulov\SignUp\Exception\SignUpException
     */
    protected function forgotAction()
    {
        $validator = new Validator(['email' => Validator::FILTER_VALID_EMAIL]);
        $data = [];
        $errors = [];
        if($this->getRequest()->getMethod() === 'POST') {
            $data = $this->getRequest()->getRequest();
            if($validator->validate($data)) {
                $user = User::findOneBy(['email' => $data['email']]);
                if($user) {
                    Container::getInstance()->getUserService()->startResetPassword($user);
                    $this->addFlash('success', Language::get(USER_RESET_PASSWORD_STARTED));

                    return $this->redirect('/sign/in');
                }

                $errors['email'] = Language::get(USER_NOT_FOUND);
            }
            else {
                $errors = $validator->getValidationErrors();
            }
        }

        return $this->render('forgot_form', [
            'validator' => $validator,
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * @return \jakulov\SignUp\Http\Response
     * @throws SignUpException
     */
    protected function resetAction()
    {
        $hash = $this->getRequest()->getQuery(User::RESET_TOKEN_PARAM);
        $user = $hash === 'test' ? true : User::findOneBy(['resetToken' => $hash]);
        if($user) {
            $validator = new ResetValidator();
            $data = $this->getRequest()->getRequest();
            $errors = [];
            if($this->getRequest()->getMethod() === 'POST' && $user instanceof User) {
                if($validator->validate($data)) {
                    $user->resetToken = '';
                    $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
                    if($user->save()) {
                        $this->addFlash('success', Language::get(USER_PASSWORD_RESET_SUCCESS));

                        return $this->redirect('/sign/in');
                    }

                    throw new SignUpException('Unable to save user');
                }
                else {
                    $errors = $validator->getValidationErrors();
                }
            }

            return $this->render('forgot_reset', [
                'validator' => $validator,
                'data' => $data,
                'errors' => $errors,
            ]);
        }

        $this->addFlash('error', Language::get(INVALID_RESET_TOKEN));

        return $this->redirect('/sign/in');
    }
}