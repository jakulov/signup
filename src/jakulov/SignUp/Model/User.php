<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 15:57
 */

namespace jakulov\SignUp\Model;

/**
 * Class User
 * @package jakulov\SignUp\Model
 */
class User extends Model
{
    const RESET_TOKEN_PARAM = 'hash';
    const SESSION_PARAM = 'auth_user';
    const SESSION_IP_KEY = 'auth_user_ip';

    /** @var string */
    public $email;
    /** @var string */
    public $phone;
    /** @var string */
    public $name;
    /** @var string */
    public $photo;
    /** @var string */
    public $resetToken;
    /** @var string */
    public $password;
    /** @var string */
    public $about;
}