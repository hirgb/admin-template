<?php
namespace org\util;

require_once __DIR__ . '/Thrift/ClassLoader/ThriftClassLoader.php';
require_once __DIR__ . '/ContentManage/DataCoreServing.php';
require_once __DIR__ . '/ContentManage/Types.php';

use \Thrift\ClassLoader\ThriftClassLoader;
use \Thrift\Protocol\TBinaryProtocol;
use \Thrift\Protocol\TCompactProtocol;
use \Thrift\Protocol\TJSONProtocol;
use \Thrift\Transport\TSocket;
use \Thrift\Transport\TBufferedTransport;
use \Thrift\Transport\TFramedTransport;

class PhoneThrift
{
    static function notify($message){
        $server = config('thriftServer');
        $isSuccess = false;
        // Load
        $loader = new ThriftClassLoader();
        $loader->registerNamespace('Thrift', __DIR__ . '/');
        $loader->register();

        // Init socket
        $s = explode(':', $server);
        $socket = new TSocket($s[0], $s[1]);
        $transport = new TFramedTransport($socket);
        $protocol = new TBinaryProtocol($transport);
        $client = new \DataCoreServingClient($protocol);
        // print_r($client);
        // Config
        $socket->setSendTimeout(10 * 1000);
        $socket->setRecvTimeout(10 * 1000);
        // Connect
        $transport->open();
        // Create request

        // Call...
        $response = $client->DataProcess($message);
        $transport->close();

        return json_decode($response);
    }
}
