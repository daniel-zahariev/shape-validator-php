## Simple shape validator

Define _**all**_ possible fields for a shape and set rules for them. 
Any field outside of the defined set will trigger an error.


### Usage:

```php
try {
    $shape = [
        'name' => 'required|string',
        'phone' => 'required|numeric',
        'notes' => 'string',
        'data' => 'any',
    ];
    $shape_validator = new ShapeValidator($shape);
    $shape_validator->validate([
        'name' => 'Hello, world!', // valid
        'extra' => null, // this will trigger an error    
    ]);
} catch(ShapeException $exception) {
    // getValidationErrors() returns an array like ['field name' => 'error message']
    log($exception->getValidationErrors());
}

```


### Available rules:
- `required` - the field must be present
- `nullable` - the field can take null values
- `string` - if the field value is set it must be a valid string
- `numeric`- if the field value is set it must have valid numeric value
- `bool` / `boolean` - if the field value is set it must be a valid boolean value (validation is using type comparison `===`)
- `any` - allows any values