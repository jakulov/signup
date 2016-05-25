<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 15:05
 */

namespace jakulov\SignUp\Controller;
use jakulov\SignUp\Service\Language;

/**
 * Class ProfileController
 * @package jakulov\SignUp\Controller
 */
class ProfileController extends Controller
{
    /**
     * @return \jakulov\SignUp\Http\Response
     */
    protected function showAction()
    {
        $authUser = $this->getAuthUser();
        if($authUser) {
            return $this->render('profile', [
                'user' => $authUser,
                'title' => Language::get(PROFILE_TITLE),
            ]);
        }

        return $this->redirect('/sign/in');
    }
}