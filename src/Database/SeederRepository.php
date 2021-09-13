<?php


namespace Latus\Installer\Database;


class SeederRepository
{
    protected static array $seeders = [];
    protected static array $prioritisedSeeders = [];

    public function register(array $classes, bool $prioritise = false)
    {
        if (empty(self::$seeders)) {
            self::$seeders = $classes;
            return;
        }

        foreach ($classes as $class) {
            if (!in_array($class, self::$prioritisedSeeders) && !(in_array($class, self::$seeders))) {
                if ($prioritise || str_starts_with($class, 'Latus\Database\Seeders')) {
                    self::$prioritisedSeeders[] = $class;
                } else {
                    self::$seeders[] = $class;
                }
            }

        }
    }

    public function prioritised(): array
    {
        return self::$prioritisedSeeders;
    }

    public function all(): array
    {
        return self::$prioritisedSeeders + self::$seeders;
    }
}