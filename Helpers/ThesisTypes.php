<?php

namespace Helpers;

class ThesisTypes
{
    public static $types = [
        1 => "Bakalářská",
        2 => "Diplomová"
    ];

    public static $typesShort = [
        1 => "BP",
        2 => "DP"
    ];

    public static function getById($id)
    {
        return self::$types[$id] ?? "Neznámý";
    }
    public static function getByIdShort($id)
    {
        return self::$typesShort[$id] ?? null;
    }
}
