<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
//\think\Route::bind('web');
// 应用公共文件

function geturl($url){
        // $headerArray =array("Content-type:application/json;","Accept:application/json");
        // $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $headerArray =array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
}

function geturlplain($url){
        // $headerArray =array("Content-type:application/json;","Accept:application/json");
        // $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $headerArray =array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
}

function posturl($url,$data){
        $data  = json_encode($data);
        $headerArray = array("Content-type:application/json;charset='ISO-8859-1'","Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        // return $output;
        return json_decode($output, true);
}
