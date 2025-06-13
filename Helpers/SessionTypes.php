<?php

namespace Helpers;

class SessionTypes {
    public static $types = [
        1 => "Osobně",
        2 => "Telefonicky",
        3 => "E-mail",
        4 => "Online"
    ];

    public static function getById($id) {
        return self::$types[$id] ?? "Neznámý";
    }
}