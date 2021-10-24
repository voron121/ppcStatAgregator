<?php

namespace PPCSoft;

class Registry
{

    protected static $storage = [];

    public static function set(string $key, $value)
    {
        self::$storage[$key] = $value;
    }

    public static function get(string $key)
    {
        if (!array_key_exists($key, self::$storage)) {
            return null;
        }
        return self::$storage[$key];
    }
}