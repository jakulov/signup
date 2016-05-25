<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:52
 */

namespace jakulov\SignUp\Controller;

use jakulov\SignUp\Container;
use jakulov\SignUp\Exception\SignUpException;
use jakulov\SignUp\Http\JsonResponse;
use jakulov\SignUp\Model\User;
use jakulov\SignUp\Service\Language;
use jakulov\SignUp\Validator\SingUpValidator;

/**
 * Class SignUpController
 * @package jakulov\SignUp\Controller
 */
class SignUpController extends Controller
{
    /**
     * @return \jakulov\SignUp\Http\Response
     * @throws SignUpException
     */
    protected function signUpAction()
    {
        if($this->getAuthUser()) {
            return $this->redirect('/');
        }

        $data = $this->getRequest()->getRequest();
        $validator = new SingUpValidator();
        $errors = [];
        if($this->getRequest()->getMethod() === 'POST') {
            if ($validator->validate($data)) {
                $user = Container::getInstance()->getUserService()->signUpUser($data);
                if($user) {
                    Container::getInstance()->getUserService()->setAuthUser($user);
                    $this->addFlash('success', Language::get(USER_SUCCESS_SIGN_UP));

                    return $this->redirect('/');
                }

                throw new SignUpException('Unable to save user data');
            }
            else {
                $errors = $validator->getValidationErrors();
            }
        }

        return $this->render('sign_up', [
            'validator' => $validator,
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    /**
     * @return JsonResponse
     */
    protected function validateEmailAction()
    {
        $value = $this->getRequest()->getQuery('email');
        $hasUser = User::findOneBy(['email' => trim($value)]);
        $data = ['ok' => 0];
        if($hasUser) {
            $data['ok'] = 1;
        }

        return new JsonResponse($data);
    }
}