<?php


namespace Latus\Installer\Database;


class SeederRepository
{
    protected static array $seeders = [];

    public function register(array $classes)
    {
        if (empty(self::$seeders)) {
            self::$seeders = $classes;
            return;
        }

        foreach ($classes as $class) {
            if (!in_array($class, self::$seeders)) {
                self::$seeders[] = $class;
            }
        }
    }

    public function all(): array
    {
        return self::$seeders;
    }
}