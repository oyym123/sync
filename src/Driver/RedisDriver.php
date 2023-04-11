<?php



namespace AsyncCenter\Driver;

use AsyncCenter\Interfaces\MessageDriver;

class RedisDriver implements MessageDriver
{
	public function subscribe(array $callbackConf, array $queueConf = [])
	{
		// TODO: Implement subscribe() method.
	}

	public function unsubscribe()
	{
		// TODO: Implement unsubscribe() method.
	}

}
