<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 18:32
 */

namespace jakulov\SignUp\Validator;

use jakulov\SignUp\Service\Language;

/**
 * Class ResetValidator
 * @package jakulov\SignUp\Validator
 */
class ResetValidator extends Validator
{
    /**
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        $rules = [
            'password' => self::FILTER_NOT_EMPTY,
            'password2' => [
                self::FILTER_NOT_EMPTY,
                function($value, $field, $data) {
                    $password = isset($data['password']) ? $data['password'] : '';
                    if($password !== $value) {
                        return Language::get(VALUES_DOES_NOT_MATCH);
                    }

                    return '';
                }
            ],
        ];

        parent::__construct($rules);
    }

    /**
     * @return array
     */
    public function getValidationJsSchema()
    {
        return [
            '"password"' => '"'. self::FILTER_NOT_EMPTY .'"',
            '"password2"' => [
                '"'. self::FILTER_NOT_EMPTY .'"',
                "function(field) {
                    field = $(field.currentTarget);
                    var value = $(field).val();
                    var password = $('[name=password]').val();
                    if(password != value) {
                        Validator.showError($(field).attr('name'), Validator.messages['VALUES_DOES_NOT_MATCH']);
                    }
                    else {
                        Validator.hideError($(field).attr('name'));
                    }
                }"
            ],
        ];
    }


}