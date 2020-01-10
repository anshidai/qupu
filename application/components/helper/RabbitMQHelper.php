<?php 

namespace app\components\helper;

/**
* amqp协议操作类
* 需先安装php_amqp扩展

* 使用方法
	$config = array(
		'host' => '127.0.0.1',
		'port' => 5672,
		'username' => 'guest',
		'password' => 'guest',
	);

	$exchangeName = 'c_linvo'; //交换机名称
	$queueName = 'q_linvo'; //队列名称
	$routeKey = 'r_key'; //路由名称

	//生产者代码
	$rabMQ = new RabbitMQ($config, $exchangeName, $queueName, $routeKey);
	for($i = 1; $i<100000; $i++) {
		$rabMQ->send("message $i ".date('Y-m-d H:i:s'));
	}


	//消费者代码
	$rabMQ = new RabbitMQ($config, $exchangeName, $queueName, $routeKey);
	$res = $rabMQ->run(array(new Task(), 'processMessage'), false);

	class Task
	{
		public function processMessage($envelope, $queue)
		{
			$msg = $envelope->getBody();
			$envelopeID = $envelope->getDeliveryTag();
			$queue->ack($envelopeID, $envelopeID);
		}
	}
*/
class RabbitMQHelper
{
	public $config = array();
	
	//交换机名称
	public $exchangeName = '';
	
	//队列名称
	public $queueName = '';
	
	//路由名称
	public $routeKey = '';
	
	//持久化 默认true
	public $durable = true;
	
	//自动删除
	public $autodelete = false;
	
	/**
	* 镜像
	* 镜像队列，打开后消息会在节点之间复制，有master和slave的概念
	*/
	public $mirror = false;
	
	private $_conn = null;
	private $_exchange = null;
	private $_channel = null;
	private $_queue = null;
	
	public function __construct($config = array(), $exchangeName = '', $queueName = '', $routeKey = '')
	{
		$this->setConfig($config);
		$this->exchangeName = $exchangeName;
		$this->queueName = $queueName;
		$this->routeKey = $routeKey;
	}
	
	
	/**
	* 初始化配置参数
	*/
	private function setConfig($config)
	{
		if(!is_array($config)) {
			throw new Exception('config is not array');
		}
		if(!($config['host'] && $config['port'] && $config['username'] && $config['password'])) {
			throw new Exception('config is array');
		}
		if(empty($config['vhost'])) {
			$config['vhost'] = '/';
		}
		
		$config['login'] = $config['username'];
		unset($config['username']);
		
		$this->config = $config;
	}
	
	
	/**
	* 设置是否持久化，默认true
	*/
	public function setDurable($durable)
	{
		$this->durable = $durable;
	}
	
	/**
	* 设置是否持久化，默认true
	*/
	public function setAutoDelete($autodelete) 
	{
        $this->autodelete = $autodelete;
    }
	
	/**
	* 设置是否镜像
	*/
	public function setMirror($mirror) 
	{
        $this->mirror = $mirror;
    }
	
	
	/**
	* 打开amqp链接
	*/
	private function open()
	{
		if(!$this->_conn) {
			try {
				$this->_conn = new AMQPConnection($this->config);
				$this->_conn->connect();
				$this->initConnection();
				
			}catch(AMQPConnectionException $ex) {
				throw new Exception('cannot connection rabbitmq',500); 
			}
		}
	}
	
	/**
	* rabbitmq连接不变
	* 重置交换机，队列，路由等配置
	*/
	public function reset($exchangeName, $queueName, $routeKey)
	{
		$this->exchangeName = $exchangeName;
		$this->queueName = $queueName;
		$this->routeKey = $routeKey;
		
		$this->initConnection();
	}
	
	/**
	* 初始化rabbit连接的相关配置
	*/
	private function initConnection()
	{
		if(empty($this->exchangeName) || empty($this->queueName) || empty($this->routeKey)) {
			throw new Exception('rabbitmq exchange_name or queueName or route_key is empty', 500);
		}
		
		$this->_channel = new AMQPChannel($this->_conn);
		$this->_exchange = new AMQPExchange($this->_channel);
		$this->_exchange->setName($this->exchangeName);
		
		//创建交换机
		$this->_exchange->setType(AMQP_EX_TYPE_DIRECT);
		if($this->durable) {
			$this->_exchange->setFlags(AMQP_DURABLE);
		}
		if($this->autodelete) {
			$this->_exchange->setFlags(AMQP_AUTODELETE);
		}
		$this->_exchange->declare();
		
		//创建队列
		$this->_queue = new AMQPQueue($this->_channel);
        $this->_queue->setName($this->queueName);
		if($this->durable) {
			$this->_queue->setFlags(AMQP_DURABLE);
		}
		if($this->autodelete) {
			$this->_queue->setFlags(AMQP_AUTODELETE);
		}
		if($this->mirror) {
			$this->_queue->setArgument(‘x-ha-policy‘, ‘all‘);
		}
		$this->_queue->declare();
		
		//绑定交换机与队列 并制定路由
		$this->_queue->bind($this->exchangeName, $this->routeKey);		
	}
	
	
	public function close()
	{
		if($this->_conn) {
			$this->_conn->disconnect();
		}
	}
	
	public function __sleep()
	{
		$this->close();
		return array_keys(get_object_vars($this));
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	
	/**
	* 生产者发送消息
	*/
	public function send($message)
	{
		$this->open();
		if(is_array($message)) {
			$message = json_encode($message);
		}else {
			$message = trim(strval($message));
		}
		
		return $this->_exchange->publish($message, $this->routeKey);
	}
	
	
	/*
    * 消费者
    * $callback 回调函数
    * $autoack 是否自动应答
    * 
    * 
	* $callback 回调函数格式
	   使用方法1(直接函数回调)：
			function processMessage($envelope, $queue) {
			   $msg = $envelope->getBody(); 
			   echo $msg."\n"; //处理消息
			   $queue->ack($envelope->getDeliveryTag());//手动应答
		   }
	   
		RabbitMQ()->run('processMessage', false);
	   
	   使用方法2(类方法回调)：
		class Task{
			function processMessage($envelope, $queue)
			{
				$msg = $envelope->getBody();
				$queue->ack($envelope->getDeliveryTag());
			}
		}
		$task = new Task();
		RabbitMQ->run(array($task,'processMessage'), false);
    */
	public function run($callback, $autoack = true)
	{
		$this->open();
		if(!$callback || !$this->_queue) {
			return false;
		}
		while(true) {
			if($autoack) {
				$this->_queue->consume($callback, AMQP_AUTOACK);
			}else {
				$this->_queue->consume($callback);
			}
		}
	}
	
}