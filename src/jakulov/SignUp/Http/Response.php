<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:45
 */

namespace jakulov\SignUp\Http;

/**
 * Class Response
 * @package jakulov\SignUp\Http
 */
class Response
{
    /** @var string */
    protected $content;
    /** @var array */
    protected $headers = [];
    /** @var array */
    protected static $cookies = [];

    /**
     * @param string $content
     * @param array $headers
     */
    public function __construct($content = '', array $headers = [])
    {
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return (string)$this->content;
    }

    /**
     * send http headers
     */
    public function sendHeaders()
    {
        foreach(self::$cookies as $name => $cookie) {
            setcookie($name, $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
        }
        foreach($this->headers as $header => $code) {
            header($header, true, $code);
        }
    }

    /**
     *
     */
    public static function clearCookies()
    {
        self::$cookies = [];
    }

    /**
     * @param $name
     * @param $value
     * @param null $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public static function setCookie($name, $value, $expire = null, $path = '/', $domain = null, $secure = false, $httpOnly = false)
    {
        self::$cookies[$name] = [
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
        ];
    }

    /**
     * @param $name
     */
    public static function unsetCookie($name)
    {
        self::setCookie($name, '', time() - 3600 * 60, '/');
    }
}