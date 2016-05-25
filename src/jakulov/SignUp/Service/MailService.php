<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 18:43
 */

namespace jakulov\SignUp\Service;

/**
 * Class MailService
 * @package jakulov\SignUp\Service
 */
class MailService
{
    /** @var string */
    protected static $from;

    /**
     * @param string $from
     */
    public static function setFrom($from)
    {
        self::$from = $from;
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @param null $headers
     * @return bool
     */
    public static function send($to, $subject, $body, $headers = null)
    {
        return mail($to, $subject, $body, $headers);
    }
}