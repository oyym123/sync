<?php

namespace AsyncCenter;

class Config
{
    public static function info($key)
    {
        $config = require getenv('CONFIG_FILE_PATH');
        return $config[$key] ?? '';
    }
}