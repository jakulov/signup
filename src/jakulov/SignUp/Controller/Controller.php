<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:53
 */

namespace jakulov\SignUp\Controller;

use jakulov\SignUp\Application;
use jakulov\SignUp\Container;
use jakulov\SignUp\Exception\SignUpException;
use jakulov\SignUp\Http\Response;
use jakulov\SignUp\Service\View;

/**
 * Class Controller
 * @package jakulov\SignUp\Controller
 */
abstract class Controller
{
    /** @var Application */
    protected $application;
    /** @var string */
    protected $viewDir = '/../View';
    /** @var string */
    protected $viewExtension = 'phtml';

    /**
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param $action
     * @param array $params
     * @return Response
     * @throws SignUpException
     */
    public function run($action, $params = [])
    {
        $action = $action .'Action';
        if(method_exists($this, $action)) {
            $response = call_user_func_array([$this, $action], $params);
            if($response instanceof Response) {
                return $response;
            }

            throw new SignUpException('Action: '. $action .' should return response instance');
        }

        throw new SignUpException('Unknown action: '. $action);
    }

    /**
     * @param $view
     * @param array $data
     * @param array $headers
     * @return Response
     * @throws SignUpException
     */
    protected function render($view, $data = [], $headers = [])
    {
        $data['flash_errors'] = $this->getFlash('error');
        $data['flash_success'] = $this->getFlash('success');
        $data['request'] = $this->getRequest();

        $file = realpath(__DIR__. $this->viewDir .'/'. $view .'.'. $this->viewExtension);

        return new Response(View::render($file, $data), $headers);
    }

    /**
     * @param $url
     * @param int $code
     * @return Response
     */
    protected function redirect($url, $code = 301)
    {
        return new Response('', ['Location: '. $url => $code]);
    }

    /**
     * @return \jakulov\SignUp\Http\Request
     */
    protected function getRequest()
    {
        return $this->application->getRequest();
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return Application::getConfig();
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return Container::getInstance();
    }

    /**
     * @param $type
     * @param $msg
     */
    protected function addFlash($type, $msg)
    {
        if(!isset($_SESSION)) {
            session_start();
        }
        $flashes = isset($_SESSION['flashes']) ? $_SESSION['flashes'] : [];
        if(!isset($flashes[$type])) {
            $flashes[$type] = [];
        }

        $flashes[$type][] = $msg;

        $_SESSION['flashes'] = $flashes;

    }

    /**
     * @param $type
     * @return array
     */
    protected function getFlash($type)
    {
        if(!isset($_SESSION)) {
            session_start();
        }

        $data = isset($_SESSION['flashes']) && isset($_SESSION['flashes'][$type]) ? $_SESSION['flashes'][$type] : [];
        if($data) {
            unset($_SESSION['flashes'][$type]);
        }

        return $data;

    }

    /**
     * @return \jakulov\SignUp\Model\User|null
     */
    protected function getAuthUser()
    {
        return Container::getInstance()->getUserService()->getAuthUser();
    }
}