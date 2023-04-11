<?php

namespace AsyncCenter;

use AsyncCenter\Service\Retry;
use AsyncCenter\Service\Utils;
use Exception;

class RetryTask
{
    public function start()
    {
        try {
            (new Retry())->start();
        } catch (Exception $e) {
            Utils::writeLog($e->getMessage(), 'RETRY_ERROR.log');
        }
    }
}

