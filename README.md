# swoft-tracker

###### 该库主要通过设置traceid，spanid，来实现日志链路记录，保证同一请求的链路traceid一致；
###### 并且增加`RedisHandler`可以将日志直接记录到redis中（协程方式）,后续可以通过ELK同步日志；另外通过日志配置增加version、interface、method、params、cost(时间消耗)的日志记录
```
{"messages":"trace[HttpTraceMiddleware.php:53] HTTP END","level":200,"level_name":"info","channel":"swoft","event":"request","tid":14,"cid":14,"traceid":"5dcb6271b889c","spanid":"","version":"","interface":"\/user\/info","method":"GET","params":[],"appInfo":{"env":"local","name":null,"version":null},"cost":"231.90ms","datetime":"2019-11-13 09:54:57.855"}

```
---

## 要求
该库的日志记录级别为`info`,请打开相关日志级别记录。

## 用法

```
composer require nango/swoft-tracker
```
## 配置

在`bean.php`文件中进行配置；

如果应用只提供RPC服务，则需要在bean.php中添加，RPC中间件来记录相关日志；如下：


```
//RPC中间件
'serviceDispatcher' => [
    'middlewares'      => [
        SwoftTracker\Middleware\RpcTraceMiddleware::class
    ],
],
```

如果应用需要对外提供HTTP服务，并且内部需要通过RPC调用其他微服务的话，则需要在bean.php中进行以下配置：

首先增加HTTP中间件：

```
'httpDispatcher'    => [
    'middlewares'      => [
        SwoftTracker\Middleware\HttpTraceMiddleware::class
    ],
],
```

另外，RPC CLient增加`extender`配置：

```
'user'              => [
    'class'   => ServiceClient::class,
    'host'    => '192.168.152.55',
        ... ...
    'packet'  => bean('rpcClientPacket'),
    'extender' => bean(SwoftTracker\Middleware\TraceExtender::class)
],
```
以上配置就可以实现，多服务之间调用时的日志链路追踪。

## logger配置
使用`RedisHandler`,可以指定连接池，将业务redis库和日志库隔离开；简单配置如下：

```
    'applicationHandler' => [
        'class'     => SwoftTracker\Middleware\RedisHandler::class,
        'redisPool' => 'redis.log-pool',
        'levels'    => 'info,error,warning',
    ],

    //可以在logger配置中增加items选项来设置日志记录消耗时间，RPC请求方法等；
    'logger'             => [
        'flushRequest' => false,
        'enable'       => true,
        'json'         => true,
        'items'        => [
            'traceid',
            'spanid',
            'version',
            'interface',
            'method',
            'params',
            'appInfo',
            'cost'
        ],
    ],
    
```

## 非swoft框架 RPC调用

如果需要在非swoft框架中通过RPC调用swoft的微服务的话，需要在`ext`中增加`traceid`参数，如下：

```
$traceid = uniqid();
$req = [
        "jsonrpc" => '2.0',
        "method" => sprintf("%s::%s::%s", $version, $class, $method),
        'params' => [12,'type'],
        'id' => '',
        'ext' => ['traceid' => $traceid],
    ];
//发起调用
... ...
```







