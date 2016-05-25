<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 14:27
 */

namespace jakulov\SignUp\Storage;

use jakulov\SignUp\Exception\SignUpException;

/**
 * Class PdoStorage
 * @package jakulov\SignUp\Storage
 */
class PdoStorage
{
    /** @var string */
    protected $dns = 'mysql:dbname=signup;host=localhost';
    /** @var string */
    protected $username = 'root';
    /** @var string */
    protected $password = '';
    /** @var string  */
    protected $database = 'signup';
    /** @var array */
    protected $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];
    /** @var \PDO */
    protected $pdo;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if(isset($config['dns']) && $config['dns']) {
            $this->dns = $config['dns'];
        }
        if(isset($config['username']) && $config['username']) {
            $this->username = $config['username'];
        }
        if(isset($config['password']) && $config['password']) {
            $this->password = $config['password'];
        }
        if(isset($config['database']) && $config['database']) {
            $this->database = $config['database'];
        }
    }

    /**
     * @return \PDO
     * @throws SignUpException
     */
    public function getConnection()
    {
        if($this->pdo === null) {
            try {
                $this->pdo = new \PDO($this->dns, $this->username, $this->password, $this->options);
                $this->pdo->exec('USE '. $this->database);
            }
            catch(\PDOException $e) {
                throw new SignUpException('Unable to connect to database', $e->getCode(), $e);
            }
        }

        return $this->pdo;
    }

    /**
     * @param $sql
     * @return \PDOStatement
     * @throws SignUpException
     */
    public function query($sql)
    {
        return $this->getConnection()->query($sql);
    }
}