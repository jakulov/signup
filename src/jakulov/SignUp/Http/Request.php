<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:44
 */

namespace jakulov\SignUp\Http;

/**
 * Class Request
 * @package jakulov\SignUp\Http
 */
class Request
{
    /** @var array */
    protected $query;
    /** @var array */
    protected $request;
    /** @var array */
    protected $server;
    /** @var array */
    protected $cookie;
    /** @var string */
    protected $path;
    /** @var string */
    protected $method;
    /** @var string */
    protected $ip;
    /** @var string */
    protected $host;

    /**
     * @return Request
     */
    public static function createFromGlobals()
    {
        $obj = new self($_GET, $_POST, $_SERVER, $_COOKIE);

        return $obj;
    }

    /**
     * @param array $get
     * @param array $post
     * @param array $server
     * @param array $cookie
     * @return Request
     */
    public static function create(array $get, array $post, array $server, array $cookie)
    {
        return new self($get, $post, $server, $cookie);
    }

    /**
     * @param array $get
     * @param array $post
     * @param array $server
     * @param array $cookie
     */
    private function __construct(array $get, array $post, array $server, array $cookie)
    {
        $this->query = $get;
        $this->request = $post;
        $this->server = $server;
        $this->cookie = $cookie;

        $this->path = isset($this->server['REQUEST_URI']) ? $this->server['REQUEST_URI'] : '/';
        $this->path = explode('?', $this->path)[0];

        $this->method = isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : 'GET';

        $this->ip = isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : '127.0.0.1';

        $this->host = isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : '127.0.0.1';
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getServer($key = null)
    {
        if($key === null) {
            return $this->server;
        }

        return isset($this->server[$key]) ? $this->server[$key] : null;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRequest($key = null)
    {
        if($key === null) {
            return $this->request;
        }

        return isset($this->request[$key]) ? $this->request[$key] : null;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getQuery($key = null)
    {
        if($key === null) {
            return $this->query;
        }

        return isset($this->query[$key]) ? $this->query[$key] : null;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getCookie($key = null)
    {
        if($key === null) {
            return $this->cookie;
        }

        return isset($this->cookie[$key]) ? $this->cookie[$key] : null;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }
}