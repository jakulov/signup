<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 15:10
 */

namespace jakulov\SignUp\Model;

use jakulov\SignUp\Container;
use jakulov\SignUp\Exception\SignUpException;

/**
 * Class Model
 * @package jakulov\SignUp\Model
 */
abstract class Model
{
    /** @var string */
    protected static $tableName;
    /** @var int */
    public $id;

    protected $restrictions = [];

    /**
     * @return string
     */
    public static function getTableName()
    {
        if(static::$tableName) {
            return static::$tableName;
        }

        $class = explode('\\', static::class);

        return lcfirst(end($class));
    }

    /**
     * @return bool
     * @throws SignUpException
     */
    public function save()
    {
        $data = json_decode(json_encode($this), true);
        $set = [];
        $connection = self::getConnection();
        foreach($data as $key => $value) {
            $set[] = $key . str_replace('IS NULL', '= NULL', self::getFieldQuotedValue($value, $connection));
        }

        $query = 'INSERT INTO '. self::getTableName() .' SET '. join(', ', $set);
        if($this->id) {
            $query = 'UPDATE '. self::getTableName() .' SET '. join(', ', $set) .' WHERE id = '. $this->id;
        }

        try {
            $result = $connection->query($query);
        }
        catch(\PDOException $e) {
            throw new SignUpException('Error in sql query. Unable to save object', $e->getCode(), $e);
        }

        $lastInsertId = null;
        if(!$this->id) {
            $lastInsertId = $connection->lastInsertId();
        }
        if(
            ($result && $this->id && $result->rowCount()) ||
            ($result && !$this->id && $lastInsertId)
        ) {
            $this->id = $lastInsertId;

            return true;
        }

        return false;
    }

    /**
     * @param $value
     * @param \PDO $connection
     * @return string
     */
    protected static function getFieldQuotedValue($value, \PDO $connection)
    {
        $quotedValue = $value;
        if(is_string($quotedValue)) {
            $quotedValue = ' = ' . $connection->quote($quotedValue, \PDO::PARAM_STR);
        }
        elseif(is_int($quotedValue) || is_float($quotedValue)) {
            $quotedValue = ' = ' . $connection->quote($quotedValue, \PDO::PARAM_INT);
        }
        elseif($quotedValue === null) {
            $quotedValue = ' IS NULL';
        }
        elseif($quotedValue instanceof \DateTime) {
            $quotedValue = ' = ' . $connection->quote($quotedValue->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        }
        elseif(is_array($quotedValue) && isset($quotedValue['date']) && isset($quotedValue['timezone_type'])) {
            $quotedValue = ' = ' . $connection->quote($quotedValue['date'], \PDO::PARAM_STR);
        }
        else {
            $quotedValue = ' = ' . $connection->quote(serialize($quotedValue), \PDO::PARAM_STR);
        }

        return $quotedValue;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function load($data = [])
    {
        foreach($data as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    /**
     * @param $id
     * @return $this
     * @throws SignUpException
     */
    public static function find($id)
    {
        $sql = 'SELECT * FROM '. self::getTableName() .' WHERE id = '. (int)$id;
        try {
            $result = self::getConnection()->query($sql);
        }
        catch(\PDOException $e) {
            throw new SignUpException('Error in sql query. Unable to find object', $e->getCode(), $e);
        }
        if($result) {
            $data = $result->fetch();
            if($data) {
                $obj = new static();

                return $obj->load($data);
            }
        }

        return null;
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return $this[]|null
     * @throws SignUpException
     */
    public static function findBy($where = [], $orderBy = [], $limit = null, $offset = null)
    {
        $connection = self::getConnection();
        $sqlWhere = [];
        foreach($where as $key => $value) {
            $sqlWhere[] = $key . self::getFieldQuotedValue($value, $connection);
        }
        $sqlOrder = [];
        foreach($orderBy as $key => $sort) {
            $sqlOrder[] = $key .' '. $sort;
        }
        $sqlLimit = '';
        if($limit !== null) {
            $sqlLimit .= ' LIMIT '. (int)$limit;
        }
        if($offset !== null) {
            $sqlLimit .= ' OFFSET '. (int)$offset;
        }

        $sql =
            'SELECT * FROM '. self::getTableName() .
            ($sqlWhere ? ' WHERE '. join(' AND ', $sqlWhere) : '') .
            ($sqlOrder ? 'ORDER BY '. join(', ', $sqlOrder) : '') .
            ($sqlLimit ? $sqlLimit : '');

        return self::fetchAll($sql);
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @return $this
     */
    public static function findOneBy($where = [], $orderBy = [])
    {
        $objects = self::findBy($where, $orderBy);
        if($objects) {
            return $objects[0];
        }

        return null;
    }

    /**
     * @param $sql
     * @return $this|null
     * @throws SignUpException
     */
    public static function fetchOne($sql)
    {
        try {
            $result = self::getConnection()->query($sql);
        }
        catch(\PDOException $e) {
            throw new SignUpException('Error in sql query. Unable to fetch object', $e->getCode(), $e);
        }
        if($result) {
            $data = $result->fetch();
            if($data) {
                $obj = new static();

                return $obj->load($data);
            }
        }

        return null;
    }

    /**
     * @param $sql
     * @return $this[]|null
     * @throws SignUpException
     */
    public static function fetchAll($sql)
    {
        try {
            $result = self::getConnection()->query($sql);
        }
        catch(\PDOException $e) {
            throw new SignUpException('Error in sql query. Unable to fetch objects', 0, $e);
        }
        if($result) {
            $return = [];
            while($data = $result->fetch()) {
                $obj = new static();

                $return[] = $obj->load($data);
            }

            return $return;
        }

        return null;
    }

    /**
     * @return \PDO
     * @throws \jakulov\SignUp\Exception\SignUpException
     */
    protected static function getConnection()
    {
        return Container::getInstance()->getPdoStorage()->getConnection();
    }
}