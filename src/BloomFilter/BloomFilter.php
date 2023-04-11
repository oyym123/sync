<?php

namespace AsyncCenter\BloomFilter;

use AsyncCenter\Config;
use Exception;

class BloomFilter extends BloomFilterRedis
{
    /**
     * 表示判断重复内容的过滤器
     * @var string
     */
    protected $bucket = 'bloomfilter';

    protected $hashFunction = array('PJWHash', 'BKDRHash', 'SDBMHash');

    /**
     * Client constructor.
     *
     * @param string $bucket
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct($bucket, $config = [])
    {
        $config = $config ?: Config::info('REDIS_CONFIG');
        $config['database'] = 3;
        $this->bucket = $bucket;
        parent::__construct($config);
    }

}