<?php

namespace OpenSolid\OpenApiAssistantBundle\OpenApi;

class OpenApiSpec
{
    public static function getDataType(mixed $value): string
    {
        return match (true) {
            is_object($value) => 'object',
            is_array($value) => 'array',
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'number',
            default => 'string',
        };
    }

    public static function getDataFormat(mixed $value): ?string
    {
        return match (true) {
            is_int($value) => 'int32',
            is_float($value) => 'float',
            '********' === $value => 'password',
            is_string($value) => match(1) {
                preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) => 'date',
                preg_match('/^\d{4}(-\d\d(-\d\d(T\d\d:\d\d(:\d\d)?(\.\d+)?(([+-]\d\d:\d\d)|Z)?)?)?)?$/i', $value) => 'date-time',
                preg_match('/^[^@\s]+@[^@\s]+$/', $value) => 'email',
                preg_match('/^(?:\d{1,3}\.){3}\d{1,3}$/', $value) => 'ipv4',
                preg_match('/^(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/', $value) => 'ipv6',
                preg_match('#^(https?://)?[a-z0-9]+([\-.][a-z0-9]+)*\.[a-z]{2,5}(:\d{1,5})?(/.*)?$#i', $value) => 'uri',
                preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value) => 'uuid',
                default => null,
            },
            default => null,
        };
    }

    public static function getDataPattern(mixed $value): ?string
    {
        return is_string($value) && preg_match('#^/(.|\n)*/([imsux]*)$#', $value) ? trim($value, '/') : null;
    }
}
