<?php

namespace app\web\controller;

use app\web\logic\UserLogic;
use org\util\Response;

class User {
    public function login(){
        $res = new Response;

        $loginName = input('loginName');
        $password = input('password');
        $result = UserLogic::getUserByLoginNameAndPassword($loginName, $password);
        if (\is_array($result)) {
            if ($result[0] === true) {
                $user = $result[1];
                $res->code = 1;
                $res->data = $user;
            } else {
                $res->code = 0;
                $res->errorMsg = 'auth failed';
            }
        } else {
            $res->code = 0;
            $res->errorMsg = $result;
        }
        return $res;
    }

    public function getUsersOfRole(){
        $res = new Response;

        $role = input('role');
        $result = UserLogic::getUserByRole($role);
        if (\is_array($result) && $result[0] === true) {
            $res->code = 1;
            $res->data = $result[1];
        } else {
            $res->code = 0;
            $res->errorMsg = $result;
        }

        return $res;
    }
}

 ?>
