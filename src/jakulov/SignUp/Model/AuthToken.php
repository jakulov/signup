<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 16:00
 */

namespace jakulov\SignUp\Model;

/**
 * Class AuthToken
 * @package jakulov\SignUp\Model
 */
class AuthToken extends Model
{
    const COOKIE_NAME = 'auth_token';
    /** @var string */
    public $token;
    /** @var \DateTime */
    public $createdAt;
    /** @var int */
    public $userId;
    /** @var string */
    public $ip;

    /**
     * @param bool $generateRandomToken
     * @param User $user
     */
    public function __construct($generateRandomToken = false, User $user = null)
    {
        $this->createdAt = new \DateTime('now');
        if($generateRandomToken) {
            $this->token = self::generateRandomToken();
        }
        if($user) {
            $this->userId = $user->id;
        }
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomToken($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}