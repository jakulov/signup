<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 18:21
 */

namespace jakulov\SignUp\Http;

/**
 * Class JsonResponse
 * @package jakulov\SignUp\Http
 */
class JsonResponse extends Response
{
    /**
     * @param string $content
     * @param array $headers
     */
    public function __construct($content = '', array $headers = [])
    {
        if(!isset($headers['Content-type: application/json'])) {
            $headers['Content-type: application/json'] = 200;
        }

        parent::__construct(json_encode($content), $headers);
    }

}