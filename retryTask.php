<?php
include_once "src/Config.php";
include_once "src/Service/Retry.php";
include_once "src/Service/Utils.php";
include_once "src/Library/RedisLib.php";
include_once "src/Library/AmqpLib.php";
include_once "src/Service/RetryType.php";

use Pupilcp\Service\Retry;

try {
    (new Retry())->start();
} catch (Exception $e) {

}