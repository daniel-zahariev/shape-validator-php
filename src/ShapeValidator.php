<?php

namespace DanielZ\ShapeValidator;

/**
 * Class ShapeValidator
 * @package DanielZ\ShapeValidator
 */
class ShapeValidator
{
    /**
     * Available rules sorted by priority
     */
    const AVAILABLE_RULES = [
        'required',
        'nullable',
        'bool',
        'boolean',
        'numeric',
        'string',
        'any',
    ];

    /**
     * @var array
     */
    protected $shape;

    /**
     * @var array
     */
    protected $allowed_fields;

    /**
     * @var array
     */
    protected $nullable_fields = [];

    /**
     * @param array $shape
     * @param array $data
     */
    public function __construct($shape = [])
    {
        $this->shape = $this->transformShape($shape);
        $this->allowed_fields = array_keys($this->shape);
    }

    /**
     * @param array $shape
     * @return array
     */
    public function transformShape($shape)
    {
        $new_shape = [];

        foreach($shape as $field => $rules) {
            if (!is_array($rules)) {
                $rules = array_filter(explode('|', $rules));
            }
            if (count($rules) == 0) {
                throw new ShapeException("Shape validation error - invalid rules for '{$field}'.", 400);
            }
            if (count($rules) != count(array_intersect($rules, self::AVAILABLE_RULES))) {
                throw new ShapeException("Shape validation error - invalid rules for '{$field}'.", 400);
            }

            $new_shape[$field] = $this->transformRules($field, $rules);
        }

        return $new_shape;
    }

    /**
     * @param string $field
     * @param array $rules
     * @return array
     */
    protected function transformRules($field, $rules)
    {
        $this->nullable_fields[$field] = false;

        $sorted_rules = [];
        foreach($rules as $rule) {
            if ($rule == 'any') continue;
            if ($rule == 'nullable') {
                $this->nullable_fields[$field] = true;
                continue;
            }
            $idx = array_search($rule, self::AVAILABLE_RULES);
            $sorted_rules[$idx] = $rule;
        }

        return array_values($sorted_rules);
    }

    /**
     * @param array $data
     * @return bool
     * @throws ShapeException
     */
    public function validate($data)
    {
        $data = (array)$data;
        $errors = [];

        $extra_keys = array_diff(array_keys($data), $this->allowed_fields);
        foreach($extra_keys as $extra_key) {
            $errors[$extra_key] = "Field '{$extra_key}' is not supported.";
        }

        foreach($this->shape as $field => $rules) {
            $is_set = array_key_exists($field, $data);
            $value = $data[$field] ?? null;
            $error = '';

            foreach ($rules as $rule) {
                $error = $this->validateValue($field, $value, $rule, $is_set);
                if ($error != '') break;
            }

            if ($error != '') {
                $errors[$field] = $error;
            }
        }

        if (!empty($errors)) {
            throw new ShapeException("Shape validation error", 400, null, $errors);
        }

        return true;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @param bool $is_set
     * @return string
     */
    protected function validateValue($field, $value, $rule, $is_set)
    {
        $error = '';
        $check_for_type = $is_set && (!is_null($value) || !(is_null($value) && $this->nullable_fields[$field]));

        switch($rule) {
            case 'required':
                if (!$is_set) {
                    $error = "Field '{$field}' is required.";
                }
                break;
            case 'bool':
            case 'boolean':
                if ($check_for_type && ($value !== true && $value !== false)) {
                    $error = "Field '{$field}' must be a boolean value.";
                }
                break;
            case 'string':
                if ($check_for_type && !is_string($value)) {
                    $error = "Field '{$field}' must be a string.";
                }
                break;
            case 'numeric':
                if ($check_for_type && !is_numeric($value)) {
                    $error = "Field '{$field}' must have numeric value.";
                }
                break;
        }

        return $error;
    }
}