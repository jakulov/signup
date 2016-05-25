<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 16:10
 */

namespace jakulov\SignUp\Service;

use jakulov\SignUp\Exception\SignUpException;

/**
 * Class View
 * @package jakulov\SignUp\Service
 */
class View
{
    /**
     * @param $file
     * @param array $data
     * @return string
     * @throws SignUpException
     */
    public static function render($file, $data = [])
    {
        if(file_exists($file) && is_readable($file)) {
            ob_start();
            extract($data);

            require $file;

            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        throw new SignUpException('Unable to find view file: '. $file);
    }

    /**
     * @param $string
     * @param bool $return
     * @return string|void
     */
    public static function out($string, $return = false)
    {
        if($return) {
            return htmlentities($string, ENT_COMPAT, 'utf-8');
        }

        echo htmlentities($string, ENT_COMPAT, 'utf-8');
        return;
    }

    /**
     * @param $data
     * @param $param
     * @return void
     */
    public static function param($data, $param)
    {
        if(isset($data[$param])) {
            return self::out($data[$param]);
        }

        echo '';
        return;
    }
}