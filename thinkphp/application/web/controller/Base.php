<?php

namespace app\web\controller;

use think\Controller;
use think\Db;
use org\util\Response;

class Base extends Controller {

    protected $res;
    protected $db;
    protected $time;

    protected function _initialize()
    {
        $this->res = new Response;
        $this->db  = Db::connect(config('tempMysql'));
        $this->time = date('Y-m-d H:i:s');
    }

    protected function end($code, $data = '', $errorMsg = '', $errorArray = []){
        $this->res->code = $code;
        $this->res->data = $data;
        $this->res->errorMsg = $errorMsg;
        if (\count($errorArray)) {
            $this->res->errorArray = $errorArray;
        }

        return $this->res;
    }

    protected function must($paramsName) {
        $params = [];
        if (\is_array($paramsName)) {
            foreach($paramsName as $p) {
                $v = input($p);
                if ($v === '' || $v === 'undefined' || $v === null) {
                    abort(400);
                } else {
                    $params[explode('/', $p)[0]] = $v;
                }
            }
            return (object)$params;
        } else {
            abort(500);
        }
    }
}

 ?>
