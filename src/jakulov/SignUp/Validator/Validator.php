<?php
/**
 * Created by PhpStorm.
 * User: yakov
 * Date: 25.05.16
 * Time: 17:01
 */

namespace jakulov\SignUp\Validator;

use jakulov\SignUp\Service\Language;

/**
 * Class AbstractValidator
 * @package jakulov\SignUp\Validator
 */
class Validator
{
    const FILTER_NOT_EMPTY = 'filterNotEmpty';
    const FILTER_VALID_EMAIL = 'filterValidEmail';
    /** @var array */
    protected $rules = [];
    /** @var array */
    protected $validationErrors = [];

    /**
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validate($data = [])
    {
        $this->validationErrors = [];
        $valid = true;

        foreach($data as $field => $value) {
            if(isset($this->rules[$field])) {
                $filters = $this->rules[$field];
                if(!is_array($filters)) {
                    $filters = [$filters];
                }

                foreach($filters as $filter) {
                    if(is_callable($filter)) {
                        $error = call_user_func_array($filter, [$value, $field, $data]);
                    }
                    else {
                        $error = call_user_func_array([$this, $filter], [$value, $field, $data]);
                    }
                    if($error) {
                        $valid = false;
                        if(!isset($this->validationErrors[$field])) {
                            $this->validationErrors[$field] = [];
                        }
                        $this->validationErrors[$field][] = $error;
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $messages = [];
        foreach($this->validationErrors as $field => $errors) {
            $messages[$field] = is_array($errors) ? join(', ', $errors) : $errors;
        }

        return $messages;
    }

    /**
     * @return array
     */
    public function getValidationJsSchema()
    {
        $rules = [];
        foreach($this->rules as $field => $filters) {
            if(is_array($filters)) {
                $rules['"'. $field .'"'] = [];
                foreach($filters as $filter) {
                    $rules['"'. $field .'"'][] = '"'. $filter .'"';
                }
            }
            else {
                $rules['"'. $field .'"'] = ['"'. $filters .'"'];
            }
        }

        return $rules;
    }

    /**
     * @param $value
     * @return string
     */
    protected function filterValidEmail($value)
    {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return Language::get(NOT_VALID_EMAIL);
        }

        return '';
    }

    /**
     * @param $value
     * @return string
     */
    protected function filterNotEmpty($value)
    {
        if(trim($value) === '') {
            return Language::get(FIELD_IS_REQUIRED);
        }

        return '';
    }

    /**
     * @param string $varName
     * @return string
     */
    public function getJsRules($varName = 'rules')
    {
        $result = '';
        foreach($this->getValidationJsSchema() as $field => $filters) {
            if(!is_array($filters)) {
                $filters = [$filters];
            }
            $result .= $varName. '['. $field .'] = []'. PHP_EOL;
            foreach ($filters as $filter) {
                $result .= $varName. '['. $field .'].push('. $filter .');'. PHP_EOL;
            }
        }

        return $result;
    }
}