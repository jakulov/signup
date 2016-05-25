<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:20
 */

namespace jakulov\SignUp;

use jakulov\SignUp\Controller\Controller;
use jakulov\SignUp\Exception\SignUpException;
use jakulov\SignUp\Http\Request;
use jakulov\SignUp\Http\Response;
use jakulov\SignUp\Service\Language;

/**
 * Class Application
 * @package jakulov\SignUp
 */
class Application
{
    const DEFAULT_LANGUAGE = 'RU_ru';
    /** @var array */
    protected static $config;
    /** @var Request */
    protected $request;
    /** @var bool */
    protected $handledException = false;

    /**
     * @return array
     */
    public static function getConfig()
    {
        if(self::$config === null) {
            $config = [];
            $configDir = realpath(__DIR__ .'/../../../config/');
            if(is_dir($configDir) && is_readable($configDir)) {
                $dh = opendir($configDir);
                while($f = readdir($dh)) {
                    if($f !== '.' && $f !== '..' && strpos($f, 'local.php') === false) {
                        $file = $configDir .'/'. $f;
                        if(is_file($file)) {
                            $fileConfig = require $file;
                            $config = array_replace_recursive($config, $fileConfig);
                        }
                    }
                }

                $file = $configDir .'/local.php';
                if(is_file($file)) {
                    $fileConfig = require $file;
                    $config = array_replace_recursive($config, $fileConfig);
                }
            }
            else {
                throw new \RuntimeException('Unable to find configuration directory');
            }

            self::$config = $config;
        }

        return self::$config;
    }

    /**
     * @param Request $request
     */
    public function run(Request $request)
    {
        $this->handledException = false;
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        try {
            $this->setRequest($request);
            list($controller, $action) = $this->getRouteHandler($this->request->getPath());

            $this->sendResponse($this->runController($controller, $action));
        }
        catch(SignUpException $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param Request $request
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
        Language::detectLang($this->request);
        Container::setRequest($this->request);
    }

    /**
     * @param int $errNo
     * @param string $errMessage
     * @param string $errFile
     * @param int $errLine
     * @throws \ErrorException
     */
    public function handleError($errNo, $errMessage, $errFile, $errLine)
    {
        throw new \ErrorException($errMessage, 0, $errNo, $errFile, $errLine);
    }

    /**
     * @param Response $response
     */
    protected function sendResponse(Response $response)
    {
        $response->sendHeaders();
        Response::clearCookies();
        echo $response->getContent();
    }

    /**
     * @param $path
     * @return array
     * @throws SignUpException
     */
    protected function getRouteHandler($path)
    {
        $routes = self::getConfig()['routes'];
        if (isset($routes[$path])) {
            $controllerClass = $routes[$path]['controller'];
            $action = $routes[$path]['action'];

            return [$controllerClass, $action];
        }

        throw new SignUpException('Page with url: ' . $path . ' was not found');
    }

    /**
     * @param $class
     * @param $action
     * @param array $params
     * @return Response
     * @throws SignUpException
     */
    protected function runController($class, $action, array $params = [])
    {
        /** @var Controller $controller */
        $controller = new $class;
        if($controller instanceof Controller) {
            $controller->setApplication($this);
            return $controller->run($action, $params);
        }

        throw new \RuntimeException('Controller class: '. $class .' should implements '. Controller::class);
    }

    /**
     * @param \Exception $exception
     */
    public function handleException(\Exception $exception)
    {
        if(!$this->handledException) {
            $this->handledException = true;
            list($controller, $action) = $this->getRouteHandler('/error');
            $this->sendResponse($this->runController($controller, $action, [$exception]));
            
            return;
        }

        var_dump($exception->getMessage());
        die('Internal server error #2');
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if($this->request === null) {
            $this->request = Request::createFromGlobals();
        }

        return $this->request;
    }
}