<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:37
 */

namespace jakulov\SignUp;

use jakulov\SignUp\Http\Request;
use jakulov\SignUp\Service\UserService;
use jakulov\SignUp\Storage\PdoStorage;

/**
 * Class Container
 * @package jakulov\SignUp
 */
class Container
{
    /** @var Request */
    protected static $request;
    /** @var PdoStorage */
    protected $pdoStorage;
    /** @var UserService */
    protected $userService;
    /** @var Container */
    protected static $instance;

    private function __clone() {}

    private function __construct(){}

    /**
     * @param Request $request
     */
    public static function setRequest(Request $request)
    {
        self::$request = $request;
    }

    /**
     * @return Container
     */
    public static function getInstance()
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return PdoStorage
     */
    public function getPdoStorage()
    {
        if($this->pdoStorage === null) {
            $this->pdoStorage = new PdoStorage(isset(Application::getConfig()['pdo']) ? Application::getConfig()['pdo'] : []);
        }

        return $this->pdoStorage;
    }

    /**
     * @return UserService
     */
    public function getUserService()
    {
        if($this->userService === null) {
            $this->userService = new UserService();
            $this->userService->setRequest(self::$request);
        }

        return $this->userService;
    }
}