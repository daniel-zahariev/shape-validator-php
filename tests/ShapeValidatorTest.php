<?php

use DanielZ\ShapeValidator\ShapeValidator;
use DanielZ\ShapeValidator\ShapeException;

test('Shape Validator: Correct values', function() {
    $data = [
        'string' => 'lorem ipsum',
        'string_null' => null,
        'number_string' => '123',
        'number_decimal' => 123,
        'number_binary' => 0b111,
        'number_octal' => 0123,
        'number_hexadecimal' => 0x123,
        'number_scientific' => 1e6,
        'number_floating' => 1.23,
        'number_null' => null,
        'bool_true' => true,
        'bool_false' => false,
    ];

    $shape = [
        'string' => 'nullable|string',
        'string_null' => 'nullable|string',
        'number_string' => 'numeric',
        'number_decimal' => 'numeric',
        'number_binary' => 'numeric',
        'number_octal' => 'numeric',
        'number_hexadecimal' => 'numeric',
        'number_scientific' => 'numeric',
        'number_floating' => 'numeric',
        'number_null' => 'nullable|numeric',
        'bool_true' => 'bool',
        'bool_false' => 'bool',
    ];

    $validator = new ShapeValidator($shape);
    expect($validator->validate($data))->toBe(true);
});

test('Shape Validator: Incorrect values', function() {
    $data = [
        'string_number' => 123,
        'string_bool' => true,
        'number_bool' => false,
        'bool_string' => 'true',
        'bool_number' => 0,
    ];

    $shape = [
        'string_number' => 'string',
        'string_bool' => 'string',
        'number_string' => 'numeric',
        'number_bool' => 'numeric',
        'bool_string' => 'bool',
        'bool_number' => 'bool',
    ];

    try {
        $validator = new ShapeValidator($shape);
        $validator->validate($data);
        expect(true)->toBeFalse(); // we shouldn't reach this line
    } catch (ShapeException $e) {
        $errors = $e->getValidationErrors();
        expect(count($errors))->toBe(5);
    }
});

test('Shape Validator: Valid set of rules', function () {
    try {
        $validator = new ShapeValidator(['field_a' => '']);
        $validator->validate([]);
        expect(true)->toBeFalse(); // we shouldn't reach this line
    } catch (ShapeException $e) {
        expect($e->getMessage())->toBe("Shape validation error - invalid rules for 'field_a'.");
    }

    try {
        $validator = new ShapeValidator(['field_a' => 'invalidRule']);
        $validator->validate([]);
        expect(true)->toBeFalse(); // we shouldn't reach this line
    } catch (ShapeException $e) {
        expect($e->getMessage())->toBe("Shape validation error - invalid rules for 'field_a'.");
    }
});

test('Shape Validator: Required & Not supported fields', function () {
    $data = [
        'field_a' => 'lorem ipsum',
        'field_c' => 'not supported',
        'field_d' => 123,
    ];

    $shape = [
        'field_a' => 'required',
        'field_b' => 'required',
        'field_d' => 'any',
    ];

    try {
        $validator = new ShapeValidator($shape);
        $validator->validate($data);
        expect(true)->toBeFalse(); // we shouldn't reach this line
    } catch (ShapeException $e) {
        $errors = $e->getValidationErrors();
        expect(count($errors))->toBe(2);
        expect($errors['field_c'])->toBe("Field 'field_c' is not supported.");
        expect($errors['field_b'])->toBe("Field 'field_b' is required.");
    }
});