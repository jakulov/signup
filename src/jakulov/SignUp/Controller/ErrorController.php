<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 15:09
 */

namespace jakulov\SignUp\Controller;

/**
 * Class ErrorController
 * @package jakulov\SignUp\Controller
 */
class ErrorController extends Controller
{
    /**
     * @param \Exception $exception
     * @return \jakulov\SignUp\Http\Response
     */
    protected function errorAction(\Exception $exception)
    {
        $showRealException = $this->getRequest()->getIp() === '127.0.0.1';

        return $this->render('error', [
            'showRealException' => $showRealException,
            'exception' => $exception
        ]);
    }
}