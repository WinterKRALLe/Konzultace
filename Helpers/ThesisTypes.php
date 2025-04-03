<?php

namespace Helpers;

class ThesisTypes
{
    public static $types = [
        1 => "Bakalářská",
        2 => "Diplomová"
    ];

    public static function getById($id)
    {
        return self::$types[$id] ?? "Neznámý";
    }
}
