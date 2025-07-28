<?php
namespace DevNoKage;

class Singleton {
    private static array $instances = [];

    public static function getInstance(): static {
        $calledClass = static::class;

        if (!isset(self::$instances[$calledClass])) {
            self::$instances[$calledClass] = new static();
        }

        return self::$instances[$calledClass];
    }
}

