<?php
/*
 * @varsion		EasyWork系统 1.0var
 * @package		程序设计深圳市九五时代科技有限公司设计开发
 * @copyright	Copyright (c) 2010 - 2015, 95era, Inc.
 * @link		http://www.d-winner.com
 */
 
//curl获取远程数据 需要开启curl库
/*
$url		远程地址
*/
function curl_get_contents($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);            		//设置访问的url地址
	//curl_setopt($ch,CURLOPT_HEADER,1);            		//是否显示头部信息
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);           		//设置超时
	curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);   	//用户访问代理 User-Agent
	curl_setopt($ch, CURLOPT_REFERER,_REFERER_);        	//设置 referer
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);      		//跟踪301
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);       		 //返回结果
	$r = curl_exec($ch);
	curl_close($ch);
	return $r;
}

function curl_post_json($url, $jsonStr){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return array($httpCode, $response);
}

function get_bycurl($url, $data=''){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tmpInfo = curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    curl_close($ch);
    return $tmpInfo;
}

function curls($url, $postDate = "", $method = "PUT") {
    $ci = curl_init ();
    curl_setopt ( $ci, CURLOPT_URL, $url );
    curl_setopt ( $ci, CURLOPT_PORT, '' );
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
    //curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko) Version/8.0.3 Safari/600.3.18");
    curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ci, CURLOPT_AUTOREFERER, 1);
    curl_setopt ( $ci, CURLOPT_TIMEOUT, 200 );
    curl_setopt ( $ci, CURLOPT_RETURNTRANSFER, true );
    curl_setopt ( $ci, CURLOPT_FORBID_REUSE, 0 );
    curl_setopt ( $ci, CURLOPT_CUSTOMREQUEST, $method );
    if ($postDate) {
        curl_setopt ( $ci, CURLOPT_POSTFIELDS, $postDate );
    }
    $response = curl_exec ( $ci );
    if (curl_errno($ci)) {
        return curl_error($ci);
    }
    curl_close($ci);
    return $response;
}
