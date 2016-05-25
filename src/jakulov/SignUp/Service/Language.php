<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 17:06
 */

namespace jakulov\SignUp\Service;

use jakulov\SignUp\Application;
use jakulov\SignUp\Exception\SignUpException;
use jakulov\SignUp\Http\Request;
use jakulov\SignUp\Http\Response;

/**
 * Class Language
 * @package jakulov\SignUp\Service
 */
class Language
{
    /** @var array */
    protected static $locale;
    /** @var string */
    protected static $lang;

    /**
     * @param mixed $lang
     */
    public static function setLang($lang)
    {
        $allowLang = Application::getConfig()['lang']['enabled'];
        if(in_array($lang, $allowLang)) {
            self::$lang = $lang;
        }
        else {
            self::$lang = Application::DEFAULT_LANGUAGE;
        }

        Response::setCookie('lang', self::$lang, time() + 3600 * 60 * 30, '/');
    }

    /**
     * @param Request $request
     */
    public static function detectLang(Request $request)
    {
        if($request->getQuery('lang')) {
            self::setLang($request->getQuery('lang'));
        }
        elseif($request->getCookie('lang')) {
            self::setLang($request->getCookie('lang'));
        }
    }

    /**
     * @return string
     */
    public static function getLang()
    {
        return self::$lang ? self::$lang : Application::DEFAULT_LANGUAGE;
    }

    /**
     * @param string $msg
     * @param string $lang
     * @return string
     */
    public static function get($msg, $lang = null)
    {
        if($lang === null) {
            $lang = self::getLang();
        }
        if(!isset(self::$locale[$lang])) {
            self::loadLocale($lang);
        }

        return isset(self::$locale[$lang][$msg]) ? self::$locale[$lang][$msg] : '';
    }

    /**
     * @param $lang
     * @throws SignUpException
     */
    protected static function loadLocale($lang)
    {
        $localeFile = realpath(__DIR__ .'/../../../../locale/'. $lang .'/locale.php');
        if(file_exists($localeFile) && is_readable($localeFile)) {
            self::$locale[$lang] = require $localeFile;
            return;
        }

        throw new SignUpException('Unable to find locale file: '. $localeFile);
    }

    /**
     * @param null $lang
     * @return string
     * @throws SignUpException
     */
    public static function jsMessages($lang = null)
    {
        if($lang === null) {
            $lang = self::getLang();
        }
        if(!isset(self::$locale[$lang])) {
            self::loadLocale($lang);
        }

        return json_encode(self::$locale[$lang]);
    }
}