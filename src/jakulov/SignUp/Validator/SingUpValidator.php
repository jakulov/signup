<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 17:33
 */

namespace jakulov\SignUp\Validator;

use jakulov\SignUp\Model\User;
use jakulov\SignUp\Service\Language;

/**
 * Class SingUpValidator
 * @package jakulov\SignUp\Validator
 */
class SingUpValidator extends Validator
{
    protected $rules = [

    ];

    /**
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        $rules = [
            'email' => [
                self::FILTER_VALID_EMAIL,
                function($value) {
                    $hasUser = User::findOneBy(['email' => trim($value)]);
                    if($hasUser) {
                        return Language::get(EMAIL_IS_TAKEN);
                    }

                    return '';
                }
            ],
            'phone' => self::FILTER_NOT_EMPTY,
            'name' => self::FILTER_NOT_EMPTY,
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
            '"email"' => [
                '"'. self::FILTER_VALID_EMAIL .'"',
                "function(field) {
                    field = $(field.currentTarget);
                    var value = $(field).val();
                    $.getJSON('/sign/up/validateEmail', {email: value}, function(data) {
                        if(data && data.ok) {
                            Validator.showError($(field).attr('name'), Validator.messages['EMAIL_IS_TAKEN']);
                        }
                        else {
                            if(!Validator.getField($(field).attr('name')).closest('.form-group').hasClass('has-error')) {
                                Validator.hideError($(field).attr('name'));
                            }
                        }
                    });
                }"
            ],
            '"phone"' => '"'. self::FILTER_NOT_EMPTY .'"',
            '"name"' => '"'. self::FILTER_NOT_EMPTY .'"',
            '"password"' => '"'. self::FILTER_NOT_EMPTY .'"',
            '"password2"' => [
                '"'. self::FILTER_NOT_EMPTY .'"',
                "function(field) {
                    field = $(field.currentTarget);
                    var value = $(field).val();
                    var password = $('[name=password]').val();
                    if(password !== value) {
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