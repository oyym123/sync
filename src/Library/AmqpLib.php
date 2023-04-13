<?php


namespace AsyncCenter\Library;

use AMQPConnectionException;
use AsyncCenter\Config;

class AmqpLib
{
    const TYPE_TOPIC = 'topic';
    const TYPE_DIRECT = 'direct';
    const TYPE_HEADERS = 'headers';
    const TYPE_FANOUT = 'fanout';

    /**
     * @var AMQPConnection
     */
    public static $ampqConnection;

    protected $prefetchCount = 10;

    /**
     * @var AMQPChannel[]
     */
    protected $channels = [];

    /**
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * @var int
     */
    private $port = 5672;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var string
     */
    private $vhost = '/';
    /**
     * @var string
     */
    private $exchange = ''; //交换机
    /**
     * @var string
     */
    private $routekey = ''; //路由key

    private static $instance = null;

    /**
     * constructor.
     *
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $vhost
     * @param null|mixed $exchange
     * @param mixed $timeout
     *
     * @throws
     */
    private function __construct($host, $port, $user, $pass, $vhost, $exchange, $timeout = 60, $heartbeat = 10)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->vhost = $vhost;
        $this->exchange = $exchange;
        if (empty($this->user)) {
            throw new \Exception("Parameter 'user' was not set for AMQP connection.");
        }
        if (empty(self::$ampqConnection)) {
            $connectionArr = [
                'host' => $this->host,
                'port' => $this->port,
                'login' => $this->user,
                'password' => $this->pass,
                'vhost' => $this->vhost,
                'connect_timeout' => $timeout,
                'heartbeat' => $heartbeat,
            ];
            $class = class_exists('AMQPConnection', false);
            if ($class) {
                self::$ampqConnection = new \AMQPConnection($connectionArr);
            } else {
                throw new \Exception('please install pecl amqp extension');
            }
        }
    }

    public static function getInstanceNew($conf = [])
    {
        //没有则使用默认配置
        $conf = $conf ?: Config::info('MQ_CONFIG');
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        $instance = new self($conf['mq_host'], $conf['mq_port'], $conf['mq_user'], $conf['mq_pass'], $conf['mq_vhost'], $conf['mq_exchange'] ?? '', $conf['timeout'] ?? null);
        if (!self::$ampqConnection->connect()) {
            throw new \Exception("Cannot connect to the broker!\n");
        }
        self::$instance = $instance;
        return self::$instance;
    }

    /**
     * 根据配置文件自动生成 交换机和队列 并且绑定
     * @param $config
     * @throws \AMQPChannelException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     */
    public static function setMqInfo($config = [], $exchangeName = '', $queueName = '', $type = AMQP_EX_TYPE_DIRECT)
    {
        $exchangeName = $exchangeName ?: ($config['mq_exchange'] ?? '');
        $queueName = $queueName ?: ($config['route_key'] ?? '');
        try {
            $ampq = AmqpLib::getInstanceNew($config);
            $channel = new \AMQPChannel($ampq::$ampqConnection);

            if (!empty($exchangeName)) {
                //创建交换机
                $ex = new \AMQPExchange($channel);
                $ex->setName($exchangeName);
                $ex->setFlags(AMQP_DURABLE); //设置持久化
                $ex->setType($type);
                $ex->declareExchange();
            }

            if (!empty($queueName)) {
                //创建队列
                $queue = new \AMQPQueue($channel);
                $queue->setName($queueName);
                $queue->setFlags(AMQP_DURABLE); //设置持久化
                $queue->declareQueue();
            }

            if (!empty($exchangeName) && !empty($queueName)) {
                //绑定交换机和队列
                $queue->bind($exchangeName, $queueName);
            }
        } catch (AMQPConnectionException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getInstance($host, $port, $user, $password, $vhost, $exchange, $timeout)
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        $instance = new self($host, $port, $user, $password, $vhost, $exchange, $timeout);
        if (!self::$ampqConnection->connect()) {
            throw new \Exception("Cannot connect to the broker!\n");
        }
        self::$instance = $instance;

        return self::$instance;
    }

    /**
     * Returns AMQP connection.
     *
     * @return AMQPConnection
     * @throws
     *
     */
    public function getConnection()
    {
        if (!self::$ampqConnection->connect()) {
            throw new \Exception("Cannot connect to the broker!\n");
        }

        return self::$ampqConnection;
    }

    /**
     * 发布消息.
     *
     * @param string $exchange 交换机
     * @param string $routingKey 路由key
     * @param string $message 消息
     *
     * @throws
     */
    public function publish($exchange = null, $routingKey = null, $message = null)
    {
        $channel = new \AMQPChannel(self::$ampqConnection);
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange);
        $channel->startTransaction(); //开始事务
        $ex->publish($message, $routingKey);
        $channel->commitTransaction(); //提交事务
        self::$ampqConnection->disconnect();
    }

    /**
     * 发布消息.
     *
     * @param string $exchange 交换机
     * @param string $routingKey 路由key
     * @param string $message 消息
     *
     * @throws
     */
    public function publishNew($exchange = null, $routingKey = null, $message = null)
    {
        $channel = new \AMQPChannel(self::$ampqConnection);
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange);
        $ex->publish($message, $routingKey);
    }

    /**
     * 消息推送 没有交换机 或队列会自动创建并且绑定  但都是直连模式
     * @param string $exchange 交换机
     * @param string $routingKey 路由key
     * @param string $message 消息
     * @throws
     */
    public static function sendMsg($exchange = null, $routingKey = null, $message = null)
    {
        self::setMqInfo([], $exchange, $routingKey);
        $channel = new \AMQPChannel(self::$ampqConnection);
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange);
        $ex->publish($message, $routingKey);
    }

    /**
     * 广播消息推送 没有交换机 会自动创建
     * @param string $exchange 交换机
     * @param string $routingKey 路由key
     * @param string $message 消息
     * @throws
     */
    public static function sendMsgFanout($exchange = '', $msg = '')
    {
        self::setMqInfo([], $exchange, '', AMQP_EX_TYPE_FANOUT);
        $channel = new \AMQPChannel(self::$ampqConnection);
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange);
        $ex->publish($msg, '', AMQP_MANDATORY, ['delivery_mode' => 2]);
    }

    /**
     * 订阅.
     *
     * @param mixed $callback
     * @param null|mixed $queueConf
     *
     * @throws
     */
    public function consume($callback, $queueConf = null)
    {
        if (null === self::$ampqConnection) {
            $this->getConnection();
        }
        //channel
        $channel = new \AMQPChannel(self::$ampqConnection);
        $channel->setPrefetchCount($queueConf['prefetchCount'] ?? $this->prefetchCount);
        $queue = new \AMQPQueue($channel);
        $queue->setName($queueConf['queueName'] ?? null);
        $queue->bind($this->exchange, $queueConf['routeKey'] ?? null);
        $queue->setFlags(AMQP_DURABLE);
        try {
            $queue->consume($callback);
        } catch (\AMQPQueueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * queue message length.
     *
     *
     * @param mixed $queueName
     *
     * @return int
     * @throws
     *
     */
    public function getMessageCount($queueName)
    {
        if (null === self::$ampqConnection) {
            $this->getConnection();
        }
        //在连接内创建一个通道
        $ch = new \AMQPChannel(self::$ampqConnection);
        $q = new \AMQPQueue($ch);
        $q->setName($queueName);
        $q->setFlags(AMQP_PASSIVE);
        $len = $q->declareQueue();
        self::$ampqConnection->disconnect();

        return $len ?? 0;
    }
}
