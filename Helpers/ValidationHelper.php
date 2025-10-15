<?php
namespace Helpers;

use Types\ValueType;

class ValidationHelper
{
    public static function validateFields(array $rules, array $input): array
    {
        $out = [];
        foreach ($rules as $field => $type) {
            if (!array_key_exists($field, $input)) {
                throw new \InvalidArgumentException("Missing field: {$field}");
            }
            $value = $input[$field];

            $validatedValue = match ($type) {
                ValueType::STRING   => is_string($value) ? $value : throw new \InvalidArgumentException("Invalid string for {$field}"),
                ValueType::INT      => filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>PHP_INT_MIN]]) !== false
                                        ? (int)$value : throw new \InvalidArgumentException("Invalid int for {$field}"),
                ValueType::FLOAT    => filter_var($value, FILTER_VALIDATE_FLOAT) !== false
                                        ? (float)$value : throw new \InvalidArgumentException("Invalid float for {$field}"),
                ValueType::DATE     => self::validateDate($value),
                ValueType::EMAIL    => filter_var($value, FILTER_VALIDATE_EMAIL) ?: throw new \InvalidArgumentException("Invalid email"),
                ValueType::PASSWORD => (
                    is_string($value) &&
                    strlen($value) >= 8 &&
                    preg_match('/[A-Z]/', $value) &&
                    preg_match('/[a-z]/', $value) &&
                    preg_match('/\d/', $value) &&
                    preg_match('/[\W_]/', $value)
                ) ? $value : throw new \InvalidArgumentException("Invalid password"),
                default => throw new \InvalidArgumentException("Invalid type for {$field}"),
            };

            $out[$field] = is_string($validatedValue) ? trim($validatedValue) : $validatedValue;
        }
        return $out;
    }

    private static function validateDate($value): string
    {
        $ts = strtotime((string)$value);
        if ($ts === false) throw new \InvalidArgumentException("Invalid date");
        return date('Y-m-d', $ts);
    }
}

