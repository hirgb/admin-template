<?php

namespace org\util;

class Request {
    public $service_ver = "1.0";
    public $bot_name = "trioxapi";
    public $bot_ver = "0";
	public $user_id = "z_test";
	public $bot_mode = 0;
	public $query = "";
	public $req_type = "brain";
	public $send_time_ms = "1454319650000";
    public $query_time_ms = "1454319650000";
	public $debug = 0;
	public $callback_msg = "anything here will send back";
    public $stat_info = [
		"ip"=> "192.168.1.1",
		"device_id"=> "asdwrgh",
		"user_group"=> "user",
		"os"=> "android",
		"os_ver"=> "1.4.0"
	];
	public $location = [
		"la"=> 40.0433,
		"lo"=> 116.269,
		"address"=> "北京市海淀区软件园西三路",
		"country"=> "中国",
		"province"=> "北京市",
		"city"=> "北京市",
		"district"=> "海淀区",
		"street"=> "软件园西三路"
	];
}

 ?>
