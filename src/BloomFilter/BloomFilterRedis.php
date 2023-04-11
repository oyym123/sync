<?php

namespace AsyncCenter\BloomFilter;

/**
 * 使用redis实现的布隆过滤器
 */
abstract class BloomFilterRedis
{
    /**
     * 需要使用一个方法来定义bucket的名字
     */
    protected $bucket;

    protected $hashFunction;

    protected $Redis;

    protected $Hash;

    /**
     * BloomFilterRedis constructor.
     *
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        if (!$this->bucket || !$this->hashFunction) {
            throw new \Exception("需要定义bucket和hashFunction", 1);
        }
        if (empty($config)) {
            throw new \Exception('请添加redis配置');
        }
        $this->Hash = new BloomFilterHash;
        $this->Redis = new \Redis();
        try {
            $database = isset($config['database']) ? $config['database'] : 3;
            $timeout = isset($config['timeout']) ? $config['timeout'] : 5;
            $host = isset($config['host']) ? $config['host'] : '127.0.0.1';
            $port = isset($config['port']) ? $config['port'] : 6379;

            $this->Redis->connect($host, $port, $timeout);
            if (isset($config['password'])) {
                $this->Redis->auth($config['password']);
            }
            $this->Redis->select($database);
        } catch (\RedisException $e) {
            throw new \Exception('redis链接超时');
        } catch (\Exception $e) {
            throw new \Exception('redis 链接超时');
        }
    }

    /**
     * 添加到集合中
     */
    public function add($string)
    {
        if (is_array($string)) {
            exit('必须是字符串！');
        }

        $string = md5($string);
        $pipe = $this->Redis->multi();

        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->$function($string);
            $pipe->setBit($this->bucket, $hash, 1);
        }
        return $pipe->exec();
    }

    /**
     * 批量添加内容到集合中
     *
     * @param array $keys
     *
     * @return array
     */
    public function multiAdd(array $keys)
    {
        $result = [];
        if (count($keys) < 1) {
            return $result;
        }

        foreach ($keys as $key) {
            $result[] = $this->add($key);
        }
        return $result;
    }

    /**
     * 查询是否存在, 存在的一定会存在, 不存在有一定几率会误判
     *
     * @param string $string
     *
     * @return bool
     */
    public function exists(string $string)
    {
        $string = md5($string);
        $pipe = $this->Redis->multi();
        $len = strlen($string);
        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->$function($string, $len);
            $pipe = $pipe->getBit($this->bucket, $hash);
        }

        return in_array(1, $pipe->exec());
    }

    /**
     * 批量检查是否存在
     *
     * @param array $keys
     *
     * @return array
     */
    public function multiExists(array $keys)
    {
        $result = [];
        if (count($keys) < 1) {
            return $result;
        }
        foreach ($keys as $key) {
            $result[] = $this->exists($key);
        }
        return $result;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->Redis->close();
    }
}